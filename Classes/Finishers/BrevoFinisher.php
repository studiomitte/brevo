<?php

declare(strict_types=1);

namespace StudioMitte\Brevo\Finishers;

/*
 * This file is part of TYPO3 CMS-based extension "brevo" by StudioMitte.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use Brevo\Brevo;
use Brevo\Contacts\Requests\CreateContactRequest;
use Brevo\Contacts\Requests\CreateDoiContactRequest;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use StudioMitte\Brevo\ApiWrapper;
use StudioMitte\Brevo\Configuration;
use StudioMitte\Brevo\Events\FormFinisherAttributeEvent;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;
use TYPO3\CMS\Form\Domain\Finishers\AbstractFinisher;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * Finisher for EXT:form sending the data to brevo
 */
class BrevoFinisher extends AbstractFinisher implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    protected ?Configuration $extensionConfiguration = null;

    public function __construct(
        protected readonly EventDispatcherInterface $eventDispatcher,
    ) {
        try {
            $this->extensionConfiguration = new Configuration();
        } catch (\RuntimeException) {
        }
    }

    protected function executeInternal(): void
    {
        if ($this->extensionConfiguration === null) {
            $this->logger?->error('Brevo finisher skipped: no API key configured');
            $this->setFinisherSubscribedVariable(0);
            return;
        }

        if (!$this->newsletterSubscriptionIsEnabled()) {
            $this->setFinisherSubscribedVariable(0);
            return;
        }

        $this->addEntryToBrevo() ? $this->setFinisherSubscribedVariable(1) : $this->setFinisherSubscribedVariable(0);
    }

    protected function setFinisherSubscribedVariable(int $returnValue): void
    {
        $this->finisherContext->getFinisherVariableProvider()->add(
            'brevo',
            'data.subscribed',
            $returnValue
        );
    }

    protected function addEntryToBrevo(): bool
    {
        try {
            $client = $this->getApi();
            if ($this->extensionConfiguration->isDoi()) {
                $client->contacts->createDoiContact(new CreateDoiContactRequest([
                    'email' => (string)$this->parseOption('email'),
                    'includeListIds' => $this->getEnrichedListIds(),
                    'templateId' => $this->getDoiTemplateId(),
                    'attributes' => $this->getAttributes(),
                    'redirectionUrl' => $this->getRedirectionUrl(),
                ]));
            } else {
                $client->contacts->createContact(new CreateContactRequest([
                    'email' => (string)$this->parseOption('email'),
                    'updateEnabled' => true,
                    'listIds' => $this->getEnrichedListIds(),
                    'attributes' => $this->getAttributes(),
                ]));
            }
            return true;
        } catch (\Exception $exception) {
            // todo: should we forward it to the user?
            $this->logger?->error($exception->getMessage());
            return false;
        }
    }

    protected function getDoiTemplateId(): int
    {
        $doiTemplateId = (int)$this->parseOption('doiTemplateId');
        if ($doiTemplateId) {
            return $doiTemplateId;
        }
        return $this->extensionConfiguration->getDoiTemplateId();
    }

    protected function getDoiRedirectPageId(): int
    {
        $doiRedirectPageId = (int)$this->parseOption('doiRedirectPageId');
        if ($doiRedirectPageId) {
            return $doiRedirectPageId;
        }
        return $this->extensionConfiguration->getDoiRedirectPageId();
    }

    protected function newsletterSubscriptionIsEnabled(): bool
    {
        // Forms created with the form editor before version 3 stored the flag in "options.enabled"
        if (!isset($this->options['renderingOptions']['enabled']) && isset($this->options['enabled'])) {
            return (bool)$this->parseOption('enabled');
        }
        return $this->isEnabled();
    }

    /**
     * @return list<int>
     */
    protected function getEnrichedListIds(): array
    {
        $defaultListsFromForm = $this->parseOption('defaultListIds');
        if ($defaultListsFromForm !== null) {
            $lists = GeneralUtility::intExplode(',', (string)$defaultListsFromForm, true);
        } else {
            $lists = $this->extensionConfiguration->getDefaultListIds();
        }

        $additionalLists = $this->parseOption('additionalListIds');
        if ($additionalLists) {
            if (is_string($additionalLists)) {
                $additionalLists = GeneralUtility::intExplode(',', $additionalLists, true);
            }
            if (is_array($additionalLists)) {
                foreach ($additionalLists as $id) {
                    $id = (int)$id;
                    if ($id > 0) {
                        $lists[] = $id;
                    }
                }
            }
        }
        return array_values(array_unique($lists));
    }

    /**
     * @return array<string, mixed>
     */
    protected function getAttributes(): array
    {
        $attributes = [];
        if ($firstNameAttribute = $this->extensionConfiguration->getAttributeFirstName()) {
            $attributes[$firstNameAttribute] = $this->parseAttributeOption('firstName');
        }
        if ($lastNameAttribute = $this->extensionConfiguration->getAttributeLastName()) {
            $attributes[$lastNameAttribute] = $this->parseAttributeOption('lastName');
        }
        if ($trackingAttribute = $this->extensionConfiguration->getAttributeTracking()) {
            $attributes[$trackingAttribute] = $this->parseAttributeOption('tracking');
        }

        // additional attribute mappings
        foreach (array_keys((array)($this->options['additionalAttributes'] ?? [])) as $key) {
            $attributes[$key] = $this->parseAttributeOption('additionalAttributes.' . $key);
        }

        $event = new FormFinisherAttributeEvent($this, $attributes);
        $this->eventDispatcher->dispatch($event);

        return array_filter($event->getAttributes(), static fn(mixed $value): bool => $value !== null);
    }

    /**
     * Date fields are rendered with their display format (e.g. "d.m.Y"), the API only accepts "Y-m-d"
     */
    protected function parseAttributeOption(string $optionPath): mixed
    {
        $rawValue = ArrayUtility::isValidPath($this->options, $optionPath, '.')
            ? ArrayUtility::getValueByPath($this->options, $optionPath, '.')
            : null;
        if (is_string($rawValue) && preg_match('/^{([^}]+)}$/', $rawValue, $matches)) {
            $formValue = ObjectAccess::getPropertyPath($this->finisherContext->getFormRuntime(), $matches[1]);
            if ($formValue instanceof \DateTimeInterface) {
                return $formValue->format('Y-m-d');
            }
        }
        return $this->parseOption($optionPath);
    }

    protected function getRedirectionUrl(): string
    {
        $typolinkConfiguration = [
            'parameter' => $this->getDoiRedirectPageId(),
            'forceAbsoluteUrl' => true,
        ];
        $contentObjectRenderer = GeneralUtility::makeInstance(ContentObjectRenderer::class);
        $contentObjectRenderer->setRequest($this->finisherContext->getRequest());
        return $contentObjectRenderer->createUrl($typolinkConfiguration);
    }

    protected function getApi(): Brevo
    {
        return ApiWrapper::getClient();
    }
}

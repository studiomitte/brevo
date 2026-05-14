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

use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Brevo\Client\Api\ContactsApi;
use Brevo\Client\Model\CreateContact;
use Brevo\Client\Model\CreateDoiContact;
use StudioMitte\Brevo\ApiWrapper;
use StudioMitte\Brevo\Configuration;
use StudioMitte\Brevo\Events\FormFinisherAttributeEvent;
use TYPO3\CMS\Core\EventDispatcher\EventDispatcher;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;
use TYPO3\CMS\Form\Domain\Finishers\AbstractFinisher;

/**
 * Finisher for EXT:form sending the data to brevo
 */
class BrevoFinisher extends AbstractFinisher implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /** @var Configuration */
    protected Configuration $extensionConfiguration;

    /** @var EventDispatcherInterface */
    protected $eventDispatcher;

    public function __construct(string $finisherIdentifier = '')
    {
        $this->extensionConfiguration = new Configuration();
        $this->eventDispatcher = GeneralUtility::makeInstance(EventDispatcher::class);
    }

    protected function executeInternal(): void
    {
        if (!$this->newsletterSubscriptionIsEnabled()) {
            $this->setFinisherSubscribedVariable(0);
            return;
        }

        $newContactId = $this->addEntryToBrevo();
        if (MathUtility::canBeInterpretedAsInteger($newContactId)) {
            $this->setFinisherSubscribedVariable(1);
            if ($newContactId > 0) {
                $this->setFinisherNewContactIdVariable($newContactId);
            }
            return;
        }

        $this->setFinisherSubscribedVariable(0);
    }

    protected function setFinisherSubscribedVariable(int $returnValue): void
    {
        $this->finisherContext->getFinisherVariableProvider()->add(
            'brevo',
            'data.subscribed',
            $returnValue
        );
    }

    protected function setFinisherNewContactIdVariable(int $newContactId): void
    {
        $this->finisherContext->getFinisherVariableProvider()->add(
            'brevo',
            'data.newContactId',
            $newContactId
        );
    }

    /**
     * Add the entry to Brevo.
     * In case of active DOI, the contact is not created immediately, so no contact ID can be returned.
     *
     * @return int|null
     */
    protected function addEntryToBrevo(): int|null
    {
        try {
            $apiInstance = $this->getApi();
            if ($this->extensionConfiguration->isDoi()) {
                $createContact = new CreateDoiContact();
                $createContact
                    ->setEmail($this->parseOption('email'))
                    ->setIncludeListIds($this->getEnrichedListIds())
                    ->setTemplateId($this->getDoiTemplateId())
                    ->setAttributes($this->getAttributes())
                    ->setRedirectionUrl($this->getRedirectionUrl());
                $apiInstance->createDoiContact($createContact);
                return 0; // in case of DOI we cannot return the contact ID, because the contact is not created immediately
            }

            $createContact = new CreateContact();
            $createContact
                ->setEmail($this->parseOption('email'))
                ->setUpdateEnabled(true)
                ->setListIds($this->getEnrichedListIds())
                ->setAttributes($this->getAttributes());
            return $apiInstance->createContact($createContact)?->getId() ?? 0;

        } catch (\Exception $exception) {
            // todo: should we forward it to the user?
            $this->logger->error($exception->getMessage());
            return null;
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
        return $this->isEnabled();
    }

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
        return array_unique($lists);
    }

    /**
     * @return object
     */
    protected function getAttributes(): object
    {
        $attributes = [];
        if ($firstNameAttribute = $this->extensionConfiguration->getAttributeFirstName()) {
            $attributes[$firstNameAttribute] = $this->parseOption('firstName');
        }
        if ($lastNameAttribute = $this->extensionConfiguration->getAttributeLastName()) {
            $attributes[$lastNameAttribute] = $this->parseOption('lastName');
        }
        if ($trackingAttribute = $this->extensionConfiguration->getAttributeTracking()) {
            $attributes[$trackingAttribute] = $this->parseOption('tracking');
        }

        // additional attribute mappings
        $additionalAttributes = (array)($this->parseOption('additionalAttributes') ?? []);
        foreach ($additionalAttributes as $key => $value) {
            $attributes[$key] = $value;
        }

        $event = new FormFinisherAttributeEvent($this, $attributes);
        $this->eventDispatcher->dispatch($event);
        $attributes = $event->getAttributes();

        return (object)$attributes;
    }

    protected function getRedirectionUrl(): string
    {
        $typolinkConfiguration = [
            'parameter' => $this->getDoiRedirectPageId(),
            'forceAbsoluteUrl' => true,
        ];
        return $this->getTypoScriptFrontendController()->cObj->typoLink_URL($typolinkConfiguration);
    }

    protected function getApi(): ContactsApi
    {
        return new ContactsApi(null, ApiWrapper::getConfig());
    }
}

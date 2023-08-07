<?php

declare(strict_types=1);

namespace StudioMitte\Sendinblue\Finishers;

/*
 * This file is part of TYPO3 CMS-based extension "sendinblue" by StudioMitte.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use SendinBlue\Client\Api\ContactsApi;
use SendinBlue\Client\Model\CreateContact;
use SendinBlue\Client\Model\CreateDoiContact;
use StudioMitte\Sendinblue\ApiWrapper;
use StudioMitte\Sendinblue\Configuration;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Form\Domain\Finishers\AbstractFinisher;

/**
 * Finisher for EXT:form sending the data to sendinblue
 */
class SendinblueFinisher extends AbstractFinisher implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /** @var Configuration */
    protected Configuration $extensionConfiguration;

    public function __construct(string $finisherIdentifier = '')
    {
        $this->extensionConfiguration = new Configuration();
    }

    protected function executeInternal(): void
    {
        if (!$this->newsletterSubscriptionIsEnabled()) {
            $this->setFinisherSubscribedVariable(0);
            return;
        }

        $this->addEntryToSendInBlue() ? $this->setFinisherSubscribedVariable(1) : $this->setFinisherSubscribedVariable(0);
    }

    protected function setFinisherSubscribedVariable(int $returnValue): void
    {
        $this->finisherContext->getFinisherVariableProvider()->add(
            'sendinblue',
            'data.subscribed',
            $returnValue
        );
    }

    protected function addEntryToSendInBlue(): bool
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
            } else {
                $createContact = new CreateContact();
                $createContact
                    ->setEmail($this->parseOption('email'))
                    ->setUpdateEnabled(true)
                    ->setListIds($this->getEnrichedListIds())
                    ->setAttributes($this->getAttributes());
                $apiInstance->createContact($createContact);
            }
            return true;
        } catch (\Exception $exception) {
            // todo: should we forward it to the user?
            $this->logger->error($exception->getMessage());
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
        $additionalAttributes = $this->parseOption('additionalAttributes');
        foreach ($additionalAttributes as $key => $value) {
            $attributes[$key] = $value;
        }
        return (object)$attributes;
    }

    protected function getRedirectionUrl(): string
    {
        $typolinkConfiguration = [
            'parameter' => $this->extensionConfiguration->getDoiRedirectPageId(),
            'forceAbsoluteUrl' => true,
        ];
        return $this->getTypoScriptFrontendController()->cObj->typoLink_URL($typolinkConfiguration);
    }

    protected function getApi(): ContactsApi
    {
        return new ContactsApi(null, ApiWrapper::getConfig());
    }
}

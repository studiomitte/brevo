<?php

declare(strict_types=1);

namespace StudioMitte\Brevo;

/*
 * This file is part of TYPO3 CMS-based extension "brevo" by StudioMitte.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Extension configuration
 */
class Configuration
{
    /** @var string */
    protected $apiKey;

    /** @var int[] */
    protected $defaultListIds;

    /** @var bool */
    protected $doi = true;

    /** @var int */
    protected $doiRedirectPageId = 0;

    /** @var int */
    protected $doiTemplateId = 0;

    /** @var string */
    protected $attributeFirstName = '';

    /** @var string */
    protected $attributeLastName = '';

    /** @var string */
    protected $attributeTracking = '';

    public function __construct()
    {
        try {
            $settings = GeneralUtility::makeInstance(ExtensionConfiguration::class)->get('brevo');

            foreach (['apiKey', 'attributeFirstName', 'attributeLastName', 'attributeTracking'] as $stringField) {
                $this->$stringField = $settings[$stringField] ?? '';
            }
            foreach (['doiRedirectPageId', 'doiTemplateId'] as $intField) {
                $this->$intField = (int)($settings[$intField] ?? 0);
            }

            $this->doi = (bool)($settings['doi'] ?? true);
            $this->defaultListIds = GeneralUtility::intExplode(',', $settings['defaultListIds'] ?? '', true);
        } catch (ExtensionConfigurationExtensionNotConfiguredException|ExtensionConfigurationPathDoesNotExistException) {
        }

        if (empty($this->apiKey)) {
            throw new \RuntimeException('No API key provided', 1603192439);
        }
    }

    public function getApiKey(): string
    {
        return $this->apiKey;
    }

    public function isDoi(): bool
    {
        return $this->doi;
    }

    public function getDoiRedirectPageId(): int
    {
        return $this->doiRedirectPageId;
    }

    public function getDoiTemplateId(): int
    {
        return $this->doiTemplateId;
    }

    /**
     * @return int[]
     */
    public function getDefaultListIds(): array
    {
        return $this->defaultListIds;
    }

    public function getAttributeFirstName(): string
    {
        return $this->attributeFirstName;
    }

    public function getAttributeLastName(): string
    {
        return $this->attributeLastName;
    }

    public function getAttributeTracking(): string
    {
        return $this->attributeTracking;
    }
}

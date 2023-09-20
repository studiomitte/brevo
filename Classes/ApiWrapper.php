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

use Brevo\Client\Configuration;
use StudioMitte\Brevo\Configuration as ExtensionConfiguration;

/**
 * Wrapper for the brevo api giving back a pre-configured API
 */
class ApiWrapper
{
    public static function getConfig(): Configuration
    {
        $configuration = new ExtensionConfiguration();
        return Configuration::getDefaultConfiguration()->setApiKey('api-key', $configuration->getApiKey());
    }
}

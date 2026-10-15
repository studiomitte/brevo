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

use Brevo\Brevo;

/**
 * Wrapper for the brevo api giving back a pre-configured client
 */
class ApiWrapper
{
    public static function getClient(): Brevo
    {
        return new Brevo(apiKey: new Configuration()->getApiKey());
    }
}

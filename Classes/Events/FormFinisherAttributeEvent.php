<?php

declare(strict_types=1);

namespace StudioMitte\Brevo\Events;

/*
 * This file is part of TYPO3 CMS-based extension "brevo" by StudioMitte.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use StudioMitte\Brevo\Finishers\BrevoFinisher;

class FormFinisherAttributeEvent
{
    public function __construct(protected BrevoFinisher $brevoFinisher, protected array $attributes) {}

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function setAttributes(array $attributes): void
    {
        $this->attributes = $attributes;
    }
}

<?php

declare(strict_types=1);

$EM_CONF[$_EXTKEY] = [
    'title' => 'Brevo integration',
    'description' => 'Integration of newsletter SaaS solution brevo.com (formerly known as sendinblue) into TYPO3 CMS',
    'category' => 'plugin',
    'author' => 'Georg Ringer',
    'author_email' => 'gr@studiomitte.com',
    'author_company' => 'StudioMitte',
    'state' => 'stable',
    'version' => '3.0.0',
    'constraints' => [
        'depends' => [
            'typo3' => '14.3.0-14.99.99',
            'form' => '14.3.0-14.99.99',
            'reports' => '14.3.0-14.99.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];

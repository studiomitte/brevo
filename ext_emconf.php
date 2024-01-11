<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Brevo integration',
    'description' => 'Integration of newsletter SaaS solution sendinblue.com  (formally known as sendinblue) into TYPO3 CMS',
    'category' => 'plugin',
    'author' => 'Georg Ringer',
    'author_email' => 'gr@studiomitte.com',
    'author_company' => 'StudioMitte',
    'state' => 'stable',
    'clearCacheOnLoad' => 0,
    'version' => '2.0.1',
    'constraints' => [
        'depends' => [
            'typo3' => '11.5.0-12.4.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];

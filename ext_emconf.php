<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Sendinblue',
    'description' => 'Integration of newsletter SaaS solution sendinblue.com into TYPO3 CMS',
    'category' => 'plugin',
    'author' => 'Georg Ringer',
    'author_email' => 'gr@studiomitte.com',
    'author_company' => 'StudioMitte',
    'state' => 'stable',
    'clearCacheOnLoad' => 0,
    'version' => '1.1.0',
    'constraints' => [
        'depends' => [
            'typo3' => '11.5.0-10.5.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];

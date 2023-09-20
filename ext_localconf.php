<?php

defined('TYPO3') or die('Access denied.');

call_user_func(
    static function () {
        // @todo registration can be dropped, when dropping v11 support
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['reports']['brevo']['general'] = [
            'title' => 'LLL:EXT:brevo/Resources/Private/Language/locallang_report.xlf:report.title',
            'description' => 'LLL:EXT:brevo/Resources/Private/Language/locallang_report.xlf:report.description',
            'icon' => 'EXT:brevo/Resources/Public/Icons/Extension.svg',
            'report' => \StudioMitte\Brevo\Report\IntegrationReport::class
        ];

        if (!isset($GLOBALS['TYPO3_CONF_VARS']['LOG']['StudioMitte']['Brevo']['writerConfiguration'])) {
            $context = \TYPO3\CMS\Core\Core\Environment::getContext();
            if ($context->isProduction()) {
                $logLevel = \TYPO3\CMS\Core\Log\LogLevel::ERROR;
            } elseif ($context->isDevelopment()) {
                $logLevel = \TYPO3\CMS\Core\Log\LogLevel::DEBUG;
            } else {
                $logLevel = \TYPO3\CMS\Core\Log\LogLevel::INFO;
            }
            $GLOBALS['TYPO3_CONF_VARS']['LOG']['StudioMitte']['Brevo']['writerConfiguration'] = [
                $logLevel => [
                    'TYPO3\\CMS\\Core\\Log\\Writer\\FileWriter' => [
                        'logFileInfix' => 'brevo'
                    ]
                ],
            ];
        }
    }
);

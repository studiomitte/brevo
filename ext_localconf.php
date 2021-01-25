<?php

if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

call_user_func(
    static function () {
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTypoScriptSetup(
            trim(
                '
plugin.tx_form {
    settings {
        yamlConfigurations {
            1483353712 = EXT:sendinblue/Configuration/Yaml/SendinblueFrontend.yaml
        }
    }
}
module.tx_form {
    settings {
        yamlConfigurations {
            1483353712 = EXT:sendinblue/Configuration/Yaml/SendinblueBackend.yaml
        }
    }
}
'
            )
        );

        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['reports']['sendinblue']['general'] = [
            'title' => 'LLL:EXT:sendinblue/Resources/Private/Language/locallang_report.xlf:report.title',
            'description' => 'LLL:EXT:sendinblue/Resources/Private/Language/locallang_report.xlf:report.description',
            'icon' => 'EXT:sendinblue/Resources/Public/Icons/Extension.svg',
            'report' => \StudioMitte\Sendinblue\Report\IntegrationReport::class
        ];

        if (!isset($GLOBALS['TYPO3_CONF_VARS']['LOG']['StudioMitte']['Sendinblue']['writerConfiguration'])) {
            $context = \TYPO3\CMS\Core\Core\Environment::getContext();
            if ($context->isProduction()) {
                $logLevel = \TYPO3\CMS\Core\Log\LogLevel::ERROR;
            } elseif ($context->isDevelopment()) {
                $logLevel = \TYPO3\CMS\Core\Log\LogLevel::DEBUG;
            } else {
                $logLevel = \TYPO3\CMS\Core\Log\LogLevel::INFO;
            }
            $GLOBALS['TYPO3_CONF_VARS']['LOG']['StudioMitte']['Sendinblue']['writerConfiguration'] = [
                $logLevel => [
                    'TYPO3\\CMS\\Core\\Log\\Writer\\FileWriter' => [
                        'logFileInfix' => 'sendinblue'
                    ]
                ],
            ];
        }
    }
);

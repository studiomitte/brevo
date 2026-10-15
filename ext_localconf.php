<?php

declare(strict_types=1);

use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Log\LogLevel;
use TYPO3\CMS\Core\Log\Writer\FileWriter;

if (!isset($GLOBALS['TYPO3_CONF_VARS']['LOG']['StudioMitte']['Brevo']['writerConfiguration'])) {
    $context = Environment::getContext();
    $logLevel = match (true) {
        $context->isProduction() => LogLevel::ERROR,
        $context->isDevelopment() => LogLevel::DEBUG,
        default => LogLevel::INFO,
    };
    $GLOBALS['TYPO3_CONF_VARS']['LOG']['StudioMitte']['Brevo']['writerConfiguration'] = [
        $logLevel => [
            FileWriter::class => [
                'logFileInfix' => 'brevo',
            ],
        ],
    ];
}

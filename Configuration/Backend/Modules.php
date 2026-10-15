<?php

declare(strict_types=1);

use StudioMitte\Brevo\Controller\IntegrationReportController;

return [
    'system_reports_brevo' => [
        'parent' => 'system_reports',
        'access' => 'admin',
        'path' => '/module/system/reports/brevo',
        'iconIdentifier' => 'module-reports',
        'labels' => [
            'title' => 'brevo.report:report.title',
            'shortDescription' => 'brevo.report:report.description',
            'description' => 'brevo.report:report.description',
        ],
        'routes' => [
            '_default' => [
                'target' => IntegrationReportController::class . '::handleRequest',
            ],
        ],
    ],
];

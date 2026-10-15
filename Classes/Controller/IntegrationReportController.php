<?php

declare(strict_types=1);

namespace StudioMitte\Brevo\Controller;

/*
 * This file is part of TYPO3 CMS-based extension "brevo" by StudioMitte.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use Brevo\Brevo;
use Brevo\Contacts\Requests\GetListsRequest;
use Brevo\TransactionalEmails\Requests\GetSmtpTemplatesRequest;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use StudioMitte\Brevo\ApiWrapper;
use TYPO3\CMS\Backend\Attribute\AsController;
use TYPO3\CMS\Backend\Template\Enum\ModuleLayout;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Core\Localization\LanguageService;

/**
 * Backend report (submodule of "Reports") giving integrators feedback about the configuration
 */
#[AsController]
final readonly class IntegrationReportController
{
    public function __construct(
        private ModuleTemplateFactory $moduleTemplateFactory,
    ) {}

    public function handleRequest(ServerRequestInterface $request): ResponseInterface
    {
        $languageService = $this->getLanguageService();
        $view = $this->moduleTemplateFactory->create($request);
        $view->setLayout(ModuleLayout::NORMAL);
        $view->setTitle(
            $languageService->translate('title', 'reports.modules.overview'),
            $languageService->translate('report.title', 'brevo.report')
        );
        $view->makeDocHeaderModuleMenu();
        $view->getDocHeaderComponent()->setShortcutContext(
            'system_reports_brevo',
            $languageService->translate('report.title', 'brevo.report')
        );

        $client = ApiWrapper::getClient();
        $view->assignMultiple([
            'contacts' => $this->getContactInformation($client),
            'emails' => $this->getTransactionalEmailInformation($client),
        ]);

        return $view->renderResponse('IntegrationReport');
    }

    /**
     * @return array<string, mixed>
     */
    private function getContactInformation(Brevo $client): array
    {
        return [
            'lists' => $client->contacts->getLists(new GetListsRequest(['limit' => 50])),
            'attributes' => $client->contacts->getAttributes(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function getTransactionalEmailInformation(Brevo $client): array
    {
        return [
            'templates' => $client->transactionalEmails->getSmtpTemplates(new GetSmtpTemplatesRequest(['limit' => 50])),
        ];
    }

    private function getLanguageService(): LanguageService
    {
        return $GLOBALS['LANG'];
    }
}

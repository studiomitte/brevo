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
use Brevo\Exceptions\BrevoApiException;
use Brevo\Exceptions\BrevoException;
use Brevo\TransactionalEmails\Requests\GetSmtpTemplatesRequest;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use StudioMitte\Brevo\ApiWrapper;
use StudioMitte\Brevo\Configuration;
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

        try {
            $client = ApiWrapper::getClient();
            $view->assignMultiple([
                'contacts' => $this->getContactInformation($client),
                'emails' => $this->getTransactionalEmailInformation($client),
            ]);
        } catch (\Throwable $e) {
            $view->assign('error', $this->describeError($e));
        }

        return $view->renderResponse('IntegrationReport');
    }

    /**
     * @return array{message: string, status: ?int, code: ?string, exception: string, apiKey: string, hint: ?string, hintLink: ?string}
     */
    private function describeError(\Throwable $e): array
    {
        $error = [
            'message' => $e->getMessage(),
            'status' => null,
            'code' => null,
            'exception' => $e::class,
            'apiKey' => $this->getMaskedApiKey(),
            'hint' => null,
            'hintLink' => null,
        ];

        if ($e instanceof \RuntimeException && $e->getCode() === 1603192439) {
            $error['hint'] = 'error.hint.missingApiKey';
            return $error;
        }

        if ($e instanceof BrevoApiException) {
            $error['status'] = $e->getCode();
            $body = json_decode((string)$e->getBody(), true);
            if (is_array($body)) {
                $error['message'] = (string)($body['message'] ?? $error['message']);
                $error['code'] = isset($body['code']) ? (string)$body['code'] : null;
            } elseif ((string)$e->getBody() !== '') {
                $error['message'] .= ': ' . $e->getBody();
            }

            if ($e->getCode() === 401 && str_contains($error['message'], 'IP address')) {
                $error['hint'] = 'error.hint.unauthorizedIp';
                $error['hintLink'] = 'https://app.brevo.com/security/authorised_ips';
            } elseif ($e->getCode() === 401) {
                $error['hint'] = 'error.hint.invalidApiKey';
                $error['hintLink'] = 'https://app.brevo.com/settings/keys/api';
            } elseif ($e->getCode() === 429) {
                $error['hint'] = 'error.hint.rateLimit';
            }
        } elseif ($e instanceof BrevoException) {
            $error['hint'] = 'error.hint.connection';
        }

        return $error;
    }

    private function getMaskedApiKey(): string
    {
        try {
            $apiKey = new Configuration()->getApiKey();
        } catch (\RuntimeException) {
            return '';
        }
        return strlen($apiKey) > 12 ? substr($apiKey, 0, 8) . '…' . substr($apiKey, -4) : '…';
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

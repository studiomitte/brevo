<?php

declare(strict_types=1);

namespace StudioMitte\Brevo\Report;

/*
 * This file is part of TYPO3 CMS-based extension "brevo" by StudioMitte.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use Brevo\Client\Api\AccountApi;
use Brevo\Client\Api\ContactsApi;
use Brevo\Client\Api\TransactionalEmailsApi;
use Brevo\Client\ApiException;
use Brevo\Client\Model\GetAccount;
use StudioMitte\Brevo\ApiWrapper;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Exception\InvalidExtensionNameException;
use TYPO3\CMS\Fluid\View\StandaloneView;
use TYPO3\CMS\Reports\ReportInterface;

/**
 * Basic report giving integrators feedback about configuration
 */
class IntegrationReport implements ReportInterface
{

    /**
     * This method renders the report
     *
     * @return string The status report as HTML
     * @throws ApiException
     * @throws InvalidExtensionNameException
     */
    public function getReport(): string
    {
        // Rendering of the output via fluid
        $view = GeneralUtility::makeInstance(StandaloneView::class);
        $view->setTemplatePathAndFilename(GeneralUtility::getFileAbsFileName(
            'EXT:brevo/Resources/Private/Templates/IntegrationReport.html'
        ));

        $view->assignMultiple([
            'account' => $this->getAccountInformation(),
            'contacts' => $this->getContactInformation(),
            'emails' => $this->getTransactionalEmailInformation(),
            'lll' => 'LLL:EXT:brevo/Resources/Private/Language/locallang_report.xlf:'
        ]);

        return $view->render();
    }

    /**
     * @return array
     * @throws ApiException
     */
    protected function getContactInformation(): array
    {
        $api = new ContactsApi(null, ApiWrapper::getConfig());

        return [
            'lists' => $api->getLists(50),
            'attributes' => $api->getAttributes(),
        ];
    }

    /**
     * @return array
     * @throws ApiException
     */
    protected function getTransactionalEmailInformation(): array
    {
        $api = new TransactionalEmailsApi(null, ApiWrapper::getConfig());

        return [
            'templates' => $api->getSmtpTemplates(),
        ];
    }

    /**
     * @return GetAccount
     * @throws ApiException
     */
    protected function getAccountInformation(): GetAccount
    {
        $api = new AccountApi(null, ApiWrapper::getConfig());
        return $api->getAccount();
    }

    public function getIdentifier(): string
    {
        return 'general';
    }

    public function getTitle(): string
    {
        return 'LLL:EXT:brevo/Resources/Private/Language/locallang_report.xlf:report.title';
    }

    public function getDescription(): string
    {
        return 'LLL:EXT:brevo/Resources/Private/Language/locallang_report.xlf:report.description';
    }

    public function getIconIdentifier(): string
    {
        return 'module-reports';
    }
}

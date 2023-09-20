<?php

declare(strict_types=1);

namespace StudioMitte\Brevo\Command;

/*
 * This file is part of TYPO3 CMS-based extension "brevo" by StudioMitte.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use Brevo\Client\Api\ContactsApi;
use StudioMitte\Brevo\ApiWrapper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Test command, for playing with API
 */
class TestCommand extends Command
{
    /**
     * Defines the allowed options for this command
     */
    protected function configure()
    {
        $this
            ->setDescription('Brevo demo');
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $config = ApiWrapper::getConfig();

        $apiInstance = new ContactsApi(
            null,
            $config
        );
        $limit = 50;
        $offset = 0;

        $result = $apiInstance->getContacts($limit, $offset);
        print_r($result);
        return 0;
    }
}

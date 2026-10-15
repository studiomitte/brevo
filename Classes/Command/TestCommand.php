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

use Brevo\Contacts\Requests\GetContactsRequest;
use Brevo\Exceptions\BrevoApiException;
use StudioMitte\Brevo\ApiWrapper;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Test command, for playing with API
 */
#[AsCommand('brevo:test', 'Brevo demo: list the first contacts')]
class TestCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        try {
            $result = ApiWrapper::getClient()->contacts->getContacts(new GetContactsRequest(['limit' => 50, 'offset' => 0]));
        } catch (BrevoApiException $e) {
            $io->error(sprintf('%s (HTTP %d): %s', $e->getMessage(), $e->getCode(), $e->getBody()));
            return Command::FAILURE;
        }
        $io->writeln((string)$result);
        return Command::SUCCESS;
    }
}

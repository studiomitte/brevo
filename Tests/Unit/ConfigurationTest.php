<?php

declare(strict_types=1);

namespace StudioMitte\Brevo\Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use StudioMitte\Brevo\Configuration;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

final class ConfigurationTest extends UnitTestCase
{
    #[Test]
    public function configurationIsProperlyReturned(): void
    {
        $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['brevo'] = [
            'apiKey' => 'some api keys',
            'attributeFirstName' => 'VORNAME',
            'attributeLastName' => 'NACHNAME',
            'attributeTracking' => 'SOURCE',
            'defaultListIds' => '2',
            'doi' => '1',
            'doiRedirectPageId' => '123',
            'doiTemplateId' => '7',
        ];

        $configuration = new Configuration();
        self::assertSame('some api keys', $configuration->getApiKey());
        self::assertSame('VORNAME', $configuration->getAttributeFirstName());
        self::assertSame('NACHNAME', $configuration->getAttributeLastName());
        self::assertSame('SOURCE', $configuration->getAttributeTracking());
        self::assertSame([2], $configuration->getDefaultListIds());
        self::assertTrue($configuration->isDoi());
        self::assertSame(123, $configuration->getDoiRedirectPageId());
        self::assertSame(7, $configuration->getDoiTemplateId());
    }

    #[Test]
    public function missingApiKeyThrowsException(): void
    {
        $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['brevo'] = [
            'some' => 'value',
        ];
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionCode(1603192439);
        new Configuration();
    }
}

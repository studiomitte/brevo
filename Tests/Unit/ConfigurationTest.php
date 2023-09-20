<?php

namespace StudioMitte\Brevo\Tests\Unit;

use StudioMitte\Brevo\Configuration;
use TYPO3\TestingFramework\Core\BaseTestCase;

class ConfigurationTest extends BaseTestCase
{

    /**
     * @test
     */
    public function configurationIsProperlyReturned()
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
        self::assertEquals('some api keys', $configuration->getApiKey());
        self::assertEquals('VORNAME', $configuration->getAttributeFirstName());
        self::assertEquals('NACHNAME', $configuration->getAttributeLastName());
        self::assertEquals('SOURCE', $configuration->getAttributeTracking());
        self::assertEquals([2], $configuration->getDefaultListIds());
        self::assertTrue($configuration->isDoi());
        self::assertEquals(123, $configuration->getDoiRedirectPageId());
        self::assertEquals(7, $configuration->getDoiTemplateId());
    }

    /**
     * @test
     */
    public function missingApiKeyThrowsException()
    {
        $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['brevo'] = [
            'some' => 'value',
        ];
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionCode(1603192439);
        new Configuration();
    }
}

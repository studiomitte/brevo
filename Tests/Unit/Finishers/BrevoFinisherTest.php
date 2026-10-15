<?php

declare(strict_types=1);

namespace StudioMitte\Brevo\Tests\Unit\Finishers;

use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Psr\EventDispatcher\EventDispatcherInterface;
use StudioMitte\Brevo\Configuration;
use StudioMitte\Brevo\Finishers\BrevoFinisher;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

#[AllowMockObjectsWithoutExpectations]
final class BrevoFinisherTest extends UnitTestCase
{
    #[Test]
    public function attributesAreGenerated(): void
    {
        $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['brevo'] = [
            'apiKey' => 'some api keys',
            'attributeFirstName' => 'FIRST',
            'attributeLastName' => 'LAST',
            'attributeTracking' => 'SOURCE',
        ];
        $eventDispatcher = new class() implements EventDispatcherInterface {
            public function dispatch(object $event): object
            {
                return $event;
            }
        };
        $mockedFinisher = $this->getAccessibleMock(BrevoFinisher::class, ['parseOption'], [$eventDispatcher]);
        $mockedFinisher->method('parseOption')->willReturnCallback(static fn(string $option): ?string => $option === 'additionalAttributes' ? null : $option);
        $mockedFinisher->_set('extensionConfiguration', new Configuration());

        $expected = [
            'FIRST' => 'firstName',
            'LAST' => 'lastName',
            'SOURCE' => 'tracking',
        ];
        self::assertSame($expected, (array)$mockedFinisher->_call('getAttributes'));
    }

    #[Test]
    #[DataProvider('newsletterSubscriptionIsEnabledReturnsValueDataProvider')]
    public function newsletterSubscriptionIsEnabledReturnsValue(mixed $given, bool $expected): void
    {
        $mockedFinisher = $this->getAccessibleMock(BrevoFinisher::class, ['parseOption'], [], '', false);
        $mockedFinisher->method('parseOption')->willReturn($given);
        $mockedFinisher->_set('options', ['renderingOptions' => ['enabled' => $given]]);
        self::assertSame($expected, $mockedFinisher->_call('newsletterSubscriptionIsEnabled'));
    }

    /**
     * @return array<string, array{mixed, bool}>
     */
    public static function newsletterSubscriptionIsEnabledReturnsValueDataProvider(): array
    {
        return [
            'true' => [true, true],
            'one' => [1, true],
            'false' => [false, false],
            'zero' => [0, false],
            'string' => ['', false],
        ];
    }
}

<?php

namespace StudioMitte\Sendinblue\Tests\Unit\Finishers;

use PHPUnit\Framework\MockObject\MockObject;
use StudioMitte\Sendinblue\Configuration;
use StudioMitte\Sendinblue\Finishers\SendinblueFinisher;
use TYPO3\TestingFramework\Core\AccessibleObjectInterface;
use TYPO3\TestingFramework\Core\BaseTestCase;

class SendinblueFinisherTest extends BaseTestCase
{

    /**
     * @test
     */
    public function attributesAreGenerated()
    {
        /** @var SendinblueFinisher|MockObject|AccessibleObjectInterface $mockedFinisher */
        $mockedFinisher = $this->getAccessibleMock(SendinblueFinisher::class, ['parseOption'], [], '', false);
        $mockedFinisher->expects(self::any())->method('parseOption')->willReturnArgument(0);
        $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['sendinblue'] = [
            'apiKey' => 'some api keys',
            'attributeFirstName' => 'FIRST',
            'attributeLastName' => 'LAST',
            'attributeTracking' => 'SOURCE',
        ];
        $mockedFinisher->_set('extensionConfiguration', new Configuration());

        $attributes = new \stdClass();
        $attributes->FIRST = 'firstName';
        $attributes->LAST = 'lastName';
        $attributes->SOURCE = 'tracking';
        self::assertEquals($attributes, $mockedFinisher->_call('getAttributes'));
    }

    /**
     * @test
     * @dataProvider newsletterSubscriptionIsEnabledReturnsValueDataProvider
     * @param mixed $given
     * @param bool $expected
     */
    public function newsletterSubscriptionIsEnabledReturnsValue($given, bool $expected)
    {
        $mockedFinisher = $this->getAccessibleMock(SendinblueFinisher::class, ['parseOption'], [], '', false);
        $mockedFinisher->expects(self::any())->method('parseOption')->willReturn($given);
        self::assertEquals($expected, $mockedFinisher->_call('newsletterSubscriptionIsEnabled'));
    }

    public function newsletterSubscriptionIsEnabledReturnsValueDataProvider(): array
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

<?php

namespace NerdsAndCompany\Schematic\Commerce\Services;

use Craft\BaseTest;
use Craft\Commerce_PaymentCurrencyModel;
use Craft\Commerce_PaymentCurrenciesService;
use Craft\Craft;
use Craft\DbCommand;
use Craft\DbConnection;
use NerdsAndCompany\Schematic\Models\Result;
use PHPUnit_Framework_MockObject_MockObject as Mock;

/**
 * Class PaymentCurrenciesTest.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2017, Nerds & Company
 * @license   MIT
 *
 * @see      http://www.nerds.company
 *
 * @coversDefaultClass \NerdsAndCompany\Schematic\Commerce\Services\PaymentCurrencies
 * @covers ::__construct
 * @covers ::<!public>
 */
class PaymentCurrenciesTest extends BaseTest
{
    //==============================================================================================================
    //=================================================  TESTS  ====================================================
    //==============================================================================================================

    /**
     * @covers ::export
     * @dataProvider provideValidPaymentCurrencies
     *
     * @param PaymentCurrencyModel[] $currencies
     * @param array                  $expectedResult
     */
    public function testSuccessfulExport(array $currencies, array $expectedResult = [])
    {
        $schematicPaymentCurrenciesService = new PaymentCurrencies();

        $actualResult = $schematicPaymentCurrenciesService->export($currencies);

        $this->assertSame($expectedResult, $actualResult);
    }

    /**
     * @covers ::import
     * @dataProvider provideValidPaymentCurrencyDefinitions
     *
     * @param array $currencyDefinitions
     */
    public function testSuccessfulImport(array $currencyDefinitions)
    {
        $this->setMockPaymentCurrenciesService();
        $this->setMockDbConnection();

        $schematicPaymentCurrenciesService = new PaymentCurrencies();

        $import = $schematicPaymentCurrenciesService->import($currencyDefinitions);

        $this->assertInstanceOf(Result::class, $import);
        $this->assertFalse($import->hasErrors());
    }

    /**
     * @covers ::import
     * @dataProvider provideValidPaymentCurrencyDefinitions
     *
     * @param array $currencyDefinitions
     */
    public function testImportWithForceOption(array $currencyDefinitions)
    {
        $this->setMockPaymentCurrenciesService();
        $this->setMockDbConnection();

        $schematicPaymentCurrenciesService = new PaymentCurrencies();

        $import = $schematicPaymentCurrenciesService->import($currencyDefinitions, true);

        $this->assertInstanceOf(Result::class, $import);
        $this->assertFalse($import->hasErrors());
    }

    //==============================================================================================================
    //==============================================  PROVIDERS  ===================================================
    //==============================================================================================================

    /**
     * @return array
     */
    public function provideValidPaymentCurrencies()
    {
        return [
            'single currency' => [
                'PaymentCurrencies' => [
                    'currency1' => $this->getMockPaymentCurrency(1),
                ],
                'expectedResult' => [
                    'currencyHandle1' => [
                        'iso' => 'currencyHandle1',
                        'primary' => null,
                        'rate' => null,
                    ],
                ],
            ],
            'multiple currencies' => [
                'PaymentCurrencies' => [
                    'currency1' => $this->getMockPaymentCurrency(1),
                    'currency2' => $this->getMockPaymentCurrency(2),
                ],
                'expectedResult' => [
                    'currencyHandle1' => [
                        'iso' => 'currencyHandle1',
                        'primary' => null,
                        'rate' => null,
                    ],
                    'currencyHandle2' => [
                        'iso' => 'currencyHandle2',
                        'primary' => null,
                        'rate' => null,
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    public function provideValidPaymentCurrencyDefinitions()
    {
        return [
            'emptyArray' => [
                'currencyDefinitions' => [],
            ],
            'single currency' => [
                'currencyDefinitions' => [
                    'currencyHandle1' => [
                        'iso' => 'currencyHandle1',
                        'primary' => null,
                        'rate' => null,
                    ],
                ],
            ],
        ];
    }

    //==============================================================================================================
    //=================================================  MOCKS  ====================================================
    //==============================================================================================================

    /**
     * @param string $currencyId
     *
     * @return Mock|PaymentCurrencyModel
     */
    private function getMockPaymentCurrency($currencyId)
    {
        $mockPaymentCurrency = $this->getMockBuilder(Commerce_PaymentCurrencyModel::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockPaymentCurrency->expects($this->any())
            ->method('__get')
            ->willReturnMap([
                ['id', $currencyId],
                ['iso', 'currencyHandle'.$currencyId],
            ]);

        $mockPaymentCurrency->expects($this->any())
            ->method('getAllErrors')
            ->willReturn([
                'ohnoes' => 'horrible error',
            ]);

        return $mockPaymentCurrency;
    }

    /**
     * @return Mock|CategoriesService
     */
    private function setMockPaymentCurrenciesService()
    {
        $mockPaymentCurrenciesService = $this->getMockBuilder(Commerce_PaymentCurrenciesService::class)
            ->disableOriginalConstructor()
            ->setMethods(['getAllPaymentCurrencies', 'savePaymentCurrency', 'deletePaymentCurrencyById'])
            ->getMock();

        $mockPaymentCurrenciesService->expects($this->any())
            ->method('getAllPaymentCurrencies')
            ->willReturn([]);

        $this->setComponent(Craft::app(), 'commerce_paymentCurrencies', $mockPaymentCurrenciesService);

        return $mockPaymentCurrenciesService;
    }

    /**
     * @return Mock|DbConnection
     */
    private function setMockDbConnection()
    {
        $mockDbConnection = $this->getMockBuilder(DbConnection::class)
            ->disableOriginalConstructor()
            ->setMethods(['createCommand'])
            ->getMock();
        $mockDbConnection->autoConnect = false; // Do not auto connect

        $mockDbCommand = $this->getMockDbCommand();
        $mockDbConnection->expects($this->any())->method('createCommand')->willReturn($mockDbCommand);

        Craft::app()->setComponent('db', $mockDbConnection);

        return $mockDbConnection;
    }

    /**
     * @return Mock|DbCommand
     */
    private function getMockDbCommand()
    {
        $mockDbCommand = $this->getMockBuilder(DbCommand::class)
            ->disableOriginalConstructor()
            ->setMethods(['insertOrUpdate'])
            ->getMock();

        return $mockDbCommand;
    }
}

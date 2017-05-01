<?php

namespace NerdsAndCompany\Schematic\Commerce\Services;

use Craft\BaseTest;
use Craft\Commerce_PaymentMethodModel;
use Craft\Commerce_PaymentMethodsService;
use Craft\Craft;
use Craft\DbCommand;
use Craft\DbConnection;
use NerdsAndCompany\Schematic\Models\Result;
use PHPUnit_Framework_MockObject_MockObject as Mock;

/**
 * Class PaymentMethodsTest.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2017, Nerds & Company
 * @license   MIT
 *
 * @see      http://www.nerds.company
 *
 * @coversDefaultClass \NerdsAndCompany\Schematic\Commerce\Services\PaymentMethods
 * @covers ::__construct
 * @covers ::<!public>
 */
class PaymentMethodsTest extends BaseTest
{
    //==============================================================================================================
    //=================================================  TESTS  ====================================================
    //==============================================================================================================

    /**
     * @covers ::export
     * @dataProvider provideValidPaymentMethods
     *
     * @param PaymentMethodModel[] $methods
     * @param array                $expectedResult
     */
    public function testSuccessfulExport(array $methods, array $expectedResult = [])
    {
        $schematicPaymentMethodsService = new PaymentMethods();

        $actualResult = $schematicPaymentMethodsService->export($methods);

        $this->assertSame($expectedResult, $actualResult);
    }

    /**
     * @covers ::import
     * @dataProvider provideValidPaymentMethodDefinitions
     *
     * @param array $methodDefinitions
     */
    public function testSuccessfulImport(array $methodDefinitions)
    {
        $this->setMockPaymentMethodsService();
        $this->setMockDbConnection();

        $schematicPaymentMethodsService = new PaymentMethods();

        $import = $schematicPaymentMethodsService->import($methodDefinitions);

        $this->assertInstanceOf(Result::class, $import);
        $this->assertFalse($import->hasErrors());
    }

    /**
     * @covers ::import
     * @dataProvider provideValidPaymentMethodDefinitions
     *
     * @param array $methodDefinitions
     */
    public function testImportWithForceOption(array $methodDefinitions)
    {
        $this->setMockPaymentMethodsService();
        $this->setMockDbConnection();

        $schematicPaymentMethodsService = new PaymentMethods();

        $import = $schematicPaymentMethodsService->import($methodDefinitions, true);

        $this->assertInstanceOf(Result::class, $import);
        $this->assertFalse($import->hasErrors());
    }

    //==============================================================================================================
    //==============================================  PROVIDERS  ===================================================
    //==============================================================================================================

    /**
     * @return array
     */
    public function provideValidPaymentMethods()
    {
        return [
            'single method' => [
                'PaymentMethods' => [
                    'method1' => $this->getMockPaymentMethod(1),
                ],
                'expectedResult' => [
                    'methodName1' => [
                        'class' => null,
                        'name' => 'methodName1',
                        'paymentType' => null,
                        'frontendEnabled' => null,
                        'isArchived' => null,
                        'dateArchived' => null,
                        'settings' => null,
                    ],
                ],
            ],
            'multiple methods' => [
                'PaymentMethods' => [
                    'method1' => $this->getMockPaymentMethod(1),
                    'method2' => $this->getMockPaymentMethod(2),
                ],
                'expectedResult' => [
                    'methodName1' => [
                        'class' => null,
                        'name' => 'methodName1',
                        'paymentType' => null,
                        'frontendEnabled' => null,
                        'isArchived' => null,
                        'dateArchived' => null,
                        'settings' => null,
                    ],
                    'methodName2' => [
                        'class' => null,
                        'name' => 'methodName2',
                        'paymentType' => null,
                        'frontendEnabled' => null,
                        'isArchived' => null,
                        'dateArchived' => null,
                        'settings' => null,
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    public function provideValidPaymentMethodDefinitions()
    {
        return [
            'emptyArray' => [
                'methodDefinitions' => [],
            ],
            'single method' => [
                'methodDefinitions' => [
                    'methodName1' => [
                        'class' => null,
                        'name' => 'methodName1',
                        'paymentType' => null,
                        'frontendEnabled' => null,
                        'isArchived' => null,
                        'dateArchived' => null,
                        'settings' => null,
                    ],
                ],
            ],
        ];
    }

    //==============================================================================================================
    //=================================================  MOCKS  ====================================================
    //==============================================================================================================

    /**
     * @param string $methodId
     *
     * @return Mock|Commerce_PaymentMethodModel
     */
    private function getMockPaymentMethod($methodId)
    {
        $mockPaymentMethod = $this->getMockBuilder(Commerce_PaymentMethodModel::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockPaymentMethod->expects($this->any())
            ->method('__get')
            ->willReturnMap([
                ['id', $methodId],
                ['name', 'methodName'.$methodId],
            ]);

        $mockPaymentMethod->expects($this->any())
            ->method('getAllErrors')
            ->willReturn([
                'ohnoes' => 'horrible error',
            ]);

        return $mockPaymentMethod;
    }

    /**
     * @return Mock|Commerce_PaymentMethodsService
     */
    private function setMockPaymentMethodsService()
    {
        $mockPaymentMethodsService = $this->getMockBuilder(Commerce_PaymentMethodsService::class)
            ->disableOriginalConstructor()
            ->setMethods(['getAllPaymentMethods', 'savePaymentMethod', 'archivePaymentMethod'])
            ->getMock();

        $mockPaymentMethodsService->expects($this->any())
            ->method('getAllPaymentMethods')
            ->willReturn([]);

        $this->setComponent(Craft::app(), 'commerce_paymentMethods', $mockPaymentMethodsService);

        return $mockPaymentMethodsService;
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

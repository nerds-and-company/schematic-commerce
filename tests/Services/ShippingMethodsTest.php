<?php

namespace NerdsAndCompany\Schematic\Commerce\Services;

use Craft\BaseTest;
use Craft\Commerce_ShippingMethodModel;
use Craft\Commerce_ShippingMethodsService;
use Craft\Craft;
use Craft\DbCommand;
use Craft\DbConnection;
use NerdsAndCompany\Schematic\Models\Result;
use PHPUnit_Framework_MockObject_MockObject as Mock;

/**
 * Class ShippingMethodsTest.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2017, Nerds & Company
 * @license   MIT
 *
 * @see      http://www.nerds.company
 *
 * @coversDefaultClass \NerdsAndCompany\Schematic\Commerce\Services\ShippingMethods
 * @covers ::__construct
 * @covers ::<!public>
 */
class ShippingMethodsTest extends BaseTest
{
    //==============================================================================================================
    //=================================================  TESTS  ====================================================
    //==============================================================================================================

    /**
     * @covers ::export
     * @dataProvider provideValidShippingMethods
     *
     * @param ShippingMethodModel[] $methods
     * @param array                 $expectedResult
     */
    public function testSuccessfulExport(array $methods, array $expectedResult = [])
    {
        $schematicShippingMethodsService = new ShippingMethods();

        $actualResult = $schematicShippingMethodsService->export($methods);

        $this->assertSame($expectedResult, $actualResult);
    }

    /**
     * @covers ::import
     * @dataProvider provideValidShippingMethodDefinitions
     *
     * @param array $methodDefinitions
     */
    public function testSuccessfulImport(array $methodDefinitions)
    {
        $this->setMockShippingMethodsService();
        $this->setMockDbConnection();

        $schematicShippingMethodsService = new ShippingMethods();

        $import = $schematicShippingMethodsService->import($methodDefinitions);

        $this->assertInstanceOf(Result::class, $import);
        $this->assertFalse($import->hasErrors());
    }

    /**
     * @covers ::import
     * @dataProvider provideValidShippingMethodDefinitions
     *
     * @param array $methodDefinitions
     */
    public function testImportWithForceOption(array $methodDefinitions)
    {
        $this->setMockShippingMethodsService();
        $this->setMockDbConnection();

        $schematicShippingMethodsService = new ShippingMethods();

        $import = $schematicShippingMethodsService->import($methodDefinitions, true);

        $this->assertInstanceOf(Result::class, $import);
        $this->assertFalse($import->hasErrors());
    }

    //==============================================================================================================
    //==============================================  PROVIDERS  ===================================================
    //==============================================================================================================

    /**
     * @return array
     */
    public function provideValidShippingMethods()
    {
        return [
            'single method' => [
                'ShippingMethods' => [
                    'method1' => $this->getMockShippingMethod(1),
                ],
                'expectedResult' => [
                    'methodHandle1' => [
                        'name' => 'methodName1',
                        'enabled' => null,
                        'rules' => [],
                    ],
                ],
            ],
            'multiple methods' => [
                'ShippingMethods' => [
                    'method1' => $this->getMockShippingMethod(1),
                    'method2' => $this->getMockShippingMethod(2),
                ],
                'expectedResult' => [
                    'methodHandle1' => [
                        'name' => 'methodName1',
                        'enabled' => null,
                        'rules' => [],
                    ],
                    'methodHandle2' => [
                        'name' => 'methodName2',
                        'enabled' => null,
                        'rules' => [],
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    public function provideValidShippingMethodDefinitions()
    {
        return [
            'emptyArray' => [
                'methodDefinitions' => [],
            ],
            'single method' => [
                'methodDefinitions' => [
                    'methodHandle1' => [
                        'name' => 'methodName1',
                        'enabled' => null,
                        'rules' => null,
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
     * @return Mock|ShippingMethodModel
     */
    private function getMockShippingMethod($methodId)
    {
        $mockShippingMethod = $this->getMockBuilder(Commerce_ShippingMethodModel::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockShippingMethod->expects($this->any())
            ->method('__get')
            ->willReturnMap([
                ['id', $methodId],
                ['handle', 'methodHandle'.$methodId],
                ['name', 'methodName'.$methodId],
            ]);

        $mockShippingMethod->expects($this->any())
            ->method('getRules')
            ->willReturn([]);

        $mockShippingMethod->expects($this->any())
            ->method('getAllErrors')
            ->willReturn([
                'ohnoes' => 'horrible error',
            ]);

        return $mockShippingMethod;
    }

    /**
     * @return Mock|CategoriesService
     */
    private function setMockShippingMethodsService()
    {
        $mockShippingMethodsService = $this->getMockBuilder(Commerce_ShippingMethodsService::class)
            ->disableOriginalConstructor()
            ->setMethods(['getAllShippingMethods', 'saveShippingMethod', 'delete'])
            ->getMock();

        $mockShippingMethodsService->expects($this->any())
            ->method('getAllShippingMethods')
            ->willReturn([]);

        $this->setComponent(Craft::app(), 'commerce_shippingMethods', $mockShippingMethodsService);

        return $mockShippingMethodsService;
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

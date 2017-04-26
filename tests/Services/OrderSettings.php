<?php

namespace NerdsAndCompany\Schematic\Commerce\Services;

use Craft\BaseTest;
use Craft\Commerce_OrderSettingModel;
use Craft\Commerce_OrderSettingsService;
use Craft\Craft;
use Craft\DbCommand;
use Craft\DbConnection;
use Craft\FieldLayoutModel;
use Craft\FieldsService;
use NerdsAndCompany\Schematic\Models\Result;
use NerdsAndCompany\Schematic\Services\Fields;
use PHPUnit_Framework_MockObject_MockObject as Mock;

/**
 * Class OrderSettingsTest.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2017, Nerds & Company
 * @license   MIT
 *
 * @see      http://www.nerds.company
 *
 * @coversDefaultClass \NerdsAndCompany\Schematic\Commerce\Services\OrderSettings
 * @covers ::__construct
 * @covers ::<!public>
 */
class OrderSettingsTest extends BaseTest
{
    //==============================================================================================================
    //=================================================  TESTS  ====================================================
    //==============================================================================================================

    /**
     * @covers ::export
     * @dataProvider provideValidOrderSettings
     *
     * @param OrderSettingModel[] $types
     * @param array               $expectedResult
     */
    public function testSuccessfulExport(array $types, array $expectedResult = [])
    {
        $this->setMockFieldsService();
        $this->setMockSchematicFields();

        $schematicOrderSettingsService = new OrderSettings();

        $actualResult = $schematicOrderSettingsService->export($types);

        $this->assertSame($expectedResult, $actualResult);
    }

    /**
     * @covers ::import
     * @dataProvider provideValidOrderSettingDefinitions
     *
     * @param array $typeDefinitions
     */
    public function testSuccessfulImport(array $typeDefinitions)
    {
        $this->setMockOrderSettingsService();
        $this->setMockDbConnection();
        $this->setMockSchematicFields();

        $schematicOrderSettingsService = new OrderSettings();

        $import = $schematicOrderSettingsService->import($typeDefinitions);

        $this->assertInstanceOf(Result::class, $import);
        $this->assertFalse($import->hasErrors());
    }

    /**
     * @covers ::import
     * @dataProvider provideValidOrderSettingDefinitions
     *
     * @param array $typeDefinitions
     */
    public function testImportWithForceOption(array $typeDefinitions)
    {
        $this->setMockOrderSettingsService();
        $this->setMockDbConnection();
        $this->setMockSchematicFields();

        $schematicOrderSettingsService = new OrderSettings();

        $import = $schematicOrderSettingsService->import($typeDefinitions, true);

        $this->assertInstanceOf(Result::class, $import);
        $this->assertFalse($import->hasErrors());
    }

    //==============================================================================================================
    //==============================================  PROVIDERS  ===================================================
    //==============================================================================================================

    /**
     * @return array
     */
    public function provideValidOrderSettings()
    {
        return [
            'single setting' => [
                'OrderSettings' => [
                    'setting1' => $this->getMockOrderSetting(),
                ],
                'expectedResult' => [
                    'order' => [
                        'name' => 'Order',
                        'fieldLayout' => [
                            'fields' => [],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    public function provideValidOrderSettingDefinitions()
    {
        return [
            'emptyArray' => [
                'settingDefinitions' => [],
            ],
            'single setting' => [
                'settingDefinitions' => [
                    'order' => [
                        'name' => 'Order',
                        'fieldLayout' => [
                            'fields' => [],
                        ],
                    ],
                ],
            ],
        ];
    }

    //==============================================================================================================
    //=================================================  MOCKS  ====================================================
    //==============================================================================================================

    /**
     * @return Mock|OrderSettingModel
     */
    private function getMockOrderSetting()
    {
        $mockOrderSetting = $this->getMockBuilder(Commerce_OrderSettingModel::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockOrderSetting->expects($this->any())
            ->method('__get')
            ->willReturnMap([
                ['id', 1],
                ['fieldLayoutId', 1],
                ['handle', 'order'],
                ['name', 'Order'],
            ]);

        $mockOrderSetting->expects($this->any())
            ->method('getAllErrors')
            ->willReturn([
                'ohnoes' => 'horrible error',
            ]);

        return $mockOrderSetting;
    }

    /**
     * @return Mock|CraftFieldsService
     */
    private function setMockFieldsService()
    {
        $mockFieldsService = $this->getMockBuilder(FieldsService::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockFieldsService->expects($this->any())
            ->method('getLayoutById')
            ->with($this->isType('integer'))
            ->willReturn($this->getMockFieldLayout());

        $this->setComponent(Craft::app(), 'fields', $mockFieldsService);

        return $mockFieldsService;
    }

    /**
     * @return Mock|fields
     */
    private function setMockSchematicFields()
    {
        $mockSchematicFields = $this->getMockBuilder(Fields::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockSchematicFields->expects($this->any())
            ->method('getFieldLayoutDefinition')
            ->with($this->isInstanceOf(FieldLayoutModel::class))
            ->willReturn(['fields' => []]);

        $mockSchematicFields->expects($this->any())
            ->method('getFieldLayout')
            ->with($this->isType('array'))
            ->willReturn($this->getMockFieldLayout());

        $this->setComponent(Craft::app(), 'schematic_fields', $mockSchematicFields);

        return $mockSchematicFields;
    }

    /**
     * @return Mock|CategoriesService
     */
    private function setMockOrderSettingsService()
    {
        $mockOrderSettingsService = $this->getMockBuilder(Commerce_OrderSettingsService::class)
            ->disableOriginalConstructor()
            ->setMethods(['getOrderSettingByHandle', 'saveOrderSetting'])
            ->getMock();

        $mockOrderSettingsService->expects($this->any())
            ->method('getOrderSettingByHandle')
            ->with('handle')
            ->willReturn([]);

        $this->setComponent(Craft::app(), 'commerce_orderSettings', $mockOrderSettingsService);

        return $mockOrderSettingsService;
    }

    /**
     * @return Mock|FieldLayoutModel
     */
    private function getMockFieldLayout()
    {
        $mockFieldLayout = $this->getMockBuilder(FieldLayoutModel::class)
            ->disableOriginalConstructor()
            ->getMock();

        return $mockFieldLayout;
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

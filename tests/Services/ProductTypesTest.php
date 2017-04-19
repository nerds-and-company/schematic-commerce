<?php

namespace NerdsAndCompany\Schematic\Commerce\Services;

use Craft\BaseTest;
use Craft\Commerce_ProductTypeModel;
use Craft\Commerce_ProductTypeLocaleModel;
use Craft\Commerce_ProductTypesService;
use Craft\Craft;
use Craft\DbCommand;
use Craft\DbConnection;
use Craft\FieldLayoutModel;
use Craft\FieldsService;
use NerdsAndCompany\Schematic\Models\Result;
use NerdsAndCompany\Schematic\Services\Fields;
use PHPUnit_Framework_MockObject_MockObject as Mock;

/**
 * Class ProductTypesTest.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2017, Nerds & Company
 * @license   MIT
 *
 * @see      http://www.nerds.company
 *
 * @coversDefaultClass \NerdsAndCompany\Schematic\Commerce\Services\ProductTypes
 * @covers ::__construct
 * @covers ::<!public>
 */
class ProductTypesTest extends BaseTest
{
    //==============================================================================================================
    //=================================================  TESTS  ====================================================
    //==============================================================================================================

    /**
     * @covers ::export
     * @dataProvider provideValidProductTypes
     *
     * @param ProductTypeModel[] $types
     * @param array              $expectedResult
     */
    public function testSuccessfulExport(array $types, array $expectedResult = [])
    {
        $this->setMockFieldsService();
        $this->setMockSchematicFields();

        $schematicProductTypesService = new ProductTypes();

        $actualResult = $schematicProductTypesService->export($types);

        $this->assertSame($expectedResult, $actualResult);
    }

    /**
     * @covers ::import
     * @dataProvider provideValidProductTypeDefinitions
     *
     * @param array $typeDefinitions
     */
    public function testSuccessfulImport(array $typeDefinitions)
    {
        $this->setMockProductTypesService();
        $this->setMockDbConnection();
        $this->setMockSchematicFields();

        $schematicProductTypesService = new ProductTypes();

        $import = $schematicProductTypesService->import($typeDefinitions);

        $this->assertInstanceOf(Result::class, $import);
        $this->assertFalse($import->hasErrors());
    }

    /**
     * @covers ::import
     * @dataProvider provideValidProductTypeDefinitions
     *
     * @param array $typeDefinitions
     */
    public function testImportWithForceOption(array $typeDefinitions)
    {
        $this->setMockProductTypesService();
        $this->setMockDbConnection();
        $this->setMockSchematicFields();

        $schematicProductTypesService = new ProductTypes();

        $import = $schematicProductTypesService->import($typeDefinitions, true);

        $this->assertInstanceOf(Result::class, $import);
        $this->assertFalse($import->hasErrors());
    }

    //==============================================================================================================
    //==============================================  PROVIDERS  ===================================================
    //==============================================================================================================

    /**
     * @return array
     */
    public function provideValidProductTypes()
    {
        return [
            'single type' => [
                'ProductTypes' => [
                    'type1' => $this->getMockProductType(1),
                ],
                'expectedResult' => [
                    'typeHandle1' => [
                        'name' => 'typeName1',
                        'hasUrls' => null,
                        'hasDimensions' => null,
                        'hasVariants' => null,
                        'hasVariantTitleField' => null,
                        'titleFormat' => null,
                        'skuFormat' => null,
                        'descriptionFormat' => null,
                        'lineItemFormat' => null,
                        'template' => null,
                        'locales' => [
                            'en' => [
                                'urlFormat' => null,
                            ],
                        ],
                        'fieldLayout' => [
                            'fields' => [],
                        ],
                        'variantFieldLayout' => [
                            'fields' => [],
                        ],
                    ],
                ],
            ],
            'multiple types' => [
                'ProductTypes' => [
                    'type1' => $this->getMockProductType(1),
                    'type2' => $this->getMockProductType(2),
                ],
                'expectedResult' => [
                    'typeHandle1' => [
                        'name' => 'typeName1',
                        'hasUrls' => null,
                        'hasDimensions' => null,
                        'hasVariants' => null,
                        'hasVariantTitleField' => null,
                        'titleFormat' => null,
                        'skuFormat' => null,
                        'descriptionFormat' => null,
                        'lineItemFormat' => null,
                        'template' => null,
                        'locales' => [
                            'en' => [
                                'urlFormat' => null,
                            ],
                        ],
                        'fieldLayout' => [
                            'fields' => [],
                        ],
                        'variantFieldLayout' => [
                            'fields' => [],
                        ],
                    ],
                    'typeHandle2' => [
                        'name' => 'typeName2',
                        'hasUrls' => null,
                        'hasDimensions' => null,
                        'hasVariants' => null,
                        'hasVariantTitleField' => null,
                        'titleFormat' => null,
                        'skuFormat' => null,
                        'descriptionFormat' => null,
                        'lineItemFormat' => null,
                        'template' => null,
                        'locales' => [
                            'en' => [
                                'urlFormat' => null,
                            ],
                        ],
                        'fieldLayout' => [
                            'fields' => [],
                        ],
                        'variantFieldLayout' => [
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
    public function provideValidProductTypeDefinitions()
    {
        return [
            'emptyArray' => [
                'typeDefinitions' => [],
            ],
            'single type' => [
                'typeDefinitions' => [
                    'typeHandle1' => [
                        'name' => 'typeName1',
                        'hasUrls' => null,
                        'hasDimensions' => null,
                        'hasVariants' => null,
                        'hasVariantTitleField' => null,
                        'titleFormat' => null,
                        'skuFormat' => null,
                        'descriptionFormat' => null,
                        'lineItemFormat' => null,
                        'template' => null,
                        'locales' => [
                            'en' => [
                                'urlFormat' => null,
                            ],
                        ],
                        'fieldLayout' => [
                            'fields' => [],
                        ],
                        'variantFieldLayout' => [
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
     * @param string $typeId
     *
     * @return Mock|ProductTypeModel
     */
    private function getMockProductType($typeId)
    {
        $mockProductType = $this->getMockBuilder(Commerce_ProductTypeModel::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockProductType->expects($this->any())
            ->method('__get')
            ->willReturnMap([
                ['id', $typeId],
                ['fieldLayoutId', $typeId],
                ['variantFieldLayoutId', $typeId],
                ['handle', 'typeHandle'.$typeId],
                ['name', 'typeName'.$typeId],
            ]);

        $mockProductType->expects($this->any())
            ->method('getLocales')
            ->willReturn([$this->getMockProductTypeLocale()]);

        $mockProductType->expects($this->any())
            ->method('getAllErrors')
            ->willReturn([
                'ohnoes' => 'horrible error',
            ]);

        return $mockProductType;
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
    private function setMockProductTypesService()
    {
        $mockProductTypesService = $this->getMockBuilder(Commerce_ProductTypesService::class)
            ->disableOriginalConstructor()
            ->setMethods(['getAllProductTypes', 'saveProductType', 'deleteProductTypeById'])
            ->getMock();

        $mockProductTypesService->expects($this->any())
            ->method('getAllProductTypes')
            ->with('handle')
            ->willReturn([]);

        $this->setComponent(Craft::app(), 'commerce_productTypes', $mockProductTypesService);

        return $mockProductTypesService;
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
     * @return Mock|ProductTypeLocaleModel
     */
    private function getMockProductTypeLocale()
    {
        $mockProductTypeLocale = $this->getMockBuilder(Commerce_ProductTypeLocaleModel::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockProductTypeLocale->expects($this->any())
            ->method('__get')
            ->willReturnMap([
                ['locale', 'en'],
            ]);

        return $mockProductTypeLocale;
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

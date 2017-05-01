<?php

namespace NerdsAndCompany\Schematic\Commerce\Services;

use Craft\BaseTest;
use Craft\Commerce_ShippingCategoryModel;
use Craft\Commerce_ShippingCategoriesService;
use Craft\Craft;
use Craft\DbCommand;
use Craft\DbConnection;
use NerdsAndCompany\Schematic\Models\Result;
use PHPUnit_Framework_MockObject_MockObject as Mock;

/**
 * Class ShippingCategoriesTest.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2017, Nerds & Company
 * @license   MIT
 *
 * @see      http://www.nerds.company
 *
 * @coversDefaultClass \NerdsAndCompany\Schematic\Commerce\Services\ShippingCategories
 * @covers ::__construct
 * @covers ::<!public>
 */
class ShippingCategoriesTest extends BaseTest
{
    //==============================================================================================================
    //=================================================  TESTS  ====================================================
    //==============================================================================================================

    /**
     * @covers ::export
     * @dataProvider provideValidShippingCategories
     *
     * @param ShippingCategoryModel[] $categories
     * @param array                   $expectedResult
     */
    public function testSuccessfulExport(array $categories, array $expectedResult = [])
    {
        $schematicShippingCategoriesService = new ShippingCategories();

        $actualResult = $schematicShippingCategoriesService->export($categories);

        $this->assertSame($expectedResult, $actualResult);
    }

    /**
     * @covers ::import
     * @dataProvider provideValidShippingCategoryDefinitions
     *
     * @param array $categoryDefinitions
     */
    public function testSuccessfulImport(array $categoryDefinitions)
    {
        $this->setMockShippingCategoriesService();
        $this->setMockDbConnection();

        $schematicShippingCategoriesService = new ShippingCategories();

        $import = $schematicShippingCategoriesService->import($categoryDefinitions);

        $this->assertInstanceOf(Result::class, $import);
        $this->assertFalse($import->hasErrors());
    }

    /**
     * @covers ::import
     * @dataProvider provideValidShippingCategoryDefinitions
     *
     * @param array $categoryDefinitions
     */
    public function testImportWithForceOption(array $categoryDefinitions)
    {
        $this->setMockShippingCategoriesService();
        $this->setMockDbConnection();

        $schematicShippingCategoriesService = new ShippingCategories();

        $import = $schematicShippingCategoriesService->import($categoryDefinitions, true);

        $this->assertInstanceOf(Result::class, $import);
        $this->assertFalse($import->hasErrors());
    }

    //==============================================================================================================
    //==============================================  PROVIDERS  ===================================================
    //==============================================================================================================

    /**
     * @return array
     */
    public function provideValidShippingCategories()
    {
        return [
            'single category' => [
                'ShippingCategories' => [
                    'category1' => $this->getMockShippingCategory(1),
                ],
                'expectedResult' => [
                    'categoryHandle1' => [
                        'name' => 'categoryName1',
                        'description' => null,
                        'default' => null,
                    ],
                ],
            ],
            'multiple categories' => [
                'ShippingCategories' => [
                    'category1' => $this->getMockShippingCategory(1),
                    'category2' => $this->getMockShippingCategory(2),
                ],
                'expectedResult' => [
                    'categoryHandle1' => [
                        'name' => 'categoryName1',
                        'description' => null,
                        'default' => null,
                    ],
                    'categoryHandle2' => [
                        'name' => 'categoryName2',
                        'description' => null,
                        'default' => null,
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    public function provideValidShippingCategoryDefinitions()
    {
        return [
            'emptyArray' => [
                'categoryDefinitions' => [],
            ],
            'single category' => [
                'categoryDefinitions' => [
                    'categoryHandle1' => [
                        'name' => 'categoryName1',
                        'description' => null,
                        'default' => null,
                    ],
                ],
            ],
        ];
    }

    //==============================================================================================================
    //=================================================  MOCKS  ====================================================
    //==============================================================================================================

    /**
     * @param string $categoryId
     *
     * @return Mock|Commerce_ShippingCategoryModel
     */
    private function getMockShippingCategory($categoryId)
    {
        $mockShippingCategory = $this->getMockBuilder(Commerce_ShippingCategoryModel::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockShippingCategory->expects($this->any())
            ->method('__get')
            ->willReturnMap([
                ['id', $categoryId],
                ['handle', 'categoryHandle'.$categoryId],
                ['name', 'categoryName'.$categoryId],
            ]);

        $mockShippingCategory->expects($this->any())
            ->method('getAllErrors')
            ->willReturn([
                'ohnoes' => 'horrible error',
            ]);

        return $mockShippingCategory;
    }

    /**
     * @return Mock|Commerce_ShippingCategoriesService
     */
    private function setMockShippingCategoriesService()
    {
        $mockShippingCategoriesService = $this->getMockBuilder(Commerce_ShippingCategoriesService::class)
            ->disableOriginalConstructor()
            ->setMethods(['getAllShippingCategories', 'saveShippingCategory', 'deleteShippingCategoryById'])
            ->getMock();

        $mockShippingCategoriesService->expects($this->any())
            ->method('getAllShippingCategories')
            ->with('handle')
            ->willReturn([]);

        $this->setComponent(Craft::app(), 'commerce_shippingCategories', $mockShippingCategoriesService);

        return $mockShippingCategoriesService;
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

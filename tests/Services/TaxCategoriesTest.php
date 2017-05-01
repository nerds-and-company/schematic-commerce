<?php

namespace NerdsAndCompany\Schematic\Commerce\Services;

use Craft\BaseTest;
use Craft\Commerce_TaxCategoryModel;
use Craft\Commerce_TaxCategoriesService;
use Craft\Craft;
use Craft\DbCommand;
use Craft\DbConnection;
use NerdsAndCompany\Schematic\Models\Result;
use PHPUnit_Framework_MockObject_MockObject as Mock;

/**
 * Class TaxCategoriesTest.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2017, Nerds & Company
 * @license   MIT
 *
 * @see      http://www.nerds.company
 *
 * @coversDefaultClass \NerdsAndCompany\Schematic\Commerce\Services\TaxCategories
 * @covers ::__construct
 * @covers ::<!public>
 */
class TaxCategoriesTest extends BaseTest
{
    //==============================================================================================================
    //=================================================  TESTS  ====================================================
    //==============================================================================================================

    /**
     * @covers ::export
     * @dataProvider provideValidTaxCategories
     *
     * @param TaxCategoryModel[] $categories
     * @param array              $expectedResult
     */
    public function testSuccessfulExport(array $categories, array $expectedResult = [])
    {
        $schematicTaxCategoriesService = new TaxCategories();

        $actualResult = $schematicTaxCategoriesService->export($categories);

        $this->assertSame($expectedResult, $actualResult);
    }

    /**
     * @covers ::import
     * @dataProvider provideValidTaxCategoryDefinitions
     *
     * @param array $categoryDefinitions
     */
    public function testSuccessfulImport(array $categoryDefinitions)
    {
        $this->setMockTaxCategoriesService();
        $this->setMockDbConnection();

        $schematicTaxCategoriesService = new TaxCategories();

        $import = $schematicTaxCategoriesService->import($categoryDefinitions);

        $this->assertInstanceOf(Result::class, $import);
        $this->assertFalse($import->hasErrors());
    }

    /**
     * @covers ::import
     * @dataProvider provideValidTaxCategoryDefinitions
     *
     * @param array $categoryDefinitions
     */
    public function testImportWithForceOption(array $categoryDefinitions)
    {
        $this->setMockTaxCategoriesService();
        $this->setMockDbConnection();

        $schematicTaxCategoriesService = new TaxCategories();

        $import = $schematicTaxCategoriesService->import($categoryDefinitions, true);

        $this->assertInstanceOf(Result::class, $import);
        $this->assertFalse($import->hasErrors());
    }

    //==============================================================================================================
    //==============================================  PROVIDERS  ===================================================
    //==============================================================================================================

    /**
     * @return array
     */
    public function provideValidTaxCategories()
    {
        return [
            'single category' => [
                'TaxCategories' => [
                    'category1' => $this->getMockTaxCategory(1),
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
                'TaxCategories' => [
                    'category1' => $this->getMockTaxCategory(1),
                    'category2' => $this->getMockTaxCategory(2),
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
    public function provideValidTaxCategoryDefinitions()
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
     * @return Mock|Commerce_TaxCategoryModel
     */
    private function getMockTaxCategory($categoryId)
    {
        $mockTaxCategory = $this->getMockBuilder(Commerce_TaxCategoryModel::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockTaxCategory->expects($this->any())
            ->method('__get')
            ->willReturnMap([
                ['id', $categoryId],
                ['handle', 'categoryHandle'.$categoryId],
                ['name', 'categoryName'.$categoryId],
            ]);

        $mockTaxCategory->expects($this->any())
            ->method('getAllErrors')
            ->willReturn([
                'ohnoes' => 'horrible error',
            ]);

        return $mockTaxCategory;
    }

    /**
     * @return Mock|Commerce_TaxCategoriesService
     */
    private function setMockTaxCategoriesService()
    {
        $mockTaxCategoriesService = $this->getMockBuilder(Commerce_TaxCategoriesService::class)
            ->disableOriginalConstructor()
            ->setMethods(['getAllTaxCategories', 'saveTaxCategory', 'deleteTaxCategoryById'])
            ->getMock();

        $mockTaxCategoriesService->expects($this->any())
            ->method('getAllTaxCategories')
            ->with('handle')
            ->willReturn([]);

        $this->setComponent(Craft::app(), 'commerce_taxCategories', $mockTaxCategoriesService);

        return $mockTaxCategoriesService;
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

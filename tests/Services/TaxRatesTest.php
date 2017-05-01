<?php

namespace NerdsAndCompany\Schematic\Commerce\Services;

use Craft\BaseTest;
use Craft\Commerce_TaxCategoryModel;
use Craft\Commerce_TaxRateModel;
use Craft\Commerce_TaxZoneModel;
use Craft\Commerce_TaxCategoriesService;
use Craft\Commerce_TaxRatesService;
use Craft\Commerce_TaxZonesService;
use Craft\Craft;
use Craft\DbCommand;
use Craft\DbConnection;
use NerdsAndCompany\Schematic\Models\Result;
use PHPUnit_Framework_MockObject_MockObject as Mock;

/**
 * Class TaxRatesTest.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2017, Nerds & Company
 * @license   MIT
 *
 * @see      http://www.nerds.company
 *
 * @coversDefaultClass \NerdsAndCompany\Schematic\Commerce\Services\TaxRates
 * @covers ::__construct
 * @covers ::<!public>
 */
class TaxRatesTest extends BaseTest
{
    //==============================================================================================================
    //=================================================  TESTS  ====================================================
    //==============================================================================================================

    /**
     * @covers ::export
     * @dataProvider provideValidTaxRates
     *
     * @param TaxRateModel[] $rates
     * @param array          $expectedResult
     */
    public function testSuccessfulExport(array $rates, array $expectedResult = [])
    {
        $schematicTaxRatesService = new TaxRates();

        $actualResult = $schematicTaxRatesService->export($rates);

        $this->assertSame($expectedResult, $actualResult);
    }

    /**
     * @covers ::import
     * @dataProvider provideValidTaxRateDefinitions
     *
     * @param array $rateDefinitions
     */
    public function testSuccessfulImport(array $rateDefinitions)
    {
        $this->setMockTaxRatesService();
        $this->setMockTaxCategoriesService();
        $this->setMockTaxZonesService();
        $this->setMockDbConnection();

        $schematicTaxRatesService = new TaxRates();

        $import = $schematicTaxRatesService->import($rateDefinitions);

        $this->assertInstanceOf(Result::class, $import);
        $this->assertFalse($import->hasErrors());
    }

    /**
     * @covers ::import
     * @dataProvider provideValidTaxRateDefinitions
     *
     * @param array $rateDefinitions
     */
    public function testImportWithForceOption(array $rateDefinitions)
    {
        $this->setMockTaxRatesService();
        $this->setMockTaxCategoriesService();
        $this->setMockTaxZonesService();
        $this->setMockDbConnection();

        $schematicTaxRatesService = new TaxRates();

        $import = $schematicTaxRatesService->import($rateDefinitions, true);

        $this->assertInstanceOf(Result::class, $import);
        $this->assertFalse($import->hasErrors());
    }

    //==============================================================================================================
    //==============================================  PROVIDERS  ===================================================
    //==============================================================================================================

    /**
     * @return array
     */
    public function provideValidTaxRates()
    {
        return [
            'single rate' => [
                'TaxRates' => [
                    'rate1' => $this->getMockTaxRate(1),
                ],
                'expectedResult' => [
                    'rateName1' => [
                        'name' => 'rateName1',
                        'rate' => null,
                        'include' => null,
                        'isVat' => null,
                        'taxable' => null,
                        'taxCategory' => 'categoryHandle1',
                        'taxZone' => 'zoneName1',
                    ],
                ],
            ],
            'multiple rates' => [
                'TaxRates' => [
                    'rate1' => $this->getMockTaxRate(1),
                    'rate2' => $this->getMockTaxRate(2),
                ],
                'expectedResult' => [
                    'rateName1' => [
                        'name' => 'rateName1',
                        'rate' => null,
                        'include' => null,
                        'isVat' => null,
                        'taxable' => null,
                        'taxCategory' => 'categoryHandle1',
                        'taxZone' => 'zoneName1',
                    ],
                    'rateName2' => [
                        'name' => 'rateName2',
                        'rate' => null,
                        'include' => null,
                        'isVat' => null,
                        'taxable' => null,
                        'taxCategory' => 'categoryHandle2',
                        'taxZone' => 'zoneName2',
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    public function provideValidTaxRateDefinitions()
    {
        return [
            'emptyArray' => [
                'rateDefinitions' => [],
            ],
            'single rate' => [
                'rateDefinitions' => [
                    'rateName1' => [
                        'name' => 'rateName1',
                        'rate' => null,
                        'include' => null,
                        'isVat' => null,
                        'taxable' => null,
                        'taxCategory' => 'categoryHandle1',
                        'taxZone' => 'zoneName1',
                    ],
                ],
            ],
        ];
    }

    //==============================================================================================================
    //=================================================  MOCKS  ====================================================
    //==============================================================================================================

    /**
     * @param string $rateId
     *
     * @return Mock|Commerce_TaxRateModel
     */
    private function getMockTaxRate($rateId)
    {
        $mockTaxRate = $this->getMockBuilder(Commerce_TaxRateModel::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockTaxRate->expects($this->any())
            ->method('__get')
            ->willReturnMap([
                ['id', $rateId],
                ['name', 'rateName'.$rateId],
            ]);

        $mockTaxRate->expects($this->any())
            ->method('getTaxCategory')
            ->willReturn($this->getMockTaxCategory($rateId));

        $mockTaxRate->expects($this->any())
            ->method('getTaxZone')
            ->willReturn($this->getMockTaxZone($rateId));

        $mockTaxRate->expects($this->any())
            ->method('getAllErrors')
            ->willReturn([
                'ohnoes' => 'horrible error',
            ]);

        return $mockTaxRate;
    }

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
            ]);

        return $mockTaxCategory;
    }

    /**
     * @param string $zoneId
     *
     * @return Mock|Commerce_TaxZoneModel
     */
    private function getMockTaxZone($zoneId)
    {
        $mockTaxZone = $this->getMockBuilder(Commerce_TaxZoneModel::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockTaxZone->expects($this->any())
            ->method('__get')
            ->willReturnMap([
                ['id', $zoneId],
                ['name', 'zoneName'.$zoneId],
            ]);

        return $mockTaxZone;
    }

    /**
     * @return Mock|Commerce_TaxRatesService
     */
    private function setMockTaxRatesService()
    {
        $mockTaxRatesService = $this->getMockBuilder(Commerce_TaxRatesService::class)
            ->disableOriginalConstructor()
            ->setMethods(['getAllTaxRates', 'saveTaxRate', 'deleteTaxRateById'])
            ->getMock();

        $mockTaxRatesService->expects($this->any())
            ->method('getAllTaxRates')
            ->willReturn([]);

        $this->setComponent(Craft::app(), 'commerce_taxRates', $mockTaxRatesService);

        return $mockTaxRatesService;
    }

    /**
     * @return Mock|Commerce_TaxCategoriesService
     */
    private function setMockTaxCategoriesService()
    {
        $mockTaxCategoriesService = $this->getMockBuilder(Commerce_TaxCategoriesService::class)
            ->disableOriginalConstructor()
            ->setMethods(['getTaxCategoryByHandle'])
            ->getMock();

        $mockTaxCategoriesService->expects($this->any())
            ->method('getTaxCategoryByHandle')
            ->willReturn($this->getMockTaxCategory(1));

        $this->setComponent(Craft::app(), 'commerce_taxCategories', $mockTaxCategoriesService);

        return $mockTaxCategoriesService;
    }

    /**
     * @return Mock|Commerce_TaxZonesService
     */
    private function setMockTaxZonesService()
    {
        $mockTaxZonesService = $this->getMockBuilder(Commerce_TaxZonesService::class)
            ->disableOriginalConstructor()
            ->setMethods(['getAllTaxZones'])
            ->getMock();

        $mockTaxZonesService->expects($this->any())
            ->method('getAllTaxZones')
            ->willReturn([]);

        $this->setComponent(Craft::app(), 'commerce_taxZones', $mockTaxZonesService);

        return $mockTaxZonesService;
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

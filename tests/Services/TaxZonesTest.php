<?php

namespace NerdsAndCompany\Schematic\Commerce\Services;

use Craft\BaseTest;
use Craft\Commerce_TaxZoneModel;
use Craft\Commerce_TaxZonesService;
use Craft\Craft;
use Craft\DbCommand;
use Craft\DbConnection;
use NerdsAndCompany\Schematic\Models\Result;
use PHPUnit_Framework_MockObject_MockObject as Mock;

/**
 * Class TaxZonesTest.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2017, Nerds & Company
 * @license   MIT
 *
 * @see      http://www.nerds.company
 *
 * @coversDefaultClass \NerdsAndCompany\Schematic\Commerce\Services\TaxZones
 * @covers ::__construct
 * @covers ::<!public>
 */
class TaxZonesTest extends BaseTest
{
    //==============================================================================================================
    //=================================================  TESTS  ====================================================
    //==============================================================================================================

    /**
     * @covers ::export
     * @dataProvider provideValidTaxZones
     *
     * @param TaxZoneModel[] $zones
     * @param array          $expectedResult
     */
    public function testSuccessfulExport(array $zones, array $expectedResult = [])
    {
        $schematicTaxZonesService = new TaxZones();

        $actualResult = $schematicTaxZonesService->export($zones);

        $this->assertSame($expectedResult, $actualResult);
    }

    /**
     * @covers ::import
     * @dataProvider provideValidTaxZoneDefinitions
     *
     * @param array $zoneDefinitions
     */
    public function testSuccessfulImport(array $zoneDefinitions)
    {
        $this->setMockTaxZonesService();
        $this->setMockDbConnection();

        $schematicTaxZonesService = new TaxZones();

        $import = $schematicTaxZonesService->import($zoneDefinitions);

        $this->assertInstanceOf(Result::class, $import);
        $this->assertFalse($import->hasErrors());
    }

    /**
     * @covers ::import
     * @dataProvider provideValidTaxZoneDefinitions
     *
     * @param array $zoneDefinitions
     */
    public function testImportWithForceOption(array $zoneDefinitions)
    {
        $this->setMockTaxZonesService();
        $this->setMockDbConnection();

        $schematicTaxZonesService = new TaxZones();

        $import = $schematicTaxZonesService->import($zoneDefinitions, true);

        $this->assertInstanceOf(Result::class, $import);
        $this->assertFalse($import->hasErrors());
    }

    //==============================================================================================================
    //==============================================  PROVIDERS  ===================================================
    //==============================================================================================================

    /**
     * @return array
     */
    public function provideValidTaxZones()
    {
        return [
            'single zone' => [
                'TaxZones' => [
                    'zone1' => $this->getMockTaxZone(1),
                ],
                'expectedResult' => [
                    'zoneName1' => [
                        'name' => 'zoneName1',
                        'description' => null,
                        'countryBased' => null,
                        'default' => null,
                        'countries' => array(),
                        'states' => array(),
                    ],
                ],
            ],
            'multiple zones' => [
                'TaxZones' => [
                    'zone1' => $this->getMockTaxZone(1),
                    'zone2' => $this->getMockTaxZone(2),
                ],
                'expectedResult' => [
                    'zoneName1' => [
                        'name' => 'zoneName1',
                        'description' => null,
                        'countryBased' => null,
                        'default' => null,
                        'countries' => array(),
                        'states' => array(),
                    ],
                    'zoneName2' => [
                        'name' => 'zoneName2',
                        'description' => null,
                        'countryBased' => null,
                        'default' => null,
                        'countries' => array(),
                        'states' => array(),
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    public function provideValidTaxZoneDefinitions()
    {
        return [
            'emptyArray' => [
                'zoneDefinitions' => [],
            ],
            'single zone' => [
                'zoneDefinitions' => [
                    'zoneName1' => [
                        'name' => 'zoneName1',
                        'description' => null,
                        'countryBased' => null,
                        'default' => null,
                        'countries' => array(),
                        'states' => array(),
                    ],
                ],
            ],
        ];
    }

    //==============================================================================================================
    //=================================================  MOCKS  ====================================================
    //==============================================================================================================

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

        $mockTaxZone->expects($this->any())
            ->method('getCountries')
            ->willReturn([]);

        $mockTaxZone->expects($this->any())
            ->method('getStates')
            ->willReturn([]);

        $mockTaxZone->expects($this->any())
            ->method('getAllErrors')
            ->willReturn([
                'ohnoes' => 'horrible error',
            ]);

        return $mockTaxZone;
    }

    /**
     * @return Mock|Commerce_TaxZonesService
     */
    private function setMockTaxZonesService()
    {
        $mockTaxZonesService = $this->getMockBuilder(Commerce_TaxZonesService::class)
            ->disableOriginalConstructor()
            ->setMethods(['getAllTaxZones', 'saveTaxZone', 'deleteTaxZoneById'])
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

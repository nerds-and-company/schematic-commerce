<?php

namespace NerdsAndCompany\Schematic\Commerce\Services;

use Craft\BaseTest;
use Craft\Commerce_ShippingZoneModel;
use Craft\Commerce_ShippingZonesService;
use Craft\Craft;
use Craft\DbCommand;
use Craft\DbConnection;
use NerdsAndCompany\Schematic\Models\Result;
use PHPUnit_Framework_MockObject_MockObject as Mock;

/**
 * Class ShippingZonesTest.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2017, Nerds & Company
 * @license   MIT
 *
 * @see      http://www.nerds.company
 *
 * @coversDefaultClass \NerdsAndCompany\Schematic\Commerce\Services\ShippingZones
 * @covers ::__construct
 * @covers ::<!public>
 */
class ShippingZonesTest extends BaseTest
{
    //==============================================================================================================
    //=================================================  TESTS  ====================================================
    //==============================================================================================================

    /**
     * @covers ::export
     * @dataProvider provideValidShippingZones
     *
     * @param ShippingZoneModel[] $zones
     * @param array               $expectedResult
     */
    public function testSuccessfulExport(array $zones, array $expectedResult = [])
    {
        $schematicShippingZonesService = new ShippingZones();

        $actualResult = $schematicShippingZonesService->export($zones);

        $this->assertSame($expectedResult, $actualResult);
    }

    /**
     * @covers ::import
     * @dataProvider provideValidShippingZoneDefinitions
     *
     * @param array $zoneDefinitions
     */
    public function testSuccessfulImport(array $zoneDefinitions)
    {
        $this->setMockShippingZonesService();
        $this->setMockDbConnection();

        $schematicShippingZonesService = new ShippingZones();

        $import = $schematicShippingZonesService->import($zoneDefinitions);

        $this->assertInstanceOf(Result::class, $import);
        $this->assertFalse($import->hasErrors());
    }

    /**
     * @covers ::import
     * @dataProvider provideValidShippingZoneDefinitions
     *
     * @param array $zoneDefinitions
     */
    public function testImportWithForceOption(array $zoneDefinitions)
    {
        $this->setMockShippingZonesService();
        $this->setMockDbConnection();

        $schematicShippingZonesService = new ShippingZones();

        $import = $schematicShippingZonesService->import($zoneDefinitions, true);

        $this->assertInstanceOf(Result::class, $import);
        $this->assertFalse($import->hasErrors());
    }

    //==============================================================================================================
    //==============================================  PROVIDERS  ===================================================
    //==============================================================================================================

    /**
     * @return array
     */
    public function provideValidShippingZones()
    {
        return [
            'single zone' => [
                'ShippingZones' => [
                    'zone1' => $this->getMockShippingZone(1),
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
                'ShippingZones' => [
                    'zone1' => $this->getMockShippingZone(1),
                    'zone2' => $this->getMockShippingZone(2),
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
    public function provideValidShippingZoneDefinitions()
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
     * @return Mock|Commerce_ShippingZoneModel
     */
    private function getMockShippingZone($zoneId)
    {
        $mockShippingZone = $this->getMockBuilder(Commerce_ShippingZoneModel::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockShippingZone->expects($this->any())
            ->method('__get')
            ->willReturnMap([
                ['id', $zoneId],
                ['name', 'zoneName'.$zoneId],
            ]);

        $mockShippingZone->expects($this->any())
            ->method('getCountries')
            ->willReturn([]);

        $mockShippingZone->expects($this->any())
            ->method('getStates')
            ->willReturn([]);

        $mockShippingZone->expects($this->any())
            ->method('getAllErrors')
            ->willReturn([
                'ohnoes' => 'horrible error',
            ]);

        return $mockShippingZone;
    }

    /**
     * @return Mock|Commerce_ShippingZonesService
     */
    private function setMockShippingZonesService()
    {
        $mockShippingZonesService = $this->getMockBuilder(Commerce_ShippingZonesService::class)
            ->disableOriginalConstructor()
            ->setMethods(['getAllShippingZones', 'saveShippingZone', 'deleteShippingZoneById'])
            ->getMock();

        $mockShippingZonesService->expects($this->any())
            ->method('getAllShippingZones')
            ->willReturn([]);

        $this->setComponent(Craft::app(), 'commerce_shippingZones', $mockShippingZonesService);

        return $mockShippingZonesService;
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

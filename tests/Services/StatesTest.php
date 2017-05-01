<?php

namespace NerdsAndCompany\Schematic\Commerce\Services;

use Craft\BaseTest;
use Craft\Commerce_CountryModel;
use Craft\Commerce_StateModel;
use Craft\Commerce_CountriesService;
use Craft\Commerce_StatesService;
use Craft\Craft;
use Craft\DbCommand;
use Craft\DbConnection;
use NerdsAndCompany\Schematic\Models\Result;
use PHPUnit_Framework_MockObject_MockObject as Mock;

/**
 * Class StatesTest.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2017, Nerds & Company
 * @license   MIT
 *
 * @see      http://www.nerds.company
 *
 * @coversDefaultClass \NerdsAndCompany\Schematic\Commerce\Services\States
 * @covers ::__construct
 * @covers ::<!public>
 */
class StatesTest extends BaseTest
{
    //==============================================================================================================
    //=================================================  TESTS  ====================================================
    //==============================================================================================================

    /**
     * @covers ::export
     * @dataProvider provideValidStates
     *
     * @param StateModel[] $states
     * @param array        $expectedResult
     */
    public function testSuccessfulExport(array $states, array $expectedResult = [])
    {
        $schematicStatesService = new States();

        $actualResult = $schematicStatesService->export($states);

        $this->assertSame($expectedResult, $actualResult);
    }

    /**
     * @covers ::import
     * @dataProvider provideValidStateDefinitions
     *
     * @param array $stateDefinitions
     */
    public function testSuccessfulImport(array $stateDefinitions)
    {
        $this->setMockStatesService();
        $this->setMockCountriesService();
        $this->setMockDbConnection();

        $schematicStatesService = new States();

        $import = $schematicStatesService->import($stateDefinitions);

        $this->assertInstanceOf(Result::class, $import);
        $this->assertFalse($import->hasErrors());
    }

    /**
     * @covers ::import
     * @dataProvider provideValidStateDefinitions
     *
     * @param array $stateDefinitions
     */
    public function testImportWithForceOption(array $stateDefinitions)
    {
        $this->setMockStatesService();
        $this->setMockCountriesService();
        $this->setMockDbConnection();

        $schematicStatesService = new States();

        $import = $schematicStatesService->import($stateDefinitions, true);

        $this->assertInstanceOf(Result::class, $import);
        $this->assertFalse($import->hasErrors());
    }

    //==============================================================================================================
    //==============================================  PROVIDERS  ===================================================
    //==============================================================================================================

    /**
     * @return array
     */
    public function provideValidStates()
    {
        return [
            'single state' => [
                'States' => [
                    'state1' => $this->getMockState(1),
                ],
                'expectedResult' => [
                    'stateAbbr1' => [
                        'name' => 'stateName1',
                        'country' => 'countryIso1',
                    ],
                ],
            ],
            'multiple states' => [
                'States' => [
                    'state1' => $this->getMockState(1),
                    'state2' => $this->getMockState(2),
                ],
                'expectedResult' => [
                    'stateAbbr1' => [
                        'name' => 'stateName1',
                        'country' => 'countryIso1',
                    ],
                    'stateAbbr2' => [
                        'name' => 'stateName2',
                        'country' => 'countryIso2',
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    public function provideValidStateDefinitions()
    {
        return [
            'emptyArray' => [
                'stateDefinitions' => [],
            ],
            'single state' => [
                'stateDefinitions' => [
                    'stateAbbr1' => [
                        'name' => 'stateName1',
                        'country' => 'countryIso1',
                    ],
                ],
            ],
        ];
    }

    //==============================================================================================================
    //=================================================  MOCKS  ====================================================
    //==============================================================================================================

    /**
     * @param string $stateId
     *
     * @return Mock|StateModel
     */
    private function getMockState($stateId)
    {
        $mockState = $this->getMockBuilder(Commerce_StateModel::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockState->expects($this->any())
            ->method('__get')
            ->willReturnMap([
                ['id', $stateId],
                ['abbreviation', 'stateAbbr'.$stateId],
                ['name', 'stateName'.$stateId],
            ]);

        $mockState->expects($this->any())
            ->method('getCountry')
            ->willReturn($this->getMockCountry($stateId));

        $mockState->expects($this->any())
            ->method('getAllErrors')
            ->willReturn([
                'ohnoes' => 'horrible error',
            ]);

        return $mockState;
    }

    /**
     * @param string $countryId
     *
     * @return Mock|Commerce_CountryModel
     */
    private function getMockCountry($countryId)
    {
        $mockCountry = $this->getMockBuilder(Commerce_CountryModel::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockCountry->expects($this->any())
            ->method('__get')
            ->willReturnMap([
                ['id', $countryId],
                ['iso', 'countryIso'.$countryId],
            ]);

        return $mockCountry;
    }

    /**
     * @return Mock|Commerce_StatesService
     */
    private function setMockStatesService()
    {
        $mockStatesService = $this->getMockBuilder(Commerce_StatesService::class)
            ->disableOriginalConstructor()
            ->setMethods(['getAllStates', 'saveState', 'deleteStateById'])
            ->getMock();

        $mockStatesService->expects($this->any())
            ->method('getAllStates')
            ->willReturn([]);

        $this->setComponent(Craft::app(), 'commerce_states', $mockStatesService);

        return $mockStatesService;
    }

    /**
     * @return Mock|Commerce_CountriesService
     */
    private function setMockCountriesService()
    {
        $mockCountriesService = $this->getMockBuilder(Commerce_CountriesService::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCountryByAttributes'])
            ->getMock();

        $mockCountriesService->expects($this->any())
            ->method('getCountryByAttributes')
            ->willReturn($this->getMockCountry(1));

        $this->setComponent(Craft::app(), 'commerce_countries', $mockCountriesService);

        return $mockCountriesService;
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

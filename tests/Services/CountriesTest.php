<?php

namespace NerdsAndCompany\Schematic\Commerce\Services;

use Craft\BaseTest;
use Craft\Commerce_CountryModel;
use Craft\Commerce_CountriesService;
use Craft\Craft;
use Craft\DbCommand;
use Craft\DbConnection;
use NerdsAndCompany\Schematic\Models\Result;
use PHPUnit_Framework_MockObject_MockObject as Mock;

/**
 * Class CountriesTest.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2017, Nerds & Company
 * @license   MIT
 *
 * @see      http://www.nerds.company
 *
 * @coversDefaultClass \NerdsAndCompany\Schematic\Commerce\Services\Countries
 * @covers ::__construct
 * @covers ::<!public>
 */
class CountriesTest extends BaseTest
{
    //==============================================================================================================
    //=================================================  TESTS  ====================================================
    //==============================================================================================================

    /**
     * @covers ::export
     * @dataProvider provideValidCountries
     *
     * @param CountryModel[] $countries
     * @param array          $expectedResult
     */
    public function testSuccessfulExport(array $countries, array $expectedResult = [])
    {
        $schematicCountriesService = new Countries();

        $actualResult = $schematicCountriesService->export($countries);

        $this->assertSame($expectedResult, $actualResult);
    }

    /**
     * @covers ::import
     * @dataProvider provideValidCountryDefinitions
     *
     * @param array $countryDefinitions
     */
    public function testSuccessfulImport(array $countryDefinitions)
    {
        $this->setMockCountriesService();
        $this->setMockDbConnection();

        $schematicCountriesService = new Countries();

        $import = $schematicCountriesService->import($countryDefinitions);

        $this->assertInstanceOf(Result::class, $import);
        $this->assertFalse($import->hasErrors());
    }

    /**
     * @covers ::import
     * @dataProvider provideValidCountryDefinitions
     *
     * @param array $countryDefinitions
     */
    public function testImportWithForceOption(array $countryDefinitions)
    {
        $this->setMockCountriesService();
        $this->setMockDbConnection();

        $schematicCountriesService = new Countries();

        $import = $schematicCountriesService->import($countryDefinitions, true);

        $this->assertInstanceOf(Result::class, $import);
        $this->assertFalse($import->hasErrors());
    }

    //==============================================================================================================
    //==============================================  PROVIDERS  ===================================================
    //==============================================================================================================

    /**
     * @return array
     */
    public function provideValidCountries()
    {
        return [
            'single country' => [
                'Countries' => [
                    'country1' => $this->getMockCountry(1),
                ],
                'expectedResult' => [
                    'countryIso1' => [
                        'name' => 'countryName1',
                        'stateRequired' => null,
                    ],
                ],
            ],
            'multiple countries' => [
                'Countries' => [
                    'country1' => $this->getMockCountry(1),
                    'country2' => $this->getMockCountry(2),
                ],
                'expectedResult' => [
                    'countryIso1' => [
                        'name' => 'countryName1',
                        'stateRequired' => null,
                    ],
                    'countryIso2' => [
                        'name' => 'countryName2',
                        'stateRequired' => null,
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    public function provideValidCountryDefinitions()
    {
        return [
            'emptyArray' => [
                'countryDefinitions' => [],
            ],
            'single country' => [
                'countryDefinitions' => [
                    'countryIso1' => [
                        'name' => 'countryName1',
                        'stateRequired' => null,
                    ],
                ],
            ],
        ];
    }

    //==============================================================================================================
    //=================================================  MOCKS  ====================================================
    //==============================================================================================================

    /**
     * @param string $countryId
     *
     * @return Mock|CountryModel
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
                ['name', 'countryName'.$countryId],
            ]);

        $mockCountry->expects($this->any())
            ->method('getAllErrors')
            ->willReturn([
                'ohnoes' => 'horrible error',
            ]);

        return $mockCountry;
    }

    /**
     * @return Mock|CategoriesService
     */
    private function setMockCountriesService()
    {
        $mockCountriesService = $this->getMockBuilder(Commerce_CountriesService::class)
            ->disableOriginalConstructor()
            ->setMethods(['getAllCountries', 'saveCountry', 'deleteCountryById'])
            ->getMock();

        $mockCountriesService->expects($this->any())
            ->method('getAllCountries')
            ->willReturn([]);

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

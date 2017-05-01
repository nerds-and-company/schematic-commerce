<?php

namespace NerdsAndCompany\Schematic\Commerce\Services;

use Craft\Craft;
use Craft\Commerce_CountryModel;
use NerdsAndCompany\Schematic\Services\Base;

/**
 * Schematic Commerce Countries Service.
 *
 * Sync Craft Setups.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2017, Nerds & Company
 * @license   MIT
 *
 * @see      http://www.nerds.company
 */
class Countries extends Base
{
    /**
     * Export countries.
     *
     * @param CountryModel[] $countries
     *
     * @return array
     */
    public function export(array $countries = [])
    {
        if (!count($countries)) {
            $countries = Craft::app()->commerce_countries->getAllCountries();
        }

        Craft::log(Craft::t('Exporting Commerce Countries'));

        $countryDefinitions = [];

        foreach ($countries as $country) {
            $countryDefinitions[$country->iso] = $this->getCountryDefinition($country);
        }

        return $countryDefinitions;
    }

    /**
     * Get countries definition.
     *
     * @param Commerce_CountryModel $country
     *
     * @return array
     */
    private function getCountryDefinition(Commerce_CountryModel $country)
    {
        return [
            'name' => $country->name,
            'stateRequired' => $country->stateRequired,
        ];
    }

    /**
     * Attempt to import countries.
     *
     * @param array $countryDefinitions
     * @param bool  $force              If set to true countries not included in the import will be deleted
     *
     * @return Result
     */
    public function import(array $countryDefinitions, $force = false)
    {
        Craft::log(Craft::t('Importing Commerce Countries'));

        $countries = [];
        foreach (Craft::app()->commerce_countries->getAllCountries() as $country) {
            $countries[$country->iso] = $country;
        }

        foreach ($countryDefinitions as $countryHandle => $countryDefinition) {
            $country = array_key_exists($countryHandle, $countries)
                ? $countries[$countryHandle]
                : new Commerce_CountryModel();

            unset($countries[$countryHandle]);

            $this->populateCountry($country, $countryDefinition, $countryHandle);

            if (!Craft::app()->commerce_countries->saveCountry($country)) { // Save country via craft
                $this->addErrors($country->getAllErrors());

                continue;
            }
        }

        if ($force) {
            foreach ($countries as $country) {
                Craft::app()->commerce_countries->deleteCountryById($country->id);
            }
        }

        return $this->getResultModel();
    }

    /**
     * Populate country.
     *
     * @param Commerce_CountryModel $country
     * @param array                 $countryDefinition
     * @param string                $countryHandle
     */
    private function populateCountry(Commerce_CountryModel $country, array $countryDefinition, $countryHandle)
    {
        $country->setAttributes([
            'iso' => $countryHandle,
            'name' => $countryDefinition['name'],
            'stateRequired' => $countryDefinition['stateRequired'],
        ]);
    }
}

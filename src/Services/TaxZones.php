<?php

namespace NerdsAndCompany\Schematic\Commerce\Services;

use Craft\Craft;
use Craft\Commerce_TaxZoneModel;
use NerdsAndCompany\Schematic\Services\Base;

/**
 * Schematic Commerce Tax Zones Service.
 *
 * Sync Craft Setups.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2017, Nerds & Company
 * @license   MIT
 *
 * @see      http://www.nerds.company
 */
class TaxZones extends Base
{
    /**
     * Export taxZones.
     *
     * @param TaxZoneModel[] $taxZones
     *
     * @return array
     */
    public function export(array $taxZones = [])
    {
        if (!count($taxZones)) {
            $taxZones = Craft::app()->commerce_taxZones->getAllTaxZones(true);
        }

        Craft::log(Craft::t('Exporting Commerce Tax Zones'));

        $taxZoneDefinitions = [];

        foreach ($taxZones as $taxZone) {
            $taxZoneDefinitions[$taxZone->name] = $this->getTaxZoneDefinition($taxZone);
        }

        return $taxZoneDefinitions;
    }

    /**
     * Get tax zones definition.
     *
     * @param Commerce_TaxZoneModel $taxZone
     *
     * @return array
     */
    private function getTaxZoneDefinition(Commerce_TaxZoneModel $taxZone)
    {
        $countries = array();
        foreach ($taxZone->getCountries() as $country) {
            $countries[] = $country->iso;
        }

        $states = array();
        foreach ($taxZone->getStates() as $state) {
            $states[] = $state->abbreviation;
        }

        return [
            'name' => $taxZone->name,
            'description' => $taxZone->description,
            'countryBased' => $taxZone->countryBased,
            'default' => $taxZone->default,
            'countries' => $countries,
            'states' => $states,
        ];
    }

    /**
     * Attempt to import tax zones.
     *
     * @param array $taxZoneDefinitions
     * @param bool  $force              If set to true tax zones not included in the import will be deleted
     *
     * @return Result
     */
    public function import(array $taxZoneDefinitions, $force = false)
    {
        Craft::log(Craft::t('Importing Commerce Tax Zones'));

        $taxZones = array();
        foreach (Craft::app()->commerce_taxZones->getAllTaxZones() as $taxZone) {
            $taxZones[$taxZone->name] = $taxZone;
        }

        foreach ($taxZoneDefinitions as $taxZoneDefinition) {
            $taxZoneHandle = $taxZoneDefinition['name'];
            $taxZone = array_key_exists($taxZoneHandle, $taxZones)
                ? $taxZones[$taxZoneHandle]
                : new Commerce_TaxZoneModel();

            unset($taxZones[$taxZoneHandle]);

            $this->populateTaxZone($taxZone, $taxZoneDefinition, $taxZoneHandle);

            $countryIds = array();
            foreach ($taxZone->getCountries() as $country) {
                $countryIds[] = $country->id;
            }

            $stateIds = array();
            foreach ($taxZone->getStates() as $state) {
                $stateIds[] = $state->id;
            }

            if (!Craft::app()->commerce_taxZones->saveTaxZone($taxZone, $countryIds, $stateIds)) { // Save taxzone via craft
                $this->addErrors($taxZone->getAllErrors());

                continue;
            }
        }

        if ($force) {
            foreach ($taxZones as $taxZone) {
                Craft::app()->commerce_taxZones->deleteTaxZoneById($taxZone->id);
            }
        }

        return $this->getResultModel();
    }

    /**
     * Populate taxZone.
     *
     * @param Commerce_TaxZoneModel $taxZone
     * @param array                 $taxZoneDefinition
     * @param string                $taxZoneHandle
     */
    private function populateTaxZone(Commerce_TaxZoneModel $taxZone, array $taxZoneDefinition, $taxZoneHandle)
    {
        $taxZone->setAttributes([
            'name' => $taxZoneHandle,
            'description' => $taxZoneDefinition['description'],
            'countryBased' => $taxZoneDefinition['countryBased'],
            'default' => $taxZoneDefinition['default'],
        ]);

        $countries = array();
        foreach ($taxZoneDefinition['countries'] as $iso) {
            $countries[] = Craft::app()->commerce_countries->getCountryByAttributes(array('iso' => $iso));
        }
        $taxZone->setCountries($countries);

        $states = array();
        foreach ($taxZoneDefinition['states'] as $abbreviation) {
            $states[] = Craft::app()->commerce_states->getStateByAttributes(array('abbreviation' => $abbreviation));
        }
        $taxZone->setStates($states);
    }
}

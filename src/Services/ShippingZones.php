<?php

namespace NerdsAndCompany\Schematic\Commerce\Services;

use Craft\Craft;
use Craft\Commerce_ShippingZoneModel;
use NerdsAndCompany\Schematic\Services\Base;

/**
 * Schematic Commerce Shipping Zones Service.
 *
 * Sync Craft Setups.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2017, Nerds & Company
 * @license   MIT
 *
 * @see      http://www.nerds.company
 */
class ShippingZones extends Base
{
    /**
     * Export shippingZones.
     *
     * @param ShippingZoneModel[] $shippingZones
     *
     * @return array
     */
    public function export(array $shippingZones = [])
    {
        if (!count($shippingZones)) {
            $shippingZones = Craft::app()->commerce_shippingZones->getAllShippingZones(true);
        }

        Craft::log(Craft::t('Exporting Commerce Shipping Zones'));

        $shippingZoneDefinitions = [];

        foreach ($shippingZones as $shippingZone) {
            $shippingZoneDefinitions[$shippingZone->name] = $this->getShippingZoneDefinition($shippingZone);
        }

        return $shippingZoneDefinitions;
    }

    /**
     * Get shipping zones definition.
     *
     * @param Commerce_ShippingZoneModel $shippingZone
     *
     * @return array
     */
    private function getShippingZoneDefinition(Commerce_ShippingZoneModel $shippingZone)
    {
        $countries = array();
        foreach ($shippingZone->getCountries() as $country) {
            $countries[] = $country->iso;
        }

        $states = array();
        foreach ($shippingZone->getStates() as $state) {
            $states[] = $state->abbreviation;
        }

        return [
            'name' => $shippingZone->name,
            'description' => $shippingZone->description,
            'countryBased' => $shippingZone->countryBased,
            'default' => $shippingZone->default,
            'countries' => $countries,
            'states' => $states,
        ];
    }

    /**
     * Attempt to import shipping zones.
     *
     * @param array $shippingZoneDefinitions
     * @param bool  $force                   If set to true shipping zones not included in the import will be deleted
     *
     * @return Result
     */
    public function import(array $shippingZoneDefinitions, $force = false)
    {
        Craft::log(Craft::t('Importing Commerce Shipping Zones'));

        $shippingZones = array();
        foreach (Craft::app()->commerce_shippingZones->getAllShippingZones() as $shippingZone) {
            $shippingZones[$shippingZone->name] = $shippingZone;
        }

        foreach ($shippingZoneDefinitions as $shippingZoneDefinition) {
            $shippingZoneHandle = $shippingZoneDefinition['name'];
            $shippingZone = array_key_exists($shippingZoneHandle, $shippingZones)
                ? $shippingZones[$shippingZoneHandle]
                : new Commerce_ShippingZoneModel();

            unset($shippingZones[$shippingZoneHandle]);

            $this->populateShippingZone($shippingZone, $shippingZoneDefinition, $shippingZoneHandle);

            $countryIds = array();
            foreach ($shippingZone->getCountries() as $country) {
                $countryIds[] = $country->id;
            }

            $stateIds = array();
            foreach ($shippingZone->getStates() as $state) {
                $stateIds[] = $state->id;
            }

            if (!Craft::app()->commerce_shippingZones->saveShippingZone($shippingZone, $countryIds, $stateIds)) { // Save shippingzone via craft
                $this->addErrors($shippingZone->getAllErrors());

                continue;
            }
        }

        if ($force) {
            foreach ($shippingZones as $shippingZone) {
                Craft::app()->commerce_shippingZones->deleteShippingZoneById($shippingZone->id);
            }
        }

        return $this->getResultModel();
    }

    /**
     * Populate shippingZone.
     *
     * @param Commerce_ShippingZoneModel $shippingZone
     * @param array                      $shippingZoneDefinition
     * @param string                     $shippingZoneHandle
     */
    private function populateShippingZone(Commerce_ShippingZoneModel $shippingZone, array $shippingZoneDefinition, $shippingZoneHandle)
    {
        $shippingZone->setAttributes([
            'name' => $shippingZoneHandle,
            'description' => $shippingZoneDefinition['description'],
            'countryBased' => $shippingZoneDefinition['countryBased'],
            'default' => $shippingZoneDefinition['default'],
        ]);

        $countries = array();
        foreach ($shippingZoneDefinition['countries'] as $iso) {
            $countries[] = Craft::app()->commerce_countries->getCountryByAttributes(array('iso' => $iso));
        }
        $shippingZone->setCountries($countries);

        $states = array();
        foreach ($shippingZoneDefinition['states'] as $abbreviation) {
            $states[] = Craft::app()->commerce_states->getStateByAttributes(array('abbreviation' => $abbreviation));
        }
        $shippingZone->setStates($states);
    }
}

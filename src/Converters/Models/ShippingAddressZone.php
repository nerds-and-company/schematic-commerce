<?php

namespace NerdsAndCompany\Schematic\Commerce\Converters\Models;

use Craft;
use craft\base\Model;
use craft\commerce\models\Country as CountryModel;
use craft\commerce\models\State as StateModel;
use craft\commerce\models\ShippingAddressZone as ShippingAddressZoneModel;
use NerdsAndCompany\Schematic\Converters\Models\Base;

/**
 * Schematic Commerce Shipping Address Zone Converter.
 *
 * Sync Craft Setups.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2018, Nerds & Company
 * @license   MIT
 *
 * @see      http://www.nerds.company
 */
class ShippingAddressZone extends Base
{
    /**
     * @var Country[]
     */
    private $countries;

    /**
     * @var State[]
     */
    private $states;

    /**
     * {@inheritdoc}
     */
    public function getRecordDefinition(Model $record): array
    {
        $definition = parent::getRecordDefinition($record);

        $definition['countries'] = $record->getCountriesNames();
        $definition['states'] = $record->getStatesNames();

        return $definition;
    }

    /**
     * {@inheritdoc}
     */
    public function saveRecord(Model $record, array $definition): bool
    {
        $commerce = Craft::$app->getPlugins()->getPlugin('commerce');

        $countries = [];
        foreach ($definition['countries'] as $country) {
            $countries[] = $this->getCountryByName($commerce, $country);
        }
        $record->setCountries($countries);

        $states = [];
        foreach ($definition['states'] as $state) {
            $states[] = $this->getStateByName($commerce, $state);
        }
        $record->setStates($states);

        return $commerce->getShippingZones()->saveShippingZone($record);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteRecord(Model $record): bool
    {
        $commerce = Craft::$app->getPlugins()->getPlugin('commerce');

        return $commerce->getShippingZones()->deleteShippingZoneById($record->id);
    }

    /**
     * {@inheritdoc}
     */
    public function getRecordIndex(Model $record): string
    {
        return $record->name;
    }

    /**
     * Get country by name.
     *
     * @param $commerce
     * @param string $name
     *
     * return Country
     */
    protected function getCountryByName($commerce, $name)
    {
        if (!isset($this->countries)) {
            $this->countries = [];
            foreach ($commerce->getCountries()->getAllCountries() as $country) {
                $this->countries[$country->name] = $country;
            }
        }
        if (!array_key_exists($name, $this->countries)) {
            $country = new CountryModel(['name' => $name]);
            if ($commerce->getCountries()->saveCountry($country)) {
                $this->countries[$name] = $country;
            } else {
                Schematic::importError($country, $name);
                return null;
            }
        }
        return $this->countries[$name];
    }

    /**
     * Get state by name.
     *
     * @param $commerce
     * @param string $name
     *
     * return State
     */
    protected function getStateByName($commerce, $name)
    {
        if (!isset($this->states)) {
            $this->states = [];
            foreach ($commerce->getStates()->getAllStates() as $state) {
                $this->states[$state->name] = $state;
            }
        }
        if (!array_key_exists($name, $this->states)) {
            $state = new StateModel(['name' => $name]);
            if ($commerce->getStates()->saveState($state)) {
                $this->states[$name] = $state;
            } else {
                Schematic::importError($state, $name);
                return null;
            }
        }
        return $this->states[$name];
    }
}

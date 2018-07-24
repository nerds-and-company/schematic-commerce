<?php

namespace NerdsAndCompany\Schematic\Commerce\Converters\Models;

use Craft;
use craft\base\Model;
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
     * {@inheritdoc}
     */
    public function getRecordDefinition(Model $record): array
    {
        $definition = parent::getRecordDefinition($record);

        if ($record instanceof ShippingAddressZoneModel) {
            $definition['countries'] = [];
            foreach ($record->getCountries() as $country) {
                $definition['countries'][$country->iso] = $this->getRecordDefinition($country);
            }

            $definition['states'] = [];
            foreach ($record->getStates() as $state) {
                $stateDefinition = $this->getRecordDefinition($state);
                unset($stateDefinition['countryId']);
                $stateDefinition['country'] = $state->getCountry() ? $state->getCountry()->name : null;

                $definition['states'][$state->abbreviation] = $stateDefinition;
            }
        }

        return $definition;
    }

    /**
     * {@inheritdoc}
     */
    public function saveRecord(Model $record, array $definition): bool
    {
        $commerce = Craft::$app->getPlugins()->getPlugin('commerce');

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
    public function getRecordIndex(): string
    {
        return 'name';
    }
}
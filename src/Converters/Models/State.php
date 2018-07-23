<?php

namespace NerdsAndCompany\Schematic\Commerce\Converters\Models;

use Craft;
use craft\base\Model;
use NerdsAndCompany\Schematic\Converters\Models\Base;

/**
 * Schematic Commerce State Converter.
 *
 * Sync Craft Setups.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2018, Nerds & Company
 * @license   MIT
 *
 * @see      http://www.nerds.company
 */
class State extends Base
{
    /**
     * {@inheritdoc}
     */
    public function getRecordDefinition(Model $record): array
    {
        $definition = parent::getRecordDefinition($record);

        unset($definition['attributes']['countryId']);
        $definition['country'] = $record->getCountry() ? $record->getCountry()->name : null;

        return $definition;
    }

    /**
     * {@inheritdoc}
     */
    public function saveRecord(Model $record, array $definition): bool
    {
        $commerce = Craft::$app->getPlugins()->getPlugin('commerce');

        return $commerce->getStates()->saveState($record);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteRecord(Model $record): bool
    {
        $commerce = Craft::$app->getPlugins()->getPlugin('commerce');

        return $commerce->getStates()->deleteStateById($record->id);
    }

    /**
     * {@inheritdoc}
     */
    public function getRecordIndex(): string
    {
        return 'abbreviation';
    }
}

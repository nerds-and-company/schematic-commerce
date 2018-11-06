<?php

namespace NerdsAndCompany\Schematic\Commerce\Converters\Models;

use Craft;
use craft\base\Model;
use NerdsAndCompany\Schematic\Converters\Models\Base;

/**
 * Schematic Commerce Tax Rate Converter.
 *
 * Sync Craft Setups.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2018, Nerds & Company
 * @license   MIT
 *
 * @see      http://www.nerds.company
 */
class TaxRate extends Base
{
    /**
     * @var int[]
     */
    private $taxZones;

    /**
     * {@inheritdoc}
     */
    public function getRecordDefinition(Model $record): array
    {
        $definition = parent::getRecordDefinition($record);

        unset($definition['attributes']['taxCategoryId']);
        unset($definition['attributes']['taxZoneId']);

        $definition['taxCategory'] = $record->getTaxCategory() ? $record->getTaxCategory()->handle : null;
        $definition['taxZone'] = $record->getTaxZone() ? $record->getTaxZone()->name : null;

        return $definition;
    }

    /**
     * {@inheritdoc}
     */
    public function saveRecord(Model $record, array $definition): bool
    {
        $commerce = Craft::$app->getPlugins()->getPlugin('commerce');

        if ($definition['taxCategory']) {
            $record->taxCategoryId = $this->getTaxCategoryIdByHandle($commerce, $definition['taxCategory']);
        }

        if ($definition['taxZone']) {
            $record->taxZoneId = $this->getTaxZoneIdByName($commerce, $definition['taxZone']);
        }

        return $commerce->getTaxRates()->saveTaxRate($record);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteRecord(Model $record): bool
    {
        $commerce = Craft::$app->getPlugins()->getPlugin('commerce');

        return $commerce->getTaxRates()->deleteTaxRateById($record->id);
    }

    /**
     * {@inheritdoc}
     */
    public function getRecordIndex(Model $record): string
    {
        return $record->name;
    }

    /**
     * Get tax category id by handle.
     *
     * @param $commerce
     * @param string $handle
     *
     * @return int|null
     */
    protected function getTaxCategoryIdByHandle($commerce, $handle)
    {
        return $commerce->getTaxCategories()->getTaxCategoryByHandle($handle)->id;
    }

    /**
     * Get tax zone id by name.
     *
     * @param $commerce
     * @param string $name
     *
     * @return int|null
     */
    protected function getTaxZoneIdByName($commerce, $name)
    {
        if (!isset($this->taxZones)) {
            $this->taxZones = [];
            foreach ($commerce->getTaxZones()->getAllTaxZones() as $taxZone) {
                $this->taxZones[$taxZone->name] = $taxZone->id;
            }
        }

        return $this->taxZones[$name];
    }
}

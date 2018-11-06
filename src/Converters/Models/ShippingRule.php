<?php

namespace NerdsAndCompany\Schematic\Commerce\Converters\Models;

use Craft;
use craft\base\Model;
use craft\commerce\models\ShippingRule as ShippingRuleModel;
use craft\commerce\models\ShippingRuleCategory as ShippingRuleCategoryModel;
use craft\commerce\models\ShippingAddressZone as ShippingZoneModel;
use NerdsAndCompany\Schematic\Schematic;
use NerdsAndCompany\Schematic\Converters\Models\Base;

/**
 * Schematic Commerce Shipping Rule Converter.
 *
 * Sync Craft Setups.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2018, Nerds & Company
 * @license   MIT
 *
 * @see      http://www.nerds.company
 */
class ShippingRule extends Base
{
    /**
     * @var ShippingZone[]
     */
    private $shippingZones;

    /**
     * {@inheritdoc}
     */
    public function getRecordDefinition(Model $record): array
    {
        $definition = parent::getRecordDefinition($record);

        unset($definition['attributes']['shippingZoneId']);
        unset($definition['attributes']['methodId']);
        $definition['shippingZone'] = $record->getShippingZone() ? $record->getShippingZone()->name : null;
        $definition['categories'] = Craft::$app->controller->module->modelMapper->export($record->getShippingRuleCategories());

        return $definition;
    }

    /**
     * {@inheritdoc}
     */
    public function saveRecord(Model $record, array $definition): bool
    {
        $commerce = Craft::$app->getPlugins()->getPlugin('commerce');

        if ($definition['shippingZone']) {
            $record->shippingZoneId = $this->getShippingZoneIdByName($commerce, $definition['shippingZone']);
        }

        if ($commerce->getShippingRules()->saveShippingRule($record)) {
            Craft::$app->controller->module->modelMapper->import(
                $definition['categories'],
                $record->getShippingRuleCategories(),
                ['shippingRuleId' => $record->id]
            );

            return true;
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteRecord(Model $record): bool
    {
        $commerce = Craft::$app->getPlugins()->getPlugin('commerce');

        return $commerce->getShippingRules()->deleteShippingRuleById($record->id);
    }

    /**
     * {@inheritdoc}
     */
    public function getRecordIndex(Model $record): string
    {
        return $record->name;
    }

    /**
     * Get shipping zone id by name.
     *
     * @param $commerce
     * @param string $name
     *
     * @return int|null
     */
    protected function getShippingZoneIdByName($commerce, $name)
    {
        if (!isset($this->shippingZones)) {
            $this->shippingZones = [];
            foreach ($commerce->getShippingZones()->getAllShippingZones() as $shippingZone) {
                $this->shippingZones[$shippingZone->name] = $shippingZone->id;
            }
        }

        return $this->shippingZones[$name];
    }
}

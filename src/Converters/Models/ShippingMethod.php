<?php

namespace NerdsAndCompany\Schematic\Commerce\Converters\Models;

use Craft;
use craft\base\Model;
use craft\commerce\models\ShippingMethod as ShippingMethodModel;
use craft\commerce\models\ShippingRule as ShippingRuleModel;
use craft\commerce\models\ShippingRuleCategory as ShippingRuleCategoryModel;
use NerdsAndCompany\Schematic\Converters\Models\Base;

/**
 * Schematic Commerce Shipping Method Converter.
 *
 * Sync Craft Setups.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2018, Nerds & Company
 * @license   MIT
 *
 * @see      http://www.nerds.company
 */
class ShippingMethod extends Base
{
    /**
     * {@inheritdoc}
     */
    public function getRecordDefinition(Model $record): array
    {
        $definition = parent::getRecordDefinition($record);

        if ($record instanceof ShippingMethodModel) {
            unset($definition['attributes']['variantFieldLayoutId']);

            $definition['rules'] = [];
            foreach ($record->getShippingRules() as $shippingRule) {
                $definition['rules'][$shippingRule->name] = $this->getRecordDefinition($shippingRule);
            }
        }

        if ($record instanceof ShippingRuleModel) {
            unset($definition['attributes']['shippingZoneId']);
            unset($definition['attributes']['methodId']);
            $definition['shippingZone'] = $record->getShippingZone() ? $record->getShippingZone()->name : null;

            $definition['categories'] = [];
            foreach ($record->getShippingRuleCategories() as $shippingRuleCategory) {
                $handle = $shippingRuleCategory->getCategory()->handle;
                $definition['categories'][$handle] = $this->getRecordDefinition($shippingRuleCategory);
            }
        }

        if ($record instanceof ShippingRuleCategoryModel) {
            unset($definition['attributes']['shippingRuleId']);
            unset($definition['attributes']['shippingCategoryId']);
            $definition['shippingCategory'] = $record->getCategory() ? $record->getCategory()->name : null;
        }

        return $definition;
    }

    /**
     * {@inheritdoc}
     */
    public function saveRecord(Model $record, array $definition): bool
    {
        $commerce = Craft::$app->getPlugins()->getPlugin('commerce');

        return $commerce->getShippingMethods()->saveShippingMethod($record);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteRecord(Model $record): bool
    {
        $commerce = Craft::$app->getPlugins()->getPlugin('commerce');

        return $commerce->getShippingMethods()->deleteShippingMethodById($record->id);
    }
}

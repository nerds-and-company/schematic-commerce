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
 * Schematic Commerce Shipping Rule Category Converter.
 *
 * Sync Craft Setups.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2018, Nerds & Company
 * @license   MIT
 *
 * @see      http://www.nerds.company
 */
class ShippingRuleCategory extends Base
{
    /**
     * @var ShippingRuleCategory[]
     */
    private $categories;

    /**
     * {@inheritdoc}
     */
    public function getRecordDefinition(Model $record): array
    {
        $definition = parent::getRecordDefinition($record);

        if ($record->shippingCategoryId) {
            $definition['shippingCategory'] = $record->getCategory()->name;
        }

        unset($definition['attributes']['shippingCategoryId']);
        unset($definition['attributes']['shippingRuleId']);

        return $definition;
    }

    /**
     * {@inheritdoc}
     */
    public function saveRecord(Model $record, array $definition): bool
    {
        $commerce = Craft::$app->getPlugins()->getPlugin('commerce');

        if ($definition['shippingCategory']) {
            $record->shippingCategoryId = $this->getCategoryIdByName($commerce, $definition['shippingCategory']);
        }

        return $commerce->getShippingRuleCategories()->createShippingRuleCategory($record);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteRecord(Model $record): bool
    {
        $commerce = Craft::$app->getPlugins()->getPlugin('commerce');

        return $commerce->getShippingRuleCategories()->deleteShippingRuleCategoryById($record->id);
    }

    /**
     * {@inheritdoc}
     */
    public function getRecordIndex(Model $record): string
    {
        return $record->getCategory()->handle;
    }

    /**
     * Get category id by name.
     *
     * @param $commerce
     * @param string $name
     *
     * @return int|null
     */
    public function getCategoryIdByName($commerce, $name)
    {
        if (!isset($this->categories)) {
            $this->categories = [];
            foreach ($commerce->getShippingCategories()->getAllShippingCategories() as $category) {
                $this->categories[$category->name] = $category->id;
            }
        }

        return $this->categories[$name];
    }
}

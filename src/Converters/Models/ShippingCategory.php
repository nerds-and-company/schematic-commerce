<?php

namespace NerdsAndCompany\Schematic\Commerce\Converters\Models;

use Craft;
use craft\base\Model;
use NerdsAndCompany\Schematic\Converters\Models\Base;

/**
 * Schematic Commerce Shipping Category Converter.
 *
 * Sync Craft Setups.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2018, Nerds & Company
 * @license   MIT
 *
 * @see      http://www.nerds.company
 */
class ShippingCategory extends Base
{
    /**
     * {@inheritdoc}
     */
    public function saveRecord(Model $record, array $definition): bool
    {
        $commerce = Craft::$app->getPlugins()->getPlugin('commerce');

        return $commerce->getShippingCategories()->saveShippingCategory($record);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteRecord(Model $record): bool
    {
        $commerce = Craft::$app->getPlugins()->getPlugin('commerce');

        return $commerce->getShippingCategories()->deleteShippingCategoryById($record->id);
    }
}

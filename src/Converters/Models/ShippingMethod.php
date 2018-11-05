<?php

namespace NerdsAndCompany\Schematic\Commerce\Converters\Models;

use Craft;
use craft\base\Model;
use craft\commerce\models\ShippingMethod as ShippingMethodModel;
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

            $definition['rules'] = Craft::$app->controller->module->modelMapper->export($record->getShippingRules());
        }

        return $definition;
    }

    /**
     * {@inheritdoc}
     */
    public function saveRecord(Model $record, array $definition): bool
    {
        $commerce = Craft::$app->getPlugins()->getPlugin('commerce');

        if ($commerce->getShippingMethods()->saveShippingMethod($record)) {
            Craft::$app->controller->module->modelMapper->import(
                $definition['rules'],
                $record->getShippingRules(),
                ['methodId' => $record->id]
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

        return $commerce->getShippingMethods()->deleteShippingMethodById($record->id);
    }
}

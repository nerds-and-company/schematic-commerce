<?php

namespace NerdsAndCompany\Schematic\Commerce\Converters\Models;

use Craft;
use craft\base\Model;
use craft\commerce\models\ProductTypeSite;
use NerdsAndCompany\Schematic\Converters\Models\Base;

/**
 * Schematic Commerce Product Type Converter.
 *
 * Sync Craft Setups.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2018, Nerds & Company
 * @license   MIT
 *
 * @see      http://www.nerds.company
 */
class ProductType extends Base
{
    /**
     * {@inheritdoc}
     */
    public function getRecordDefinition(Model $record): array
    {
        $definition = parent::getRecordDefinition($record);

        // Also define variant field layout
        if (isset($definition['attributes']['variantFieldLayoutId'])) {
            $definition['variantFieldLayout'] = $this->getFieldLayoutDefinition($record->getVariantFieldLayout());
        }
        unset($definition['attributes']['variantFieldLayoutId']);

        if ($record instanceof ProductTypeSite) {
            unset($definition['attributes']['productTypeId']);
            unset($definition['attributes']['siteId']);
        }

        return $definition;
    }

    /**
     * {@inheritdoc}
     */
    public function setRecordAttributes(Model &$record, array $definition, array $defaultAttributes)
    {
        parent::setRecordAttributes($record, $definition, $defaultAttributes);

        // Set variant field layout
        if (array_key_exists('variantFieldLayout', $definition)) {
            $record->getBehavior('variantFieldLayout')->setFieldLayout($this->getFieldLayout($definition['variantFieldLayout']));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function saveRecord(Model $record, array $definition): bool
    {
        $commerce = Craft::$app->getPlugins()->getPlugin('commerce');

        return $commerce->getProductTypes()->saveProductType($record);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteRecord(Model $record): bool
    {
        $commerce = Craft::$app->getPlugins()->getPlugin('commerce');

        return $commerce->getProductTypes()->deleteProductTypeById($record->id);
    }
}

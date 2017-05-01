<?php

namespace NerdsAndCompany\Schematic\Commerce\Services;

use Craft\Craft;
use Craft\Commerce_ShippingCategoryModel;
use NerdsAndCompany\Schematic\Services\Base;

/**
 * Schematic Commerce Shipping Categories Service.
 *
 * Sync Craft Setups.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2017, Nerds & Company
 * @license   MIT
 *
 * @see      http://www.nerds.company
 */
class ShippingCategories extends Base
{
    /**
     * Export shippingCategories.
     *
     * @param ShippingCategoryModel[] $shippingCategories
     *
     * @return array
     */
    public function export(array $shippingCategories = [])
    {
        if (!count($shippingCategories)) {
            $shippingCategories = Craft::app()->commerce_shippingCategories->getAllShippingCategories();
        }

        Craft::log(Craft::t('Exporting Commerce Shipping Categories'));

        $shippingCategoryDefinitions = [];

        foreach ($shippingCategories as $shippingCategory) {
            $shippingCategoryDefinitions[$shippingCategory->handle] = $this->getShippingCategoryDefinition($shippingCategory);
        }

        return $shippingCategoryDefinitions;
    }

    /**
     * Get shipping categories definition.
     *
     * @param Commerce_ShippingCategoryModel $shippingCategory
     *
     * @return array
     */
    private function getShippingCategoryDefinition(Commerce_ShippingCategoryModel $shippingCategory)
    {
        return [
            'name' => $shippingCategory->name,
            'description' => $shippingCategory->description,
            'default' => $shippingCategory->default,
        ];
    }

    /**
     * Attempt to import shipping categories.
     *
     * @param array $shippingCategoryDefinitions
     * @param bool  $force                       If set to true shipping categories not included in the import will be deleted
     *
     * @return Result
     */
    public function import(array $shippingCategoryDefinitions, $force = false)
    {
        Craft::log(Craft::t('Importing Commerce Shipping Categories'));

        $this->resetCraftShippingCategoriesServiceCache();
        $shippingCategories = Craft::app()->commerce_shippingCategories->getAllShippingCategories('handle');

        foreach ($shippingCategoryDefinitions as $shippingCategoryHandle => $shippingCategoryDefinition) {
            $shippingCategory = array_key_exists($shippingCategoryHandle, $shippingCategories)
                ? $shippingCategories[$shippingCategoryHandle]
                : new Commerce_ShippingCategoryModel();

            unset($shippingCategories[$shippingCategoryHandle]);

            $this->populateShippingCategory($shippingCategory, $shippingCategoryDefinition, $shippingCategoryHandle);

            if (!Craft::app()->commerce_shippingCategories->saveShippingCategory($shippingCategory)) { // Save shippingcategory via craft
                $this->addErrors($shippingCategory->getAllErrors());

                continue;
            }
        }

        if ($force) {
            foreach ($shippingCategories as $shippingCategory) {
                Craft::app()->commerce_shippingCategories->deleteShippingCategoryById($shippingCategory->id);
            }
        }

        return $this->getResultModel();
    }

    /**
     * Populate shippingcategory.
     *
     * @param Commerce_ShippingCategoryModel $shippingCategory
     * @param array                          $shippingCategoryDefinition
     * @param string                         $shippingCategoryHandle
     */
    private function populateShippingCategory(Commerce_ShippingCategoryModel $shippingCategory, array $shippingCategoryDefinition, $shippingCategoryHandle)
    {
        $shippingCategory->setAttributes([
            'handle' => $shippingCategoryHandle,
            'name' => $shippingCategoryDefinition['name'],
            'description' => $shippingCategoryDefinition['description'],
            'default' => $shippingCategoryDefinition['default'],
        ]);
    }

    /**
     * Reset service cache using reflection.
     */
    private function resetCraftShippingCategoriesServiceCache()
    {
        $obj = Craft::app()->commerce_shippingCategories;
        $refObject = new \ReflectionObject($obj);
        if ($refObject->hasProperty('_fetchedAllShippingCategories')) {
            $refProperty = $refObject->getProperty('_fetchedAllShippingCategories');
            $refProperty->setAccessible(true);
            $refProperty->setValue($obj, false);
        }
        if ($refObject->hasProperty('_shippingCategoriesById')) {
            $refProperty = $refObject->getProperty('_shippingCategoriesById');
            $refProperty->setAccessible(true);
            $refProperty->setValue($obj, []);
        }
        if ($refObject->hasProperty('_shippingCategoriesByHandle')) {
            $refProperty = $refObject->getProperty('_shippingCategoriesByHandle');
            $refProperty->setAccessible(true);
            $refProperty->setValue($obj, []);
        }
    }
}

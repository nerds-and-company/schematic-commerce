<?php

namespace NerdsAndCompany\Schematic\Commerce\Services;

use Craft\Craft;
use Craft\Commerce_TaxCategoryModel;
use NerdsAndCompany\Schematic\Services\Base;

/**
 * Schematic Commerce Tax Categories Service.
 *
 * Sync Craft Setups.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2017, Nerds & Company
 * @license   MIT
 *
 * @see      http://www.nerds.company
 */
class TaxCategories extends Base
{
    /**
     * Export taxCategories.
     *
     * @param TaxCategoryModel[] $taxCategories
     *
     * @return array
     */
    public function export(array $taxCategories = [])
    {
        if (!count($taxCategories)) {
            $taxCategories = Craft::app()->commerce_taxCategories->getAllTaxCategories();
        }

        Craft::log(Craft::t('Exporting Commerce Tax Categories'));

        $taxCategoryDefinitions = [];

        foreach ($taxCategories as $taxCategory) {
            $taxCategoryDefinitions[$taxCategory->handle] = $this->getTaxCategoryDefinition($taxCategory);
        }

        return $taxCategoryDefinitions;
    }

    /**
     * Get tax categories definition.
     *
     * @param Commerce_TaxCategoryModel $taxCategory
     *
     * @return array
     */
    private function getTaxCategoryDefinition(Commerce_TaxCategoryModel $taxCategory)
    {
        return [
            'name' => $taxCategory->name,
            'description' => $taxCategory->description,
            'default' => $taxCategory->default,
        ];
    }

    /**
     * Attempt to import tax categories.
     *
     * @param array $taxCategoryDefinitions
     * @param bool  $force                  If set to true tax categories not included in the import will be deleted
     *
     * @return Result
     */
    public function import(array $taxCategoryDefinitions, $force = false)
    {
        Craft::log(Craft::t('Importing Commerce Tax Categories'));

        $this->resetCraftTaxCategoriesServiceCache();
        $taxCategories = Craft::app()->commerce_taxCategories->getAllTaxCategories('handle');

        foreach ($taxCategoryDefinitions as $taxCategoryHandle => $taxCategoryDefinition) {
            $taxCategory = array_key_exists($taxCategoryHandle, $taxCategories)
                ? $taxCategories[$taxCategoryHandle]
                : new Commerce_TaxCategoryModel();

            unset($taxCategories[$taxCategoryHandle]);

            $this->populateTaxCategory($taxCategory, $taxCategoryDefinition, $taxCategoryHandle);

            if (!Craft::app()->commerce_taxCategories->saveTaxCategory($taxCategory)) { // Save taxcategory via craft
                $this->addErrors($taxCategory->getAllErrors());

                continue;
            }
        }

        if ($force) {
            foreach ($taxCategories as $taxCategory) {
                Craft::app()->commerce_taxCategories->deleteTaxCategoryById($taxCategory->id);
            }
        }

        return $this->getResultModel();
    }

    /**
     * Populate taxcategory.
     *
     * @param Commerce_TaxCategoryModel $taxCategory
     * @param array                     $taxCategoryDefinition
     * @param string                    $taxCategoryHandle
     */
    private function populateTaxCategory(Commerce_TaxCategoryModel $taxCategory, array $taxCategoryDefinition, $taxCategoryHandle)
    {
        $taxCategory->setAttributes([
            'handle' => $taxCategoryHandle,
            'name' => $taxCategoryDefinition['name'],
            'description' => $taxCategoryDefinition['description'],
            'default' => $taxCategoryDefinition['default'],
        ]);
    }

    /**
     * Reset service cache using reflection.
     */
    private function resetCraftTaxCategoriesServiceCache()
    {
        $obj = Craft::app()->commerce_taxCategories;
        $refObject = new \ReflectionObject($obj);
        if ($refObject->hasProperty('_fetchedAllTaxCategories')) {
            $refProperty = $refObject->getProperty('_fetchedAllTaxCategories');
            $refProperty->setAccessible(true);
            $refProperty->setValue($obj, false);
        }
        if ($refObject->hasProperty('_taxCategoriesById')) {
            $refProperty = $refObject->getProperty('_taxCategoriesById');
            $refProperty->setAccessible(true);
            $refProperty->setValue($obj, array());
        }
        if ($refObject->hasProperty('_taxCategoriesByHandle')) {
            $refProperty = $refObject->getProperty('_taxCategoriesByHandle');
            $refProperty->setAccessible(true);
            $refProperty->setValue($obj, array());
        }
    }
}

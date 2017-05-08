<?php

namespace NerdsAndCompany\Schematic\Commerce\Services;

use Craft\Craft;
use Craft\Commerce_ProductTypeModel;
use Craft\Commerce_ProductTypeLocaleModel;
use NerdsAndCompany\Schematic\Services\Base;

/**
 * Schematic Commerce Product Types Service.
 *
 * Sync Craft Setups.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2017, Nerds & Company
 * @license   MIT
 *
 * @see      http://www.nerds.company
 */
class ProductTypes extends Base
{
    /**
     * Export productTypes.
     *
     * @param ProductTypeModel[] $productTypes
     *
     * @return array
     */
    public function export(array $productTypes = [])
    {
        if (!count($productTypes)) {
            $productTypes = Craft::app()->commerce_productTypes->getAllProductTypes();
        }

        Craft::log(Craft::t('Exporting Commerce Product Types'));

        $productTypeDefinitions = [];

        foreach ($productTypes as $productType) {
            $productTypeDefinitions[$productType->handle] = $this->getProductTypeDefinition($productType);
        }

        return $productTypeDefinitions;
    }

    /**
     * Get product types definition.
     *
     * @param Commerce_ProductTypeModel $productType
     *
     * @return array
     */
    private function getProductTypeDefinition(Commerce_ProductTypeModel $productType)
    {
        $fieldLayout = Craft::app()->fields->getLayoutById($productType->fieldLayoutId);
        $variantFieldLayout = Craft::app()->fields->getLayoutById($productType->variantFieldLayoutId);

        return [
            'name' => $productType->name,
            'hasUrls' => $productType->hasUrls,
            'hasDimensions' => $productType->hasDimensions,
            'hasVariants' => $productType->hasVariants,
            'hasVariantTitleField' => $productType->hasVariantTitleField,
            'titleFormat' => $productType->titleFormat,
            'skuFormat' => $productType->skuFormat,
            'descriptionFormat' => $productType->descriptionFormat,
            'lineItemFormat' => $productType->lineItemFormat,
            'template' => $productType->template,
            'locales' => $this->getLocaleDefinitions($productType->getLocales()),
            'fieldLayout' => Craft::app()->schematic_fields->getFieldLayoutDefinition($fieldLayout),
            'variantFieldLayout' => Craft::app()->schematic_fields->getFieldLayoutDefinition($variantFieldLayout),
            'taxCategories' => array_column($productType->getTaxCategories(), 'handle'),
        ];
    }

    /**
     * Get locale definitions.
     *
     * @param ProductTypeLocaleModel[] $locales
     *
     * @return array
     */
    private function getLocaleDefinitions(array $locales)
    {
        $localeDefinitions = [];

        foreach ($locales as $locale) {
            $localeDefinitions[$locale->locale] = $this->getLocaleDefinition($locale);
        }

        return $localeDefinitions;
    }

    /**
     * Get locale definition.
     *
     * @param Commerce_ProductTypeLocaleModel $locale
     *
     * @return array
     */
    private function getLocaleDefinition(Commerce_ProductTypeLocaleModel $locale)
    {
        return [
            'urlFormat' => $locale->urlFormat,
        ];
    }

    /**
     * Attempt to import product types.
     *
     * @param array $productTypeDefinitions
     * @param bool  $force                  If set to true product types not included in the import will be deleted
     *
     * @return Result
     */
    public function import(array $productTypeDefinitions, $force = false)
    {
        Craft::log(Craft::t('Importing Commerce Product Types'));

        $this->resetCraftProductTypesServiceCache();
        $productTypes = Craft::app()->commerce_productTypes->getAllProductTypes('handle');

        foreach ($productTypeDefinitions as $productTypeHandle => $productTypeDefinition) {
            $productType = array_key_exists($productTypeHandle, $productTypes)
                ? $productTypes[$productTypeHandle]
                : new Commerce_ProductTypeModel();

            unset($productTypes[$productTypeHandle]);

            $this->populateProductType($productType, $productTypeDefinition, $productTypeHandle);

            if (!Craft::app()->commerce_productTypes->saveProductType($productType)) { // Save producttype via craft
                $this->addErrors($productType->getAllErrors());

                continue;
            }
        }

        if ($force) {
            foreach ($productTypes as $productType) {
                Craft::app()->commerce_productTypes->deleteProductTypeById($productType->id);
            }
        }

        return $this->getResultModel();
    }

    /**
     * Populate producttype.
     *
     * @param Commerce_ProductTypeModel $productType
     * @param array                     $productTypeDefinition
     * @param string                    $productTypeHandle
     */
    private function populateProductType(Commerce_ProductTypeModel $productType, array $productTypeDefinition, $productTypeHandle)
    {
        $productType->setAttributes([
            'handle' => $productTypeHandle,
            'name' => $productTypeDefinition['name'],
            'hasUrls' => $productTypeDefinition['hasUrls'],
            'hasDimensions' => $productTypeDefinition['hasDimensions'],
            'hasVariants' => $productTypeDefinition['hasVariants'],
            'hasVariantTitleField' => $productTypeDefinition['hasVariantTitleField'],
            'titleFormat' => $productTypeDefinition['titleFormat'],
            'skuFormat' => $productTypeDefinition['skuFormat'],
            'descriptionFormat' => $productTypeDefinition['descriptionFormat'],
            'lineItemFormat' => $productTypeDefinition['lineItemFormat'],
            'template' => $productTypeDefinition['template'],
        ]);

        $this->populateProductTypeCategories($productType, $productTypeDefinition['taxCategories']);
        $this->populateProductTypeLocales($productType, $productTypeDefinition['locales']);

        $fieldLayout = Craft::app()->schematic_fields->getFieldLayout($productTypeDefinition['fieldLayout']);
        $productType->setFieldLayout($fieldLayout);
    }

    /**
     * Populate section locales.
     *
     * @param Commerce_ProductTypeModel $productType
     * @param $localeDefinitions
     */
    private function populateProductTypeLocales(Commerce_ProductTypeModel $productType, $localeDefinitions)
    {
        $locales = $productType->getLocales();

        foreach ($localeDefinitions as $localeId => $localeDef) {
            $locale = array_key_exists($localeId, $locales) ? $locales[$localeId] : new Commerce_ProductTypeLocaleModel();

            $locale->setAttributes([
                'locale' => $localeId,
                'urlFormat' => $localeDef['urlFormat'],
            ]);

            // Todo: Is this a hack? I don't see another way.
            // Todo: Might need a sorting order as well? It's NULL at the moment.
            Craft::app()->db->createCommand()->insertOrUpdate('locales', [
                'locale' => $locale->locale,
            ], []);

            $locales[$localeId] = $locale;
        }

        $productType->setLocales($locales);
    }

    /**
     * Populate productTypeCategories.
     *
     * @param Commerce_ProductTypeModel $productType
     * @param $categoryDefinitions
     */
    private function populateProductTypeCategories(Commerce_ProductTypeModel $productType, $categoryDefinitions)
    {
        $taxCategoryIds = [];
        foreach ($categoryDefinitions as $handle) {
            $taxCategoryIds[] = Craft::app()->commerce_taxCategories->getTaxCategoryByHandle($handle)->id;
        }
        $productType->setTaxCategories($taxCategoryIds);
    }

    /**
     * Reset service cache using reflection.
     */
    private function resetCraftProductTypesServiceCache()
    {
        $obj = Craft::app()->commerce_productTypes;
        $refObject = new \ReflectionObject($obj);
        if ($refObject->hasProperty('_fetchedAllProductTypes')) {
            $refProperty = $refObject->getProperty('_fetchedAllProductTypes');
            $refProperty->setAccessible(true);
            $refProperty->setValue($obj, false);
        }
        if ($refObject->hasProperty('_productTypesById')) {
            $refProperty = $refObject->getProperty('_productTypesById');
            $refProperty->setAccessible(true);
            $refProperty->setValue($obj, []);
        }
    }
}

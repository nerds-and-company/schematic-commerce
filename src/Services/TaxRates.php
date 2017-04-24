<?php

namespace NerdsAndCompany\Schematic\Commerce\Services;

use Craft\Craft;
use Craft\Commerce_TaxRateModel;
use NerdsAndCompany\Schematic\Services\Base;

/**
 * Schematic Commerce Tax Rates Service.
 *
 * Sync Craft Setups.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2017, Nerds & Company
 * @license   MIT
 *
 * @see      http://www.nerds.company
 */
class TaxRates extends Base
{
    /**
     * Export taxRates.
     *
     * @param TaxRateModel[] $taxRates
     *
     * @return array
     */
    public function export(array $taxRates = [])
    {
        if (!count($taxRates)) {
            $taxRates = Craft::app()->commerce_taxRates->getAllTaxRates();
        }

        Craft::log(Craft::t('Exporting Commerce Tax Rates'));

        $taxRateDefinitions = [];

        foreach ($taxRates as $taxRate) {
            $taxRateDefinitions[$taxRate->name] = $this->getTaxRateDefinition($taxRate);
        }

        return $taxRateDefinitions;
    }

    /**
     * Get tax rates definition.
     *
     * @param Commerce_TaxRateModel $taxRate
     *
     * @return array
     */
    private function getTaxRateDefinition(Commerce_TaxRateModel $taxRate)
    {
        return [
            'name' => $taxRate->name,
            'rate' => $taxRate->rate,
            'include' => $taxRate->include,
            'isVat' => $taxRate->isVat,
            'taxable' => $taxRate->taxable,
            'taxCategory' => $taxRate->getTaxCategory()->handle,
            'taxZone' => $taxRate->getTaxZone()->name,
        ];
    }

    /**
     * Attempt to import tax rates.
     *
     * @param array $taxRateDefinitions
     * @param bool  $force              If set to true tax rates not included in the import will be deleted
     *
     * @return Result
     */
    public function import(array $taxRateDefinitions, $force = false)
    {
        Craft::log(Craft::t('Importing Commerce Tax Rates'));

        $taxRates = array();
        foreach (Craft::app()->commerce_taxRates->getAllTaxRates() as $taxRate) {
            $taxRates[$taxRate->name] = $taxRate;
        }

        foreach ($taxRateDefinitions as $taxRateDefinition) {
            $taxRateHandle = $taxRateDefinition['name'];
            $taxRate = array_key_exists($taxRateHandle, $taxRates)
                ? $taxRates[$taxRateHandle]
                : new Commerce_TaxRateModel();

            unset($taxRates[$taxRateHandle]);

            $this->populateTaxRate($taxRate, $taxRateDefinition, $taxRateHandle);

            if (!Craft::app()->commerce_taxRates->saveTaxRate($taxRate)) { // Save taxrate via craft
                $this->addErrors($taxRate->getAllErrors());

                continue;
            }
        }

        if ($force) {
            foreach ($taxRates as $taxRate) {
                Craft::app()->commerce_taxRates->deleteTaxRateById($taxRate->id);
            }
        }

        return $this->getResultModel();
    }

    /**
     * Populate taxRate.
     *
     * @param Commerce_TaxRateModel $taxRate
     * @param array                 $taxRateDefinition
     * @param string                $taxRateHandle
     */
    private function populateTaxRate(Commerce_TaxRateModel $taxRate, array $taxRateDefinition, $taxRateHandle)
    {
        $taxCategory = Craft::app()->commerce_taxCategories->getTaxCategoryByHandle($taxRateDefinition['taxCategory']);
        $taxZone = null;
        foreach (Craft::app()->commerce_taxZones->getAllTaxZones() as $zone) {
            if ($zone->name == $taxRateDefinition['taxZone']) {
                $taxZone = $zone->id;
            }
        }

        $taxRate->setAttributes([
            'name' => $taxRateHandle,
            'rate' => $taxRateDefinition['rate'],
            'include' => $taxRateDefinition['include'],
            'isVat' => $taxRateDefinition['isVat'],
            'taxable' => $taxRateDefinition['taxable'],
            'taxCategoryId' => $taxCategory->id,
            'taxZoneId' => $taxZone,
        ]);
    }
}

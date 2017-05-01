<?php

namespace NerdsAndCompany\Schematic\Commerce\Services;

use Craft\Craft;
use Craft\Commerce_PaymentCurrencyModel;
use NerdsAndCompany\Schematic\Services\Base;

/**
 * Schematic Commerce Payment Currencies Service.
 *
 * Sync Craft Setups.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2017, Nerds & Company
 * @license   MIT
 *
 * @see      http://www.nerds.company
 */
class PaymentCurrencies extends Base
{
    /**
     * Export paymentCurrencies.
     *
     * @param PaymentCurrencyModel[] $paymentCurrencies
     *
     * @return array
     */
    public function export(array $paymentCurrencies = [])
    {
        if (!count($paymentCurrencies)) {
            $paymentCurrencies = Craft::app()->commerce_paymentCurrencies->getAllPaymentCurrencies();
        }

        Craft::log(Craft::t('Exporting Commerce Payment Currencies'));

        $paymentCurrencyDefinitions = [];

        foreach ($paymentCurrencies as $paymentCurrency) {
            $paymentCurrencyDefinitions[$paymentCurrency->iso] = $this->getPaymentCurrencyDefinition($paymentCurrency);
        }

        return $paymentCurrencyDefinitions;
    }

    /**
     * Get payment currencies definition.
     *
     * @param Commerce_PaymentCurrencyModel $paymentCurrency
     *
     * @return array
     */
    private function getPaymentCurrencyDefinition(Commerce_PaymentCurrencyModel $paymentCurrency)
    {
        return [
            'iso' => $paymentCurrency->iso,
            'primary' => $paymentCurrency->primary,
            'rate' => $paymentCurrency->rate,
        ];
    }

    /**
     * Attempt to import payment currencies.
     *
     * @param array $paymentCurrencyDefinitions
     * @param bool  $force                      If set to true payment currencies not included in the import will be deleted
     *
     * @return Result
     */
    public function import(array $paymentCurrencyDefinitions, $force = false)
    {
        Craft::log(Craft::t('Importing Commerce Payment Currencies'));

        $this->resetCraftPaymentCurrenciesServiceCache();
        $paymentCurrencies = [];
        foreach (Craft::app()->commerce_paymentCurrencies->getAllPaymentCurrencies() as $paymentCurrency) {
            $paymentCurrencies[$paymentCurrency->iso] = $paymentCurrency;
        }

        foreach ($paymentCurrencyDefinitions as $paymentCurrencyDefinition) {
            $paymentCurrencyHandle = $paymentCurrencyDefinition['iso'];
            $paymentCurrency = array_key_exists($paymentCurrencyHandle, $paymentCurrencies)
                ? $paymentCurrencies[$paymentCurrencyHandle]
                : new Commerce_PaymentCurrencyModel();

            unset($paymentCurrencies[$paymentCurrencyHandle]);

            $this->populatePaymentCurrency($paymentCurrency, $paymentCurrencyDefinition, $paymentCurrencyHandle);

            if (!Craft::app()->commerce_paymentCurrencies->savePaymentCurrency($paymentCurrency)) { // Save paymentcurrency via craft
                $this->addErrors($paymentCurrency->getAllErrors());

                continue;
            }
        }

        if ($force) {
            foreach ($paymentCurrencies as $paymentCurrency) {
                Craft::app()->commerce_paymentCurrencies->deletePaymentCurrencyById($paymentCurrency->id);
            }
        }

        return $this->getResultModel();
    }

    /**
     * Populate paymentCurrency.
     *
     * @param Commerce_PaymentCurrencyModel $paymentCurrency
     * @param array                         $paymentCurrencyDefinition
     * @param string                        $paymentCurrencyHandle
     */
    private function populatePaymentCurrency(Commerce_PaymentCurrencyModel $paymentCurrency, array $paymentCurrencyDefinition, $paymentCurrencyHandle)
    {
        $paymentCurrency->setAttributes([
            'iso' => $paymentCurrencyHandle,
            'primary' => $paymentCurrencyDefinition['primary'],
            'rate' => $paymentCurrencyDefinition['rate'],
        ]);
    }

    /**
     * Reset service cache using reflection.
     */
    private function resetCraftPaymentCurrenciesServiceCache()
    {
        $obj = Craft::app()->commerce_paymentCurrencies;
        $refObject = new \ReflectionObject($obj);
        if ($refObject->hasProperty('_allCurrencies')) {
            $refProperty = $refObject->getProperty('_allCurrencies');
            $refProperty->setAccessible(true);
            $refProperty->setValue($obj, null);
        }
    }
}

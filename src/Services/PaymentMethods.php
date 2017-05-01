<?php

namespace NerdsAndCompany\Schematic\Commerce\Services;

use Craft\Craft;
use Craft\Commerce_PaymentMethodModel;
use NerdsAndCompany\Schematic\Services\Base;

/**
 * Schematic Commerce Payment Methods Service.
 *
 * Sync Craft Setups.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2017, Nerds & Company
 * @license   MIT
 *
 * @see      http://www.nerds.company
 */
class PaymentMethods extends Base
{
    /**
     * Export paymentMethods.
     *
     * @param PaymentMethodModel[] $paymentMethods
     *
     * @return array
     */
    public function export(array $paymentMethods = [])
    {
        if (!count($paymentMethods)) {
            $paymentMethods = Craft::app()->commerce_paymentMethods->getAllPaymentMethods();
        }

        Craft::log(Craft::t('Exporting Commerce Payment Methods'));

        $paymentMethodDefinitions = [];

        foreach ($paymentMethods as $paymentMethod) {
            $paymentMethodDefinitions[$paymentMethod->name] = $this->getPaymentMethodDefinition($paymentMethod);
        }

        return $paymentMethodDefinitions;
    }

    /**
     * Get payment methods definition.
     *
     * @param Commerce_PaymentMethodModel $paymentMethod
     *
     * @return array
     */
    private function getPaymentMethodDefinition(Commerce_PaymentMethodModel $paymentMethod)
    {
        return [
            'class' => $paymentMethod->class,
            'name' => $paymentMethod->name,
            'paymentType' => $paymentMethod->paymentType,
            'frontendEnabled' => $paymentMethod->frontendEnabled,
            'isArchived' => $paymentMethod->isArchived,
            'dateArchived' => $paymentMethod->dateArchived,
            'settings' => $paymentMethod->settings,
        ];
    }

    /**
     * Attempt to import payment methods.
     *
     * @param array $paymentMethodDefinitions
     * @param bool  $force                    If set to true payment methods not included in the import will be deleted
     *
     * @return Result
     */
    public function import(array $paymentMethodDefinitions, $force = false)
    {
        Craft::log(Craft::t('Importing Commerce Payment Methods'));

        $paymentMethods = [];
        foreach (Craft::app()->commerce_paymentMethods->getAllPaymentMethods() as $paymentMethod) {
            $paymentMethods[$paymentMethod->name] = $paymentMethod;
        }

        foreach ($paymentMethodDefinitions as $paymentMethodDefinition) {
            $paymentMethodHandle = $paymentMethodDefinition['name'];
            $paymentMethod = array_key_exists($paymentMethodHandle, $paymentMethods)
                ? $paymentMethods[$paymentMethodHandle]
                : new Commerce_PaymentMethodModel();

            unset($paymentMethods[$paymentMethodHandle]);

            $this->populatePaymentMethod($paymentMethod, $paymentMethodDefinition, $paymentMethodHandle);

            if (!Craft::app()->commerce_paymentMethods->savePaymentMethod($paymentMethod)) { // Save paymentmethod via craft
                $this->addErrors($paymentMethod->getAllErrors());

                continue;
            }
        }

        if ($force) {
            foreach ($paymentMethods as $paymentMethod) {
                Craft::app()->commerce_paymentMethods->archivePaymentMethod($paymentMethod->id);
            }
        }

        return $this->getResultModel();
    }

    /**
     * Populate paymentMethod.
     *
     * @param Commerce_PaymentMethodModel $paymentMethod
     * @param array                       $paymentMethodDefinition
     * @param string                      $paymentMethodHandle
     */
    private function populatePaymentMethod(Commerce_PaymentMethodModel $paymentMethod, array $paymentMethodDefinition, $paymentMethodHandle)
    {
        $paymentMethod->setAttributes([
            'name' => $paymentMethodHandle,
            'class' => $paymentMethodDefinition['class'],
            'paymentType' => $paymentMethodDefinition['paymentType'],
            'frontendEnabled' => $paymentMethodDefinition['frontendEnabled'],
            'isArchived' => $paymentMethodDefinition['isArchived'],
            'dateArchived' => $paymentMethodDefinition['dateArchived'],
            'settings' => $paymentMethodDefinition['settings'],
        ]);
    }
}

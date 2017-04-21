<?php

namespace NerdsAndCompany\Schematic\Commerce\Services;

use Craft\Craft;
use Craft\Commerce_ShippingMethodModel;
use NerdsAndCompany\Schematic\Services\Base;

/**
 * Schematic Commerce Shipping Methods Service.
 *
 * Sync Craft Setups.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2017, Nerds & Company
 * @license   MIT
 *
 * @see      http://www.nerds.company
 */
class ShippingMethods extends Base
{
    /**
     * Export shippingMethods.
     *
     * @param ShippingMethodModel[] $shippingMethods
     *
     * @return array
     */
    public function export(array $shippingMethods = [])
    {
        if (!count($shippingMethods)) {
            $shippingMethods = Craft::app()->commerce_shippingMethods->getAllShippingMethods();
        }

        Craft::log(Craft::t('Exporting Commerce Shipping Methods'));

        $shippingMethodDefinitions = [];

        foreach ($shippingMethods as $shippingMethod) {
            $shippingMethodDefinitions[$shippingMethod->handle] = $this->getShippingMethodDefinition($shippingMethod);
        }

        return $shippingMethodDefinitions;
    }

    /**
     * Get shipping methods definition.
     *
     * @param Commerce_ShippingMethodModel $shippingMethod
     *
     * @return array
     */
    private function getShippingMethodDefinition(Commerce_ShippingMethodModel $shippingMethod)
    {
        return [
            'name' => $shippingMethod->name,
            'enabled' => $shippingMethod->enabled,
        ];
    }

    /**
     * Attempt to import shipping methods.
     *
     * @param array $shippingMethodDefinitions
     * @param bool  $force                     If set to true shipping methods not included in the import will be deleted
     *
     * @return Result
     */
    public function import(array $shippingMethodDefinitions, $force = false)
    {
        Craft::log(Craft::t('Importing Commerce Shipping Methods'));

        $this->resetCraftShippingMethodsServiceCache();
        $shippingMethods = array();
        foreach (Craft::app()->commerce_shippingMethods->getAllShippingMethods() as $shippingMethod) {
            $shippingMethods[$shippingMethod->handle] = $shippingMethod;
        }

        foreach ($shippingMethodDefinitions as $shippingMethodHandle => $shippingMethodDefinition) {
            $shippingMethod = array_key_exists($shippingMethodHandle, $shippingMethods)
                ? $shippingMethods[$shippingMethodHandle]
                : new Commerce_ShippingMethodModel();

            unset($shippingMethods[$shippingMethodHandle]);

            $this->populateShippingMethod($shippingMethod, $shippingMethodDefinition, $shippingMethodHandle);

            if (!Craft::app()->commerce_shippingMethods->saveShippingMethod($shippingMethod)) { // Save shippingmethod via craft
                $this->addErrors($shippingMethod->getAllErrors());

                continue;
            }
        }

        if ($force) {
            foreach ($shippingMethods as $shippingMethod) {
                Craft::app()->commerce_shippingMethods->deleteShippingMethodById($shippingMethod->id);
            }
        }

        return $this->getResultModel();
    }

    /**
     * Populate shippingmethod.
     *
     * @param Commerce_ShippingMethodModel $shippingMethod
     * @param array                        $shippingMethodDefinition
     * @param string                       $shippingMethodHandle
     */
    private function populateShippingMethod(Commerce_ShippingMethodModel $shippingMethod, array $shippingMethodDefinition, $shippingMethodHandle)
    {
        $shippingMethod->setAttributes([
            'handle' => $shippingMethodHandle,
            'name' => $shippingMethodDefinition['name'],
            'enabled' => $shippingMethodDefinition['enabled'],
        ]);
    }

    /**
     * Reset service cache using reflection.
     */
    private function resetCraftShippingMethodsServiceCache()
    {
        $obj = Craft::app()->commerce_shippingMethods;
        $refObject = new \ReflectionObject($obj);
        if ($refObject->hasProperty('_shippingMethods')) {
            $refProperty = $refObject->getProperty('_shippingMethods');
            $refProperty->setAccessible(true);
            $refProperty->setValue($obj, null);
        }
    }
}

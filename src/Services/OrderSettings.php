<?php

namespace NerdsAndCompany\Schematic\Commerce\Services;

use Craft\Craft;
use Craft\Commerce_OrderSettingsModel;
use NerdsAndCompany\Schematic\Services\Base;

/**
 * Schematic Commerce Order Settings Service.
 *
 * Sync Craft Setups.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2017, Nerds & Company
 * @license   MIT
 *
 * @see      http://www.nerds.company
 */
class OrderSettings extends Base
{
    /**
     * Export orderSettings.
     *
     * @param OrderSettingsModel[] $orderSettings
     *
     * @return array
     */
    public function export(array $orderSettings = [])
    {
        if (!count($orderSettings)) {
            $orderSettings = [Craft::app()->commerce_orderSettings->getOrderSettingByHandle('order')];
        }

        Craft::log(Craft::t('Exporting Commerce Order Settings'));

        $orderSettingDefinitions = [];

        foreach ($orderSettings as $orderSetting) {
            $orderSettingDefinitions[$orderSetting->handle] = $this->getOrderSettingDefinition($orderSetting);
        }

        return $orderSettingDefinitions;
    }

    /**
     * Get order settings definition.
     *
     * @param Commerce_OrderSettingsModel $orderSetting
     *
     * @return array
     */
    private function getOrderSettingDefinition(Commerce_OrderSettingsModel $orderSetting)
    {
        $fieldLayout = Craft::app()->fields->getLayoutById($orderSetting->fieldLayoutId);

        return [
            'name' => $orderSetting->name,
            'fieldLayout' => Craft::app()->schematic_fields->getFieldLayoutDefinition($fieldLayout),
        ];
    }

    /**
     * Attempt to import order settings.
     *
     * @param array $orderSettingDefinitions
     * @param bool  $force                   If set to true order settings not included in the import will be deleted
     *
     * @return Result
     */
    public function import(array $orderSettingDefinitions, $force = false)
    {
        Craft::log(Craft::t('Importing Commerce Order Settings'));

        $settings = Craft::app()->commerce_orderSettings->getOrderSettingByHandle('order');
        $orderSettings = $settings ? ['order' => $settings] : [];

        foreach ($orderSettingDefinitions as $orderSettingHandle => $orderSettingDefinition) {
            $orderSetting = array_key_exists($orderSettingHandle, $orderSettings)
                ? $orderSettings[$orderSettingHandle]
                : new Commerce_OrderSettingsModel();

            unset($orderSettings[$orderSettingHandle]);

            $this->populateOrderSetting($orderSetting, $orderSettingDefinition, $orderSettingHandle);

            if (!Craft::app()->commerce_orderSettings->saveOrderSetting($orderSetting)) { // Save ordersettings via craft
                $this->addErrors($orderSetting->getAllErrors());

                continue;
            }
        }

        return $this->getResultModel();
    }

    /**
     * Populate ordersettings.
     *
     * @param Commerce_OrderSettingsModel $orderSetting
     * @param array                       $orderSettingDefinition
     * @param string                      $orderSettingHandle
     */
    private function populateOrderSetting(Commerce_OrderSettingsModel $orderSetting, array $orderSettingDefinition, $orderSettingHandle)
    {
        $orderSetting->setAttributes([
            'handle' => $orderSettingHandle,
            'name' => $orderSettingDefinition['name'],
        ]);

        $fieldLayout = Craft::app()->schematic_fields->getFieldLayout($orderSettingDefinition['fieldLayout']);
        $orderSetting->setFieldLayout($fieldLayout);
    }
}

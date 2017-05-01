<?php

namespace NerdsAndCompany\Schematic\Commerce\Services;

use Craft\Craft;
use Craft\Commerce_OrderStatusModel;
use NerdsAndCompany\Schematic\Services\Base;

/**
 * Schematic Commerce Order Statuses Service.
 *
 * Sync Craft Setups.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2017, Nerds & Company
 * @license   MIT
 *
 * @see      http://www.nerds.company
 */
class OrderStatuses extends Base
{
    /**
     * Export orderStatuses.
     *
     * @param OrderStatusModel[] $orderStatuses
     *
     * @return array
     */
    public function export(array $orderStatuses = [])
    {
        if (!count($orderStatuses)) {
            $orderStatuses = Craft::app()->commerce_orderStatuses->getAllOrderStatuses();
        }

        Craft::log(Craft::t('Exporting Commerce Order Statuses'));

        $orderStatusDefinitions = [];

        foreach ($orderStatuses as $orderStatus) {
            $orderStatusDefinitions[$orderStatus->handle] = $this->getOrderStatusDefinition($orderStatus);
        }

        return $orderStatusDefinitions;
    }

    /**
     * Get order statuses definition.
     *
     * @param Commerce_OrderStatusModel $orderStatus
     *
     * @return array
     */
    private function getOrderStatusDefinition(Commerce_OrderStatusModel $orderStatus)
    {
        return [
            'name' => $orderStatus->name,
            'color' => $orderStatus->color,
            'sortOrder' => $orderStatus->sortOrder,
            'default' => $orderStatus->default,
        ];
    }

    /**
     * Attempt to import order statuses.
     *
     * @param array $orderStatusDefinitions
     * @param bool  $force                  If set to true order statuses not included in the import will be deleted
     *
     * @return Result
     */
    public function import(array $orderStatusDefinitions, $force = false)
    {
        Craft::log(Craft::t('Importing Commerce Order Statuses'));

        $orderStatuses = [];
        foreach (Craft::app()->commerce_orderStatuses->getAllOrderStatuses() as $orderStatus) {
            $orderStatuses[$orderStatus->handle] = $orderStatus;
        }

        foreach ($orderStatusDefinitions as $orderStatusHandle => $orderStatusDefinition) {
            $orderStatus = array_key_exists($orderStatusHandle, $orderStatuses)
                ? $orderStatuses[$orderStatusHandle]
                : new Commerce_OrderStatusModel();

            unset($orderStatuses[$orderStatusHandle]);

            $this->populateOrderStatus($orderStatus, $orderStatusDefinition, $orderStatusHandle);

            if (!Craft::app()->commerce_orderStatuses->saveOrderStatus($orderStatus, [])) { // Save orderstatus via craft
                $this->addErrors($orderStatus->getAllErrors());

                continue;
            }
        }

        if ($force) {
            foreach ($orderStatuses as $orderStatus) {
                Craft::app()->commerce_orderStatuses->deleteOrderStatusById($orderStatus->id);
            }
        }

        return $this->getResultModel();
    }

    /**
     * Populate orderStatus.
     *
     * @param Commerce_OrderStatusModel $orderStatus
     * @param array                     $orderStatusDefinition
     * @param string                    $orderStatusHandle
     */
    private function populateOrderStatus(Commerce_OrderStatusModel $orderStatus, array $orderStatusDefinition, $orderStatusHandle)
    {
        $orderStatus->setAttributes([
            'handle' => $orderStatusHandle,
            'name' => $orderStatusDefinition['name'],
            'color' => $orderStatusDefinition['color'],
            'sortOrder' => $orderStatusDefinition['sortOrder'],
            'default' => $orderStatusDefinition['default'],
        ]);
    }
}

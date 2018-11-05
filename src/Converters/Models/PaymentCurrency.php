<?php

namespace NerdsAndCompany\Schematic\Commerce\Converters\Models;

use Craft;
use craft\base\Model;
use NerdsAndCompany\Schematic\Converters\Models\Base;

/**
 * Schematic Commerce Payment Currency Converter.
 *
 * Sync Craft Setups.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2018, Nerds & Company
 * @license   MIT
 *
 * @see      http://www.nerds.company
 */
class PaymentCurrency extends Base
{
    /**
     * {@inheritdoc}
     */
    public function getRecordDefinition(Model $record): array
    {
        return [
            'class' => get_class($record),
            'attributes' => [
                'iso' => $record->iso,
                'primary' => $record->primary,
                'rate' => $record->rate,
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function saveRecord(Model $record, array $definition): bool
    {
        $commerce = Craft::$app->getPlugins()->getPlugin('commerce');

        return $commerce->getPaymentCurrencies()->savePaymentCurrency($record);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteRecord(Model $record): bool
    {
        $commerce = Craft::$app->getPlugins()->getPlugin('commerce');

        return $commerce->getPaymentCurrencies()->deletePaymentCurrencyById($record->id);
    }

    /**
     * {@inheritdoc}
     */
    public function getRecordIndex(Model $record): string
    {
        return $record->iso;
    }
}

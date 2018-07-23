<?php

namespace NerdsAndCompany\Schematic\Commerce\DataTypes;

use Craft;
use NerdsAndCompany\Schematic\DataTypes\Base;

/**
 * Schematic Commerce Payment Currencies DataType.
 *
 * Sync Craft Setups.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2018, Nerds & Company
 * @license   MIT
 *
 * @see      http://www.nerds.company
 */
class PaymentCurrenciesDataType extends Base
{
    /**
     * {@inheritdoc}
     */
    public function getMapperHandle(): string
    {
        return 'modelMapper';
    }

    /**
     * {@inheritdoc}
     */
    public function getRecords(): array
    {
        $commerce = Craft::$app->getPlugins()->getPlugin('commerce');

        return $commerce->getPaymentCurrencies()->getAllPaymentCurrencies();
    }
}

<?php

namespace NerdsAndCompany\Schematic\Commerce;

use Craft;
use yii\base\Event;
use craft\base\Plugin;
use NerdsAndCompany\Schematic\Schematic;
use NerdsAndCompany\Schematic\Events\ConverterEvent;
use NerdsAndCompany\Schematic\Commerce\DataTypes\CountryDataType;
use NerdsAndCompany\Schematic\Commerce\DataTypes\EmailDataType;
use NerdsAndCompany\Schematic\Commerce\DataTypes\GatewaysDataType;
use NerdsAndCompany\Schematic\Commerce\DataTypes\OrderSettingsDataType;
use NerdsAndCompany\Schematic\Commerce\DataTypes\OrderStatusesDataType;
use NerdsAndCompany\Schematic\Commerce\DataTypes\PaymentCurrenciesDataType;
use NerdsAndCompany\Schematic\Commerce\DataTypes\ProductTypesDataType;
use NerdsAndCompany\Schematic\Commerce\DataTypes\ShippingCategoriesDataType;
use NerdsAndCompany\Schematic\Commerce\DataTypes\ShippingMethodsDataType;
use NerdsAndCompany\Schematic\Commerce\DataTypes\ShippingAddressZonesDataType;
use NerdsAndCompany\Schematic\Commerce\DataTypes\StatesDataType;
use NerdsAndCompany\Schematic\Commerce\DataTypes\TaxCategoriesDataType;
use NerdsAndCompany\Schematic\Commerce\DataTypes\TaxRatesDataType;
use NerdsAndCompany\Schematic\Commerce\DataTypes\TaxAddressZonesDataType;

/**
 * Schematic Commerce Plugin.
 *
 * Sync Craft Setups.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2018, Nerds & Company
 * @license   MIT
 *
 * @see      http://www.nerds.company
 */
class SchematicCommerce extends Plugin
{
    /**
     * Add new converters and datatypes to schematic.
     */
    public function init()
    {
        // Register extra data types
        $schematic = Craft::$app->plugins->getPlugin('schematic');
        $config = [
            'components' => $schematic->components,
            'dataTypes' => array_merge($schematic->dataTypes, [
                'countries' => CountryDataType::class,
                'emails' => EmailDataType::class,
                'gateways' => GatewaysDataType::class,
                'orderSettings' => OrderSettingsDataType::class,
                'orderStatuses' => OrderStatusesDataType::class,
                'paymentCurrencies' => PaymentCurrenciesDataType::class,
                'productTypes' => ProductTypesDataType::class,
                'shippingCategories' => ShippingCategoriesDataType::class,
                'shippingMethods' => ShippingMethodsDataType::class,
                'shippingZones' => ShippingAddressZonesDataType::class,
                'states' => StatesDataType::class,
                'taxCategories' => TaxCategoriesDataType::class,
                'taxRates' => TaxRatesDataType::class,
                'taxZones' => TaxAddressZonesDataType::class,
            ]),
        ];
        Craft::configure($schematic, $config);

        // Init plugin
        parent::init();

        // Register our converters for Commerce models
        Event::on(Schematic::class, Schematic::EVENT_RESOLVE_CONVERTER, function (ConverterEvent $event) {
            $modelClass = $event->modelClass;
            if (strpos($modelClass, 'craft\\commerce') !== false) {
                $converterClass = 'NerdsAndCompany\\Schematic\\Commerce\\Converters\\'.ucfirst(str_replace('craft\\commerce\\', '', $modelClass));
                $event->converterClass = $converterClass;
            }
        });
    }
}

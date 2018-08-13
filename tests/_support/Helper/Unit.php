<?php

namespace Helper;

use Craft;
use craft\console\Application;
use yii\console\Controller;
use craft\services\Plugins;
use craft\commerce\Plugin as Commerce;
use craft\commerce\services\Countries;
use craft\commerce\services\Emails;
use craft\commerce\services\Gateways;
use craft\commerce\services\OrderSettings;
use craft\commerce\services\OrderStatuses;
use craft\commerce\services\PaymentCurrencies;
use craft\commerce\services\ProductTypes;
use craft\commerce\services\ShippingCategories;
use craft\commerce\services\ShippingMethods;
use craft\commerce\services\ShippingZones;
use craft\commerce\services\States;
use craft\commerce\services\TaxCategories;
use craft\commerce\services\TaxRates;
use craft\commerce\services\TaxZones;
use Codeception\Module;
use Codeception\TestCase;
use NerdsAndCompany\Schematic\Schematic;
use NerdsAndCompany\Schematic\Commerce\SchematicCommerce;

/**
 * UnitTest helper.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Unit extends Module
{
    /**
     * Mock craft Mappers.
     *
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     * phpcs:disable PSR2.Methods.MethodDeclaration.Underscore
     *
     * @param TestCase $test
     */
    public function _before(TestCase $test)
    {
        $mockApp = $this->getMockApp($test);
        $mockApp->controller = $this->getMock($test, Controller::class);
        $mockApp->controller->module = $this->getmockModule($test);

        Craft::$app = $mockApp;
        Schematic::$force = false;
    }

    /**
     * Get a preconfigured mock module.
     *
     * @param TestCase $test
     *
     * @return Mock|Schematic
     */
    private function getMockModule(TestCase $test)
    {
        $mockModule = $this->getMock($test, SchematicCommerce::class);

        return $mockModule;
    }

    /**
     * Get a preconfigured mock app.
     *
     * @param TestCase $test
     *
     * @return Mock|Application
     */
    private function getMockApp(TestCase $test)
    {
        $mockApp = $this->getMock($test, Application::class);
        $mockPlugins = $this->getMock($test, Plugins::class);
        $mockCommerce = $this->getMock($test, Commerce::class);
        $mockCountries = $this->getMock($test, Countries::class);
        $mockEmails = $this->getMock($test, Emails::class);
        $mockGateways = $this->getMock($test, Gateways::class);
        $mockOrderSettings = $this->getMock($test, OrderSettings::class);
        $mockOrderStatuses = $this->getMock($test, OrderStatuses::class);
        $mockPaymentCurrencies = $this->getMock($test, PaymentCurrencies::class);
        $mockProductTypes = $this->getMock($test, ProductTypes::class);
        $mockShippingCategories = $this->getMock($test, ShippingCategories::class);
        $mockShippingMethods = $this->getMock($test, ShippingMethods::class);
        $mockShippingZones = $this->getMock($test, ShippingZones::class);
        $mockStates = $this->getMock($test, States::class);
        $mockTaxCategories = $this->getMock($test, TaxCategories::class);
        $mockTaxRates = $this->getMock($test, TaxRates::class);
        $mockTaxZones = $this->getMock($test, TaxZones::class);

        $mockCommerce->expects($test->any())
            ->method('__get')
            ->willReturnMap([
                ['countries', $mockCountries],
                ['emails', $mockEmails],
                ['gateways', $mockGateways],
                ['orderSettings', $mockOrderSettings],
                ['orderStatuses', $mockOrderStatuses],
                ['paymentCurrencies', $mockPaymentCurrencies],
                ['productTypes', $mockProductTypes],
                ['shippingCategories', $mockShippingCategories],
                ['shippingMethods', $mockShippingMethods],
                ['shippingZones', $mockShippingZones],
                ['states', $mockStates],
                ['taxCategories', $mockTaxCategories],
                ['taxRates', $mockTaxRates],
                ['taxZones', $mockTaxZones],
            ]);

        $mockPlugins->expects($test->any())
                    ->method('getPlugin')
                    ->willReturn($mockCommerce);

        $mockApp->expects($test->any())
                ->method('getPlugins')
                ->willReturn($mockPlugins);

        return $mockApp;
    }

    /**
     * Get a mock object for class.
     *
     * @param TestCase $test
     * @param string   $class
     *
     * @return Mock
     */
    private function getMock(TestCase $test, string $class)
    {
        return $test->getMockBuilder($class)
                ->disableOriginalConstructor()
                ->getMock();
    }
}

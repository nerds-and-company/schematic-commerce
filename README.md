# Schematic for Commerce [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/nerds-and-company/schematic-commerce/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/nerds-and-company/schematic-commerce/?branch=master) [![Build Status](https://travis-ci.org/nerds-and-company/schematic-commerce.svg?branch=master)](https://travis-ci.org/nerds-and-company/schematic-commerce) [![Code Coverage](https://scrutinizer-ci.com/g/nerds-and-company/schematic-commerce/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/nerds-and-company/schematic-commerce/?branch=master)

Schematic for Commerce is a package for synchronizing Commerce settings with [Schematic](https://github.com/nerds-and-company/schematic).

## Installation

This tool can be installed [using Composer](https://getcomposer.org/doc/00-intro.md). Run the following command from the root of your project:

```
composer require nerds-and-company/schematic-commerce
```

This will add `nerds-and-company/schematic-commerce` as a requirement to your  project's `composer.json` file and install the source-code into the `vendor/nerds-and-company/schematic-commerce` directory.

## Usage

This package should be loaded with a Craft plugin, which implements the `registerMigrationService()` hook of Schematic:

```php
use NerdsAndCompany\Schematic\Commerce\Services as Commerce;

public function registerMigrationService()
{
    return [
        'commerce_orderSettings' => new Commerce\OrderSettings(),
        'commerce_emails' => new Commerce\Emails(),
        'commerce_orderStatuses' => new Commerce\OrderStatuses(),
        'commerce_paymentMethods' => new Commerce\PaymentMethods(),
        'commerce_paymentCurrencies' => new Commerce\PaymentCurrencies(),
        'commerce_productTypes' => new Commerce\ProductTypes(),
        'commerce_countries' => new Commerce\Countries(),
        'commerce_states' => new Commerce\States(),
        'commerce_taxCategories' => new Commerce\TaxCategories(),
        'commerce_taxZones' => new Commerce\TaxZones(),
        'commerce_taxRates' => new Commerce\TaxRates(),
        'commerce_shippingCategories' => new Commerce\ShippingCategories(),
        'commerce_shippingZones' => new Commerce\ShippingZones(),
        'commerce_shippingMethods' => new Commerce\ShippingMethods(),
    ];
}
```

Here is a list of all of the supported Commerce data types:

| Data Type |
| ------------- |
| Order Settings |
| Order Statuses |
| Emails |
| Payment Methods |
| Payment Currencies |
| Product Types |
| Countries |
| States |
| Tax Categories |
| Tax Rates |
| Shipping Categories |
| Shipping Zones |
| Shipping Methods |

## License

This project has been licensed under the MIT License (MIT). Please see [License File](LICENSE) for more information.

## Changelog

[CHANGELOG.md](CHANGELOG.md)

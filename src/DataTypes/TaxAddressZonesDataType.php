<?php

namespace NerdsAndCompany\Schematic\Commerce\DataTypes;

use Craft;
use NerdsAndCompany\Schematic\DataTypes\Base;

/**
 * Schematic Commerce Tax Address Zones DataType.
 *
 * Sync Craft Setups.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2018, Nerds & Company
 * @license   MIT
 *
 * @see      http://www.nerds.company
 */
class TaxAddressZonesDataType extends Base
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

        return $commerce->getTaxZones()->getAllTaxZones();
    }

    /**
     * {@inheritdoc}
     */
    public function afterImport()
    {
        $commerce = Craft::$app->getPlugins()->getPlugin('commerce');

        $obj = $commerce->getTaxZones();
        $refObject = new \ReflectionObject($obj);
        if ($refObject->hasProperty('_fetchedAllTaxZones')) {
            $refProperty1 = $refObject->getProperty('_fetchedAllTaxZones');
            $refProperty1->setAccessible(true);
            $refProperty1->setValue($obj, false);
        }
    }
}

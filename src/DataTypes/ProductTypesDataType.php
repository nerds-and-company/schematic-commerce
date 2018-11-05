<?php

namespace NerdsAndCompany\Schematic\Commerce\DataTypes;

use Craft;
use NerdsAndCompany\Schematic\DataTypes\Base;

/**
 * Schematic Commerce Product Types DataType.
 *
 * Sync Craft Setups.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2018, Nerds & Company
 * @license   MIT
 *
 * @see      http://www.nerds.company
 */
class ProductTypesDataType extends Base
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

        return $commerce->getProductTypes()->getAllProductTypes();
    }

    /**
     * {@inheritdoc}
     */
    public function afterImport()
    {
        $commerce = Craft::$app->getPlugins()->getPlugin('commerce');

        $obj = $commerce->getProductTypes();
        $refObject = new \ReflectionObject($obj);
        if ($refObject->hasProperty('_fetchedAllProductTypes')) {
            $refProperty1 = $refObject->getProperty('_fetchedAllProductTypes');
            $refProperty1->setAccessible(true);
            $refProperty1->setValue($obj, false);
        }
    }
}

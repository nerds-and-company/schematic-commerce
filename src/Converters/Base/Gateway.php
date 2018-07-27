<?php

namespace NerdsAndCompany\Schematic\Commerce\Converters\Base;

use Craft;
use craft\base\Model;
use NerdsAndCompany\Schematic\Converters\Models\Base;

/**
 * Schematic Commerce Gateway Converter.
 *
 * Sync Craft Setups.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2018, Nerds & Company
 * @license   MIT
 *
 * @see      http://www.nerds.company
 */
class Gateway extends Base
{
    /**
     * {@inheritdoc}
     */
    public function saveRecord(Model $record, array $definition): bool
    {
        $commerce = Craft::$app->getPlugins()->getPlugin('commerce');

        return $commerce->getGateways()->saveGateway($record);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteRecord(Model $record): bool
    {
        $commerce = Craft::$app->getPlugins()->getPlugin('commerce');

        return $commerce->getGateways()->archiveGatewayById($record->id);
    }
}

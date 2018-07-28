<?php

namespace NerdsAndCompany\Schematic\Commerce\Converters\Models;

use Craft;
use craft\base\Model;
use NerdsAndCompany\Schematic\Converters\Models\Base;

/**
 * Schematic Commerce Order Status Converter.
 *
 * Sync Craft Setups.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2018, Nerds & Company
 * @license   MIT
 *
 * @see      http://www.nerds.company
 */
class OrderStatus extends Base
{
    /**
     * @var bool|array
     */
    private $emails = false;

    /**
     * {@inheritdoc}
     */
    public function getRecordDefinition(Model $record): array
    {
        $definition = parent::getRecordDefinition($record);

        $definition['emails'] = [];
        foreach ($record->getEmails() as $email) {
            $definition['emails'][] = $email->name;
        }

        return $definition;
    }

    /**
     * {@inheritdoc}
     */
    public function saveRecord(Model $record, array $definition): bool
    {
        $commerce = Craft::$app->getPlugins()->getPlugin('commerce');

        if (!$this->emails) {
            $this->emails = [];
            foreach ($commerce->getEmails()->getAllEmails() as $email) {
                $this->emails[$email->name] = $email;
            }
        }

        $emailIds = [];
        if (is_array(@$definition['emails'])) {
            foreach ($definition['emails'] as $emailName) {
                if (array_key_exists($emailName, $this->emails)) {
                    $emailIds[] = $this->emails[$emailName]->id;
                }
            }
        }

        return $commerce->getOrderStatuses()->saveOrderStatus($record, $emailIds);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteRecord(Model $record): bool
    {
        $commerce = Craft::$app->getPlugins()->getPlugin('commerce');

        return $commerce->getOrderStatuses()->deleteOrderStatusById($record->id);
    }
}

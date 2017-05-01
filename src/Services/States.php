<?php

namespace NerdsAndCompany\Schematic\Commerce\Services;

use Craft\Craft;
use Craft\Commerce_StateModel;
use NerdsAndCompany\Schematic\Services\Base;

/**
 * Schematic Commerce States Service.
 *
 * Sync Craft Setups.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2017, Nerds & Company
 * @license   MIT
 *
 * @see      http://www.nerds.company
 */
class States extends Base
{
    /**
     * Export states.
     *
     * @param StateModel[] $states
     *
     * @return array
     */
    public function export(array $states = [])
    {
        if (!count($states)) {
            $states = Craft::app()->commerce_states->getAllStates();
        }

        Craft::log(Craft::t('Exporting Commerce States'));

        $stateDefinitions = [];

        foreach ($states as $state) {
            $stateDefinitions[$state->abbreviation] = $this->getStateDefinition($state);
        }

        return $stateDefinitions;
    }

    /**
     * Get states definition.
     *
     * @param Commerce_StateModel $state
     *
     * @return array
     */
    private function getStateDefinition(Commerce_StateModel $state)
    {
        return [
            'name' => $state->name,
            'country' => $state->getCountry()->iso,
        ];
    }

    /**
     * Attempt to import states.
     *
     * @param array $stateDefinitions
     * @param bool  $force            If set to true states not included in the import will be deleted
     *
     * @return Result
     */
    public function import(array $stateDefinitions, $force = false)
    {
        Craft::log(Craft::t('Importing Commerce States'));

        $states = [];
        foreach (Craft::app()->commerce_states->getAllStates() as $state) {
            $states[$state->abbreviation] = $state;
        }

        foreach ($stateDefinitions as $stateHandle => $stateDefinition) {
            $state = array_key_exists($stateHandle, $states)
                ? $states[$stateHandle]
                : new Commerce_StateModel();

            unset($states[$stateHandle]);

            $this->populateState($state, $stateDefinition, $stateHandle);

            if (!Craft::app()->commerce_states->saveState($state)) { // Save state via craft
                $this->addErrors($state->getAllErrors());

                continue;
            }
        }

        if ($force) {
            foreach ($states as $state) {
                Craft::app()->commerce_states->deleteStateById($state->id);
            }
        }

        return $this->getResultModel();
    }

    /**
     * Populate state.
     *
     * @param Commerce_StateModel $state
     * @param array               $stateDefinition
     * @param string              $stateHandle
     */
    private function populateState(Commerce_StateModel $state, array $stateDefinition, $stateHandle)
    {
        $country = Craft::app()->commerce_countries->getCountryByAttributes(['iso' => $stateDefinition['country']]);

        $state->setAttributes([
            'abbreviation' => $stateHandle,
            'name' => $stateDefinition['name'],
            'countryId' => $country ? $country->id : null,
        ]);
    }
}

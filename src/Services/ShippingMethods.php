<?php

namespace NerdsAndCompany\Schematic\Commerce\Services;

use Craft\Craft;
use Craft\Commerce_ShippingMethodModel;
use Craft\Commerce_ShippingRuleModel;
use Craft\Commerce_ShippingRuleCategoryModel;
use Craft\Commerce_ShippingZoneRecord;
use NerdsAndCompany\Schematic\Services\Base;

/**
 * Schematic Commerce Shipping Methods Service.
 *
 * Sync Craft Setups.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2017, Nerds & Company
 * @license   MIT
 *
 * @see      http://www.nerds.company
 */
class ShippingMethods extends Base
{
    /**
     * Export shippingMethods.
     *
     * @param ShippingMethodModel[] $shippingMethods
     *
     * @return array
     */
    public function export(array $shippingMethods = [])
    {
        if (!count($shippingMethods)) {
            $shippingMethods = Craft::app()->commerce_shippingMethods->getAllShippingMethods();
        }

        Craft::log(Craft::t('Exporting Commerce Shipping Methods'));

        $shippingMethodDefinitions = [];

        foreach ($shippingMethods as $shippingMethod) {
            $shippingMethodDefinitions[$shippingMethod->handle] = $this->getShippingMethodDefinition($shippingMethod);
        }

        return $shippingMethodDefinitions;
    }

    /**
     * Get shipping methods definition.
     *
     * @param Commerce_ShippingMethodModel $shippingMethod
     *
     * @return array
     */
    private function getShippingMethodDefinition(Commerce_ShippingMethodModel $shippingMethod)
    {
        return [
            'name' => $shippingMethod->name,
            'enabled' => $shippingMethod->enabled,
            'rules' => $this->getRuleDefinitions($shippingMethod->getRules()),
        ];
    }

    /**
     * Get rule definitions.
     *
     * @param Commerce_ShippingRuleModel[] $rules
     *
     * @return array
     */
    private function getRuleDefinitions(array $rules)
    {
        $ruleDefinitions = [];

        foreach ($rules as $rule) {
            $ruleDefinitions[$rule->name] = $this->getRuleDefinition($rule);
        }

        return $ruleDefinitions;
    }

    /**
     * Get rule definition.
     *
     * @param Commerce_ShippingRuleModel $rule
     *
     * @return array
     */
    private function getRuleDefinition(Commerce_ShippingRuleModel $rule)
    {
        return [
            'name' => $rule->name,
            'description' => $rule->description,
            'shippingZone' => $rule->shippingZone ? $rule->shippingZone->name : null,
            'priority' => $rule->priority,
            'enabled' => $rule->enabled,
            'minQty' => $rule->minQty,
            'maxQty' => $rule->maxQty,
            'minTotal' => $rule->minTotal,
            'maxTotal' => $rule->maxTotal,
            'minWeight' => $rule->minWeight,
            'maxWeight' => $rule->maxWeight,
            'baseRate' => $rule->baseRate,
            'perItemRate' => $rule->perItemRate,
            'weightRate' => $rule->weightRate,
            'percentageRate' => $rule->percentageRate,
            'minRate' => $rule->minRate,
            'maxRate' => $rule->maxRate,
            'categories' => $this->getCategoryDefinitions($rule->getShippingRuleCategories()),
        ];
    }

    /**
     * Get category definitions.
     *
     * @param Commerce_ShippingRuleCategoryModel[] $categories
     *
     * @return array
     */
    private function getCategoryDefinitions(array $categories)
    {
        $categoryDefinitions = [];

        foreach ($categories as $category) {
            $categoryDefinitions[$category->getCategory()->handle] = $this->getCategoryDefinition($category);
        }

        return $categoryDefinitions;
    }

    /**
     * Get category definition.
     *
     * @param Commerce_ShippingRuleCategoryModel $category
     *
     * @return array
     */
    private function getCategoryDefinition(Commerce_ShippingRuleCategoryModel $category)
    {
        return [
            'condition' => $category->condition,
            'perItemRate' => $category->perItemRate,
            'weightRate' => $category->weightRate,
            'percentageRate' => $category->percentageRate,
        ];
    }

    /**
     * Attempt to import shipping methods.
     *
     * @param array $shippingMethodDefinitions
     * @param bool  $force                     If set to true shipping methods not included in the import will be deleted
     *
     * @return Result
     */
    public function import(array $shippingMethodDefinitions, $force = false)
    {
        Craft::log(Craft::t('Importing Commerce Shipping Methods'));

        $this->resetCraftShippingMethodsServiceCache();
        $this->resetCraftShippingCategoriesServiceCache();
        $shippingMethods = array();
        foreach (Craft::app()->commerce_shippingMethods->getAllShippingMethods() as $shippingMethod) {
            $shippingMethods[$shippingMethod->handle] = $shippingMethod;
        }

        foreach ($shippingMethodDefinitions as $shippingMethodHandle => $shippingMethodDefinition) {
            $shippingMethod = array_key_exists($shippingMethodHandle, $shippingMethods)
                ? $shippingMethods[$shippingMethodHandle]
                : new Commerce_ShippingMethodModel();

            unset($shippingMethods[$shippingMethodHandle]);

            $this->populateShippingMethod($shippingMethod, $shippingMethodDefinition, $shippingMethodHandle);

            if (!Craft::app()->commerce_shippingMethods->saveShippingMethod($shippingMethod)) { // Save shippingmethod via craft
                $this->addErrors($shippingMethod->getAllErrors());

                continue;
            }

            $this->populateShippingMethodRules($shippingMethod, $shippingMethodDefinition['rules'], $force);
        }

        if ($force) {
            foreach ($shippingMethods as $shippingMethod) {
                Craft::app()->commerce_shippingMethods->delete($shippingMethod);
            }
        }

        return $this->getResultModel();
    }

    /**
     * Populate shippingmethod.
     *
     * @param Commerce_ShippingMethodModel $shippingMethod
     * @param array                        $shippingMethodDefinition
     * @param string                       $shippingMethodHandle
     */
    private function populateShippingMethod(Commerce_ShippingMethodModel $shippingMethod, array $shippingMethodDefinition, $shippingMethodHandle)
    {
        $shippingMethod->setAttributes([
            'handle' => $shippingMethodHandle,
            'name' => $shippingMethodDefinition['name'],
            'enabled' => $shippingMethodDefinition['enabled'],
        ]);
    }

    /**
     * Populate shipping method rules.
     *
     * @param Commerce_ShippingMethodModel $shippingMethod
     * @param array                        $ruleDefinitions
     * @param bool                         $force
     */
    private function populateShippingMethodRules(Commerce_ShippingMethodModel $shippingMethod, $ruleDefinitions, $force = false)
    {
        $rules = array();
        foreach ($shippingMethod->getRules() as $rule) {
            $rules[$rule->name] = $rule;
        }

        foreach ($ruleDefinitions as $ruleName => $ruleDef) {
            $rule = array_key_exists($ruleName, $rules) ? $rules[$ruleName] : new Commerce_ShippingRuleModel();

            unset($rules[$ruleName]);

            $shippingZone = Commerce_ShippingZoneRecord::model()->findByAttributes(array('name' => $ruleDef['shippingZone']));

            $rule->setAttributes([
                'name' => $ruleName,
                'description' => $ruleDef['description'],
                'shippingZoneId' => $shippingZone ? $shippingZone->id : null,
                'methodId' => $shippingMethod->id,
                'priority' => $ruleDef['priority'],
                'enabled' => $ruleDef['enabled'],
                'minQty' => $ruleDef['minQty'],
                'maxQty' => $ruleDef['maxQty'],
                'minTotal' => $ruleDef['minTotal'],
                'maxTotal' => $ruleDef['maxTotal'],
                'minWeight' => $ruleDef['minWeight'],
                'maxWeight' => $ruleDef['maxWeight'],
                'baseRate' => $ruleDef['baseRate'],
                'perItemRate' => $ruleDef['perItemRate'],
                'weightRate' => $ruleDef['weightRate'],
                'percentageRate' => $ruleDef['percentageRate'],
                'minRate' => $ruleDef['minRate'],
                'maxRate' => $ruleDef['maxRate'],
            ]);

            $this->populateShippingMethodRuleCategories($rule, $ruleDef['categories']);

            if (!Craft::app()->commerce_shippingRules->saveShippingRule($rule)) { // Save shippingrule via craft
                $this->addErrors($rule->getAllErrors());

                continue;
            }
        }

        if ($force) {
            foreach ($rules as $rule) {
                Craft::app()->commerce_shippingRules->deleteShippingRuleById($rule->id);
            }
        }
    }

    /**
     * Populate shipping method rule categories.
     *
     * @param Commerce_ShippingRuleModel $rule
     * @param $categoryDefinitions
     */
    private function populateShippingMethodRuleCategories(Commerce_ShippingRuleModel $rule, $categoryDefinitions)
    {
        $categories = array();
        foreach ($rule->getShippingRuleCategories() as $category) {
            $categories[$category->getCategory()->handle] = $category;
        }

        foreach ($categoryDefinitions as $categoryHandle => $categoryDef) {
            $category = array_key_exists($categoryHandle, $categories) ? $categories[$categoryHandle] : new Commerce_ShippingRuleCategoryModel();

            $category->setAttributes([
                'shippingCategoryId' => Craft::app()->commerce_shippingCategories->getShippingCategoryByHandle($categoryHandle)->id,
                'condition' => $categoryDef['condition'],
                'perItemRate' => $categoryDef['perItemRate'],
                'weightRate' => $categoryDef['weightRate'],
                'percentageRate' => $categoryDef['percentageRate'],
            ]);

            $categories[$category->shippingCategoryId] = $category;
        }

        $rule->setShippingRuleCategories($categories);
    }

    /**
     * Reset service cache using reflection.
     */
    private function resetCraftShippingMethodsServiceCache()
    {
        $obj = Craft::app()->commerce_shippingMethods;
        $refObject = new \ReflectionObject($obj);
        if ($refObject->hasProperty('_shippingMethods')) {
            $refProperty = $refObject->getProperty('_shippingMethods');
            $refProperty->setAccessible(true);
            $refProperty->setValue($obj, null);
        }
    }

    /**
     * Reset service cache using reflection.
     */
    private function resetCraftShippingCategoriesServiceCache()
    {
        $obj = Craft::app()->commerce_shippingCategories;
        $refObject = new \ReflectionObject($obj);
        if ($refObject->hasProperty('_fetchedAllShippingCategories')) {
            $refProperty = $refObject->getProperty('_fetchedAllShippingCategories');
            $refProperty->setAccessible(true);
            $refProperty->setValue($obj, false);
        }
        if ($refObject->hasProperty('_shippingCategoriesById')) {
            $refProperty = $refObject->getProperty('_shippingCategoriesById');
            $refProperty->setAccessible(true);
            $refProperty->setValue($obj, array());
        }
        if ($refObject->hasProperty('_shippingCategoriesByHandle')) {
            $refProperty = $refObject->getProperty('_shippingCategoriesByHandle');
            $refProperty->setAccessible(true);
            $refProperty->setValue($obj, array());
        }
    }
}

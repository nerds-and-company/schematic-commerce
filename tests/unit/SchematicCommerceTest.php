<?php

namespace NerdsAndCompany\Schematic\Commerce;

use Codeception\Test\Unit;
use NerdsAndCompany\Schematic\Interfaces\DataTypeInterface;

/**
 * Class SchematicCommerceTest.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2018, Nerds & Company
 * @license   MIT
 *
 * @see      http://www.nerds.company
 */
class SchematicCommerceTest extends Unit
{
    /**
     * @var SchematicCommere
     */
    private $module;

    /**
     * Set the mapper.
     *
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     * phpcs:disable PSR2.Methods.MethodDeclaration.Underscore
     */
    protected function _before()
    {
        $this->module = new SchematicCommerce('schematic-commerce');
    }

    //==============================================================================================================
    //=================================================  TESTS  ====================================================
    //==============================================================================================================

    /**
     * @dataProvider provideDataTypes
     *
     * @param string $dataTypeHandle
     * @param bool   $valid
     * @param string $dataTypeClass
     */
    public function testGetDataType(string $dataTypeHandle, bool $valid, string $dataTypeClass)
    {
        if ($dataTypeClass) {
            $this->module->dataTypes[$dataTypeHandle] = $dataTypeClass;
        }

        $result = $this->module->getDataType($dataTypeHandle);

        if ($valid) {
            $this->assertInstanceOf(DataTypeInterface::class, $result);
        } else {
            $this->assertNull($result);
        }
    }

    //==============================================================================================================
    //==============================================  PROVIDERS  ===================================================
    //==============================================================================================================

    /**
     * @return array
     */
    public function provideDataTypes()
    {
        return [
            'existing dataType' => [
                'dataTypeHandle' => 'sections',
                'valid' => true,
                'dataTypeClass' => '',
            ],
            'dataType not registerd' => [
                'dataTypeHandle' => 'unregistered',
                'valid' => false,
                'dataTypeClass' => '',
            ],
            'dataTypeClass does not exist' => [
                'dataTypeHandle' => 'notExists',
                'valid' => false,
                'dataTypeClass' => 'NotExists',
            ],
            'dataTypeClass does not implement interface' => [
                'dataTypeHandle' => 'implements',
                'valid' => false,
                'dataTypeClass' => \stdClass::class,
            ],
        ];
    }
}

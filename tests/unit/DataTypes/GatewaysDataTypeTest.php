<?php

namespace NerdsAndCompany\Schematic\Commerce\DataTypes;

use Craft;
use craft\commerce\base\Gateway;
use Codeception\Test\Unit;

/**
 * Class GatewaysDataTypeTest.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2018, Nerds & Company
 * @license   MIT
 *
 * @see      http://www.nerds.company
 */
class GatewaysDataTypeTest extends Unit
{
    /**
     * @var GatewaysDataType
     */
    private $dataType;

    /**
     * Set the dataType.
     *
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     * phpcs:disable PSR2.Methods.MethodDeclaration.Underscore
     */
    protected function _before()
    {
        $this->dataType = new GatewaysDataType();
    }

    //==============================================================================================================
    //=================================================  TESTS  ====================================================
    //==============================================================================================================

    /**
     * Get mapper handle test.
     */
    public function testGetMapperHandle()
    {
        $result = $this->dataType->getMapperHandle();

        $this->assertSame('modelMapper', $result);
    }

    /**
     * Get records test.
     */
    public function testGetRecords()
    {
        $records = [$this->getMockGateway()];

        $commerce = Craft::$app->getPlugins()->getPlugin('commerce');
        $commerce->getGateways()->expects($this->exactly(1))
                                ->method('getAllGateways')
                                ->willReturn($records);

        $result = $this->dataType->getRecords();

        $this->assertSame($records, $result);
    }

    //==============================================================================================================
    //================================================  HELPERS  ===================================================
    //==============================================================================================================

    /**
     * @return Mock|Gateway
     */
    private function getMockGateway()
    {
        return $this->getMockBuilder(Gateway::class)->getMock();
    }
}

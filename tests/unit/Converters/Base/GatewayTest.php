<?php

namespace NerdsAndCompany\Schematic\Commerce\Converters\Base;

use Craft;
use craft\commerce\base\Gateway as GatewayModel;
use craft\commerce\gateways\Dummy as DummyGateway;
use Codeception\Test\Unit;

/**
 * Class GatewayTest.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2018, Nerds & Company
 * @license   MIT
 *
 * @see      http://www.nerds.company
 */
class GatewayTest extends Unit
{
    /**
     * @var Gateway
     */
    private $converter;

    /**
     * Set the converter.
     *
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     * phpcs:disable PSR2.Methods.MethodDeclaration.Underscore
     */
    protected function _before()
    {
        $this->converter = new Gateway();
    }

    //==============================================================================================================
    //=================================================  TESTS  ====================================================
    //==============================================================================================================

    /**
     * @dataProvider provideGateways
     *
     * @param GatewayModel $gateway
     * @param array        $definition
     */
    public function testGetRecordDefinition(GatewayModel $gateway, array $definition)
    {
        $result = $this->converter->getRecordDefinition($gateway);

        $this->assertSame($definition, $result);
    }

    /**
     * @dataProvider provideGateways
     *
     * @param GatewayModel $gateway
     * @param array        $definition
     */
    public function testSaveRecord(GatewayModel $gateway, array $definition)
    {
        $commerce = Craft::$app->getPlugins()->getPlugin('commerce');
        $commerce->getGateways()->expects($this->exactly(1))
                                ->method('saveGateway')
                                ->with($gateway)
                                ->willReturn(true);

        $result = $this->converter->saveRecord($gateway, $definition);

        $this->assertTrue($result);
    }

    /**
     * @dataProvider provideGateways
     *
     * @param GatewayModel $gateway
     */
    public function testDeleteRecord(GatewayModel $gateway)
    {
        $commerce = Craft::$app->getPlugins()->getPlugin('commerce');
        $commerce->getGateways()->expects($this->exactly(1))
                                ->method('archiveGatewayById')
                                ->with($gateway->id);

        $this->converter->deleteRecord($gateway);
    }

    /**
     * @dataProvider provideGateways
     *
     * @param GatewayModel $gateway
     * @param array        $definition
     */
    public function testSetRecordAttributes(GatewayModel $gateway, array $definition)
    {
        $newGateway = new DummyGateway();

        $this->converter->setRecordAttributes($newGateway, $definition, []);

        $this->assertSame($gateway->name, $newGateway->name);
        $this->assertSame($gateway->handle, $newGateway->handle);
    }

    //==============================================================================================================
    //==============================================  PROVIDERS  ===================================================
    //==============================================================================================================

    /**
     * @return array
     */
    public function provideGateways()
    {
        $mockGateway = $this->getMockGateway(1);

        return [
            'valid gateway' => [
                'gateway' => $mockGateway,
                'definition' => $this->getMockGatewayDefinition($mockGateway),
            ],
        ];
    }

    //==============================================================================================================
    //================================================  HELPERS  ===================================================
    //==============================================================================================================

    /**
     * @param GatewayModel $mockGateway
     *
     * @return array
     */
    private function getMockGatewayDefinition(GatewayModel $mockGateway)
    {
        return [
            'class' => get_class($mockGateway),
            'attributes' => [
                'name' => 'gatewayName'.$mockGateway->id,
                'handle' => 'gatewayHandle'.$mockGateway->id,
                'paymentType' => null,
                'isFrontendEnabled' => null,
                'sendCartInfo' => null,
                'isArchived' => null,
                'dateArchived' => null,
                'sortOrder' => null,
            ],
        ];
    }

    /**
     * @param int $gatewayId
     *
     * @return Mock|GatewayModel
     */
    private function getMockGateway(int $gatewayId)
    {
        $mockGateway = $this->getMockBuilder(GatewayModel::class)
                           ->disableOriginalConstructor()
                           ->getMock();

        $this->setMockGatewayAttributes($mockGateway, $gatewayId);

        return $mockGateway;
    }

    /**
     * @param Mock|GatewayModel $mockGateway
     * @param int               $gatewayId
     */
    private function setMockGatewayAttributes(GatewayModel &$mockGateway, int $gatewayId)
    {
        $mockGateway->id = $gatewayId;
        $mockGateway->handle = 'gatewayHandle'.$gatewayId;
        $mockGateway->name = 'gatewayName'.$gatewayId;
    }
}

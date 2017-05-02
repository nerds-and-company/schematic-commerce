<?php

namespace NerdsAndCompany\Schematic\Commerce\Services;

use Craft\BaseTest;
use Craft\Commerce_OrderStatusModel;
use Craft\Commerce_OrderStatusesService;
use Craft\Commerce_EmailsService;
use Craft\Craft;
use Craft\DbCommand;
use Craft\DbConnection;
use NerdsAndCompany\Schematic\Models\Result;
use PHPUnit_Framework_MockObject_MockObject as Mock;

/**
 * Class OrderStatusesTest.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2017, Nerds & Company
 * @license   MIT
 *
 * @see      http://www.nerds.company
 *
 * @coversDefaultClass \NerdsAndCompany\Schematic\Commerce\Services\OrderStatuses
 * @covers ::__construct
 * @covers ::<!public>
 */
class OrderStatusesTest extends BaseTest
{
    //==============================================================================================================
    //=================================================  TESTS  ====================================================
    //==============================================================================================================

    /**
     * @covers ::export
     * @dataProvider provideValidOrderStatuses
     *
     * @param OrderStatusModel[] $types
     * @param array              $expectedResult
     */
    public function testSuccessfulExport(array $types, array $expectedResult = [])
    {
        $schematicOrderStatusesService = new OrderStatuses();

        $actualResult = $schematicOrderStatusesService->export($types);

        $this->assertSame($expectedResult, $actualResult);
    }

    /**
     * @covers ::import
     * @dataProvider provideValidOrderStatusDefinitions
     *
     * @param array $statusDefinitions
     */
    public function testSuccessfulImport(array $statusDefinitions)
    {
        $this->setMockOrderStatusesService();
        $this->setMockEmailsService();
        $this->setMockDbConnection();

        $schematicOrderStatusesService = new OrderStatuses();

        $import = $schematicOrderStatusesService->import($statusDefinitions);

        $this->assertInstanceOf(Result::class, $import);
        $this->assertFalse($import->hasErrors());
    }

    /**
     * @covers ::import
     * @dataProvider provideValidOrderStatusDefinitions
     *
     * @param array $statusDefinitions
     */
    public function testImportWithForceOption(array $statusDefinitions)
    {
        $this->setMockOrderStatusesService();
        $this->setMockEmailsService();
        $this->setMockDbConnection();

        $schematicOrderStatusesService = new OrderStatuses();

        $import = $schematicOrderStatusesService->import($statusDefinitions, true);

        $this->assertInstanceOf(Result::class, $import);
        $this->assertFalse($import->hasErrors());
    }

    //==============================================================================================================
    //==============================================  PROVIDERS  ===================================================
    //==============================================================================================================

    /**
     * @return array
     */
    public function provideValidOrderStatuses()
    {
        return [
            'single status' => [
                'OrderStatuses' => [
                    'status1' => $this->getMockOrderStatus(1),
                ],
                'expectedResult' => [
                    'statusHandle1' => [
                        'name' => 'statusName1',
                        'color' => null,
                        'sortOrder' => null,
                        'default' => null,
                        'emails' => [],
                    ],
                ],
            ],
            'multiple statuses' => [
                'OrderStatuses' => [
                    'status1' => $this->getMockOrderStatus(1),
                    'status2' => $this->getMockOrderStatus(2),
                ],
                'expectedResult' => [
                    'statusHandle1' => [
                        'name' => 'statusName1',
                        'color' => null,
                        'sortOrder' => null,
                        'default' => null,
                        'emails' => [],
                    ],
                    'statusHandle2' => [
                        'name' => 'statusName2',
                        'color' => null,
                        'sortOrder' => null,
                        'default' => null,
                        'emails' => [],
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    public function provideValidOrderStatusDefinitions()
    {
        return [
            'emptyArray' => [
                'statusDefinitions' => [],
            ],
            'single type' => [
                'statusDefinitions' => [
                    'statusHandle1' => [
                        'name' => 'statusName1',
                        'color' => null,
                        'sortOrder' => null,
                        'default' => null,
                        'emails' => [],
                    ],
                ],
            ],
        ];
    }

    //==============================================================================================================
    //=================================================  MOCKS  ====================================================
    //==============================================================================================================

    /**
     * @param string $statusId
     *
     * @return Mock|Commerce_OrderStatusModel
     */
    private function getMockOrderStatus($statusId)
    {
        $mockOrderStatus = $this->getMockBuilder(Commerce_OrderStatusModel::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockOrderStatus->expects($this->any())
            ->method('__get')
            ->willReturnMap([
                ['id', $statusId],
                ['handle', 'statusHandle'.$statusId],
                ['name', 'statusName'.$statusId],
            ]);

        $mockOrderStatus->expects($this->any())
            ->method('getAllErrors')
            ->willReturn([
                'ohnoes' => 'horrible error',
            ]);

        $mockOrderStatus->expects($this->any())
            ->method('getEmails')
            ->willReturn([]);

        return $mockOrderStatus;
    }

    /**
     * @return Mock|Commerce_OrderStatusesService
     */
    private function setMockOrderStatusesService()
    {
        $mockOrderStatusesService = $this->getMockBuilder(Commerce_OrderStatusesService::class)
            ->disableOriginalConstructor()
            ->setMethods(['getAllOrderStatuses', 'saveOrderStatus', 'deleteOrderStatusById'])
            ->getMock();

        $mockOrderStatusesService->expects($this->any())
            ->method('getAllOrderStatuses')
            ->willReturn([]);

        $this->setComponent(Craft::app(), 'commerce_orderStatuses', $mockOrderStatusesService);

        return $mockOrderStatusesService;
    }

    /**
     * @return Mock|Commerce_EmailsService
     */
    private function setMockEmailsService()
    {
        $mockEmailsService = $this->getMockBuilder(Commerce_EmailsService::class)
            ->disableOriginalConstructor()
            ->setMethods(['getAllEmails', 'saveEmail', 'deleteEmailById'])
            ->getMock();

        $mockEmailsService->expects($this->any())
            ->method('getAllEmails')
            ->willReturn([]);

        $this->setComponent(Craft::app(), 'commerce_emails', $mockEmailsService);

        return $mockEmailsService;
    }

    /**
     * @return Mock|DbConnection
     */
    private function setMockDbConnection()
    {
        $mockDbConnection = $this->getMockBuilder(DbConnection::class)
            ->disableOriginalConstructor()
            ->setMethods(['createCommand'])
            ->getMock();
        $mockDbConnection->autoConnect = false; // Do not auto connect

        $mockDbCommand = $this->getMockDbCommand();
        $mockDbConnection->expects($this->any())->method('createCommand')->willReturn($mockDbCommand);

        Craft::app()->setComponent('db', $mockDbConnection);

        return $mockDbConnection;
    }

    /**
     * @return Mock|DbCommand
     */
    private function getMockDbCommand()
    {
        $mockDbCommand = $this->getMockBuilder(DbCommand::class)
            ->disableOriginalConstructor()
            ->setMethods(['insertOrUpdate'])
            ->getMock();

        return $mockDbCommand;
    }
}

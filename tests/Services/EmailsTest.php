<?php

namespace NerdsAndCompany\Schematic\Commerce\Services;

use Craft\BaseTest;
use Craft\Commerce_EmailModel;
use Craft\Commerce_EmailsService;
use Craft\Craft;
use Craft\DbCommand;
use Craft\DbConnection;
use NerdsAndCompany\Schematic\Models\Result;
use PHPUnit_Framework_MockObject_MockObject as Mock;

/**
 * Class EmailsTest.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2017, Nerds & Company
 * @license   MIT
 *
 * @see      http://www.nerds.company
 *
 * @coversDefaultClass \NerdsAndCompany\Schematic\Commerce\Services\Emails
 * @covers ::__construct
 * @covers ::<!public>
 */
class EmailsTest extends BaseTest
{
    //==============================================================================================================
    //=================================================  TESTS  ====================================================
    //==============================================================================================================

    /**
     * @covers ::export
     * @dataProvider provideValidEmails
     *
     * @param EmailModel[] $emails
     * @param array        $expectedResult
     */
    public function testSuccessfulExport(array $emails, array $expectedResult = [])
    {
        $schematicEmailsService = new Emails();

        $actualResult = $schematicEmailsService->export($emails);

        $this->assertSame($expectedResult, $actualResult);
    }

    /**
     * @covers ::import
     * @dataProvider provideValidEmailDefinitions
     *
     * @param array $emailDefinitions
     */
    public function testSuccessfulImport(array $emailDefinitions)
    {
        $this->setMockEmailsService();
        $this->setMockDbConnection();

        $schematicEmailsService = new Emails();

        $import = $schematicEmailsService->import($emailDefinitions);

        $this->assertInstanceOf(Result::class, $import);
        $this->assertFalse($import->hasErrors());
    }

    /**
     * @covers ::import
     * @dataProvider provideValidEmailDefinitions
     *
     * @param array $emailDefinitions
     */
    public function testImportWithForceOption(array $emailDefinitions)
    {
        $this->setMockEmailsService();
        $this->setMockDbConnection();

        $schematicEmailsService = new Emails();

        $import = $schematicEmailsService->import($emailDefinitions, true);

        $this->assertInstanceOf(Result::class, $import);
        $this->assertFalse($import->hasErrors());
    }

    //==============================================================================================================
    //==============================================  PROVIDERS  ===================================================
    //==============================================================================================================

    /**
     * @return array
     */
    public function provideValidEmails()
    {
        return [
            'single email' => [
                'Emails' => [
                    'email1' => $this->getMockEmail(1),
                ],
                'expectedResult' => [
                    'emailName1' => [
                        'name' => 'emailName1',
                        'subject' => null,
                        'recipientType' => null,
                        'to' => null,
                        'bcc' => null,
                        'enabled' => null,
                        'templatePath' => null,
                    ],
                ],
            ],
            'multiple emails' => [
                'Emails' => [
                    'email1' => $this->getMockEmail(1),
                    'email2' => $this->getMockEmail(2),
                ],
                'expectedResult' => [
                    'emailName1' => [
                        'name' => 'emailName1',
                        'subject' => null,
                        'recipientType' => null,
                        'to' => null,
                        'bcc' => null,
                        'enabled' => null,
                        'templatePath' => null,
                    ],
                    'emailName2' => [
                        'name' => 'emailName2',
                        'subject' => null,
                        'recipientType' => null,
                        'to' => null,
                        'bcc' => null,
                        'enabled' => null,
                        'templatePath' => null,
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    public function provideValidEmailDefinitions()
    {
        return [
            'emptyArray' => [
                'emailDefinitions' => [],
            ],
            'single email' => [
                'emailDefinitions' => [
                    'emailName1' => [
                        'name' => 'emailName1',
                        'subject' => null,
                        'recipientType' => null,
                        'to' => null,
                        'bcc' => null,
                        'enabled' => null,
                        'templatePath' => null,
                    ],
                ],
            ],
        ];
    }

    //==============================================================================================================
    //=================================================  MOCKS  ====================================================
    //==============================================================================================================

    /**
     * @param string $emailId
     *
     * @return Mock|EmailModel
     */
    private function getMockEmail($emailId)
    {
        $mockEmail = $this->getMockBuilder(Commerce_EmailModel::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockEmail->expects($this->any())
            ->method('__get')
            ->willReturnMap([
                ['id', $emailId],
                ['name', 'emailName'.$emailId],
            ]);

        $mockEmail->expects($this->any())
            ->method('getAllErrors')
            ->willReturn([
                'ohnoes' => 'horrible error',
            ]);

        return $mockEmail;
    }

    /**
     * @return Mock|CategoriesService
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

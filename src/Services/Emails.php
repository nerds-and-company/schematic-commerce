<?php

namespace NerdsAndCompany\Schematic\Commerce\Services;

use Craft\Craft;
use Craft\Commerce_EmailModel;
use NerdsAndCompany\Schematic\Services\Base;

/**
 * Schematic Commerce Emails Service.
 *
 * Sync Craft Setups.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2017, Nerds & Company
 * @license   MIT
 *
 * @see      http://www.nerds.company
 */
class Emails extends Base
{
    /**
     * Export emails.
     *
     * @param EmailModel[] $emails
     *
     * @return array
     */
    public function export(array $emails = [])
    {
        if (!count($emails)) {
            $emails = Craft::app()->commerce_emails->getAllEmails();
        }

        Craft::log(Craft::t('Exporting Commerce Emails'));

        $emailDefinitions = [];

        foreach ($emails as $email) {
            $emailDefinitions[$email->name] = $this->getEmailDefinition($email);
        }

        return $emailDefinitions;
    }

    /**
     * Get emails definition.
     *
     * @param Commerce_EmailModel $email
     *
     * @return array
     */
    private function getEmailDefinition(Commerce_EmailModel $email)
    {
        return [
            'name' => $email->name,
            'subject' => $email->subject,
            'recipientType' => $email->recipientType,
            'to' => $email->to,
            'bcc' => $email->bcc,
            'enabled' => $email->enabled,
            'templatePath' => $email->templatePath,
        ];
    }

    /**
     * Attempt to import emails.
     *
     * @param array $emailDefinitions
     * @param bool  $force            If set to true emails not included in the import will be deleted
     *
     * @return Result
     */
    public function import(array $emailDefinitions, $force = false)
    {
        Craft::log(Craft::t('Importing Commerce Emails'));

        $emails = array();
        foreach (Craft::app()->commerce_emails->getAllEmails() as $email) {
            $emails[$email->name] = $email;
        }

        foreach ($emailDefinitions as $emailHandle => $emailDefinition) {
            $email = array_key_exists($emailHandle, $emails)
                ? $emails[$emailHandle]
                : new Commerce_EmailModel();

            unset($emails[$emailHandle]);

            $this->populateEmail($email, $emailDefinition, $emailHandle);

            if (!Craft::app()->commerce_emails->saveEmail($email)) { // Save email via craft
                $this->addErrors($email->getAllErrors());

                continue;
            }
        }

        if ($force) {
            foreach ($emails as $email) {
                Craft::app()->commerce_emails->deleteEmailById($email->id);
            }
        }

        return $this->getResultModel();
    }

    /**
     * Populate email.
     *
     * @param Commerce_EmailModel $email
     * @param array               $emailDefinition
     * @param string              $emailHandle
     */
    private function populateEmail(Commerce_EmailModel $email, array $emailDefinition, $emailHandle)
    {
        $email->setAttributes([
            'name' => $emailHandle,
            'subject' => $emailDefinition['subject'],
            'recipientType' => $emailDefinition['recipientType'],
            'to' => $emailDefinition['to'],
            'bcc' => $emailDefinition['bcc'],
            'enabled' => $emailDefinition['enabled'],
            'templatePath' => $emailDefinition['templatePath'],
        ]);
    }
}

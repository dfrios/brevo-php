<?php


/**
 * Declare interface
 */
interface CrmInterface {

  public function __construct(string $baseUrl, string $apiKey);
  public function sendEmail(int $templateId, array $data = array());
  public function createContact(array $data = array(), int $listId);
  public function getContact(string $email) : int;
  public function addContactToList(string $email, int $listId) : bool;
}
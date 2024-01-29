<?php


/**
 * Declare interface
 */
interface CrmInterface {

  public function __construct(string $baseUrl, string $apiKey);
  public function sendEmail(int|string $templateId, array $data = array());
  public function createContact(array $data = array(), int|string $listId);
  public function getContact(string $email) : int|string;
  public function addContactToList(string $email, int|string $listId) : bool;
}
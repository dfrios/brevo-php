<?php

require(__DIR__ . '/CrmInterface.php');


/**
 * Class to connect to Brevo API
 */
class Brevo implements CrmInterface {

  private $apiKey = "";
  private $baseUrl = "";


  /**
   * Constructor
   */
  public function __construct(string $baseUrl, string $apiKey) {
    $this->baseUrl = $baseUrl;
    $this->apiKey = $apiKey;
  }


  /**
   * Send email using template created and stored on Brevo platform
   */
  public function sendEmail(int|string $templateId, array $data = array()) {

    $dataToSend = [
      'to' => [
        [
          'email' => $data['EMAIL'],
          'name' => $data['FIRSTNAME'],
        ]
      ],
      'templateId' => $templateId,
      'params' => [
        'FIRSTNAME' => $data['FIRSTNAME'],
      ],
    ];

    $response = $this->curlExec('smtp/email', 'POST', $dataToSend);
  }


  /**
   * Add new contact on Brevo platform
   */
  public function createContact(array $data = array(), int|string $listId) {

    $contactId = $this->getContact($data['EMAIL']);

    if ($contactId == 0) {
      // Contact does not exist
      // echo "Contacto no existe\n";
      $contactId = $this->addContact($data, $listId);
    }
    else {
      // Contact already exists
      // $this->addContactToList($data['email'], $listId);
      // echo "Contacto ya existe\n";
      $this->updateContact($data, $contactId, $listId);
    }

    return $contactId;
  }


  /**
   * Add contact. Private method
   */
  private function addContact(array $data = array(), int $listId) : int|string {

    $data = array(
      'email' => $data['EMAIL'],
      'emailBlacklisted' => false,
      'smsBlacklisted' => false,
      'updateEnabled' => true,
      'listIds' => array($listId),
      'attributes' => $data['attributes']
    );

    $response = $this->curlExec('contacts', "POST", $data);
    $responseJson = json_decode($response);

    if (isset($responseJson->code)) {
      if ($responseJson->code == "duplicate_parameter")
        return "duplicated data";
    }
    else {
      return $responseJson->id;
    }
  }


  /**
   * Update contact. Private method
   */
  private function updateContact(array $data = array(), int $contactId, int $listId) {

    $attributes = $data['attributes'];
    $attributes['EMAIL'] = $data['EMAIL'];

    $data = array(
      'email' => $data['EMAIL'],
      'emailBlacklisted' => false,
      'smsBlacklisted' => false,
      'updateEnabled' => true,
      'listIds' => array($listId),
      'attributes' => $attributes
    );

    $response = $this->curlExec('contacts/' . $contactId, "PUT", $data);
    $responseJson = json_decode($response);

    if (isset($responseJson->code)) {
      if ($responseJson->code == "duplicate_parameter")
        return "duplicated data";
    }
    else {
      return $responseJson->id;
    }
  }


  /**
   * Get contact on Brevo platform
   */
  public function getContact(string $email) : int|string {

    $response = $this->curlExec("contacts/{$email}");
    $responseJson = json_decode($response);

    $contactId = 0;
    if (isset($responseJson->id))
      $contactId = $responseJson->id;

    return $contactId;
  }


  public function addContactToList(string $email, int|string $listId) : bool {

    $data = array(
      'emails' => array($email)
      );

    $response = $this->curlExec("contacts/lists/{$listId}/contacts/add", "POST", $data);
    $responseJson = json_decode($response);

    return (isset($responseJson->code)) ? false : true;
  }


  /**
   * Execute cURL petition
   */
  private function curlExec(string $req, string $proto = 'GET', array $postVars = array()) {

    $curl = curl_init($this->baseUrl . "/" . $req);

    curl_setopt($curl, CURLOPT_HTTPHEADER, array(
      'Content-Type: application/json',
      'api-key: ' . $this->apiKey
    ));

    if ($proto == 'POST') {
      curl_setopt($curl, CURLOPT_POST, TRUE);
      curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($postVars, JSON_PRETTY_PRINT));
    }

    if ($proto == 'GET') {
    }

    if ($proto == 'PUT') {
      curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
      curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($postVars, JSON_PRETTY_PRINT));
    }

    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_VERBOSE, true);
// var_dump(curl_getinfo($curl));
    $salida = curl_exec($curl);
    curl_close($curl);
// var_dump($salida);die();
    // Almacenamiento de logs
    file_put_contents('brevo.log.txt',
        '[' . date(DATE_RFC2822) . "]\n" .
        json_encode($postVars, JSON_PRETTY_PRINT) . "\n\n" .
        $salida . "\n\n================\n\n",
      FILE_APPEND);

    return $salida;
  }
}

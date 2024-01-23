<?php


/**
 * Class to connect to Brevo API
 */
class Brevo {

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
  public function sendEmail(int $templateId, array $data = array()) {

    $dataToSend = [
      'to' => [
        [
          'email' => $data['email'],
          'name' => $data['firstname'],
        ]
      ],
      'templateId' => $templateId,
      'params' => [
        'FIRSTNAME' => $data['firstname'],
      ],
    ];

    $response = $this->curlExec('smtp/email', 'POST', $dataToSend);
  }


  /**
   * Add new contact on Brevo platform
   */
  public function createContact(array $data = array(), int $listId) {

    $contactId = $this->getContact($data['email']);

    if ($contactId == 0) {
      // Contact does not exist
      $contactId = $this->addContact($data, $listId);
    }
    else {
      // Contact already exists
      $this->addContactToList($data['email'], $listId);
    }

    return $contactId;
  }


  /**
   * Add contact. Private method
   */
  private function addContact(array $data = array(), int $listId) {

    $data = array(
      'email' => $data['email'],
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
   * Get contact on Brevo platform
   */
  public function getContact(string $email) : int {

    $response = $this->curlExec("contacts/{$email}");
    $responseJson = json_decode($response);

    $contactId = 0;
    if (isset($responseJson->id))
      $contactId = $responseJson->id;
  
    return $contactId;
  }


  public function addContactToList(string $email, int $listId) : bool {

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
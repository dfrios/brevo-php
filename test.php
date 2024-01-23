<?php

require('Brevo.php');
require('config.php');

$brevo = new Brevo(BREVO_URL, BREVO_APIKEY);


// Get contact
// $email = 'david@davidrios.me';
// echo "getContact:";
// var_dump($brevo->getContact($email));
// echo "<hr/>\n\n";

// Create new contact
$data = array(
  'email' => 'david@davidriossss.me',
  'attributes' => array(
    'FIRSTNAME' => 'David',
    'LASTNAME' => 'Ríos',
    'NUMERO_IDENTIFICACION' => '42828318',
    'PAIS'=> 'Colombia',
    'CIUDAD' => 'Sabaneta',
    'DIRECCION' => 'Cra 34#81 a sur 37',
    'SMS' => '573003255451',
    'ANO_NACIMIENTO' => '1985',
    'MES_NACIMIENTO' => '5',
    'DIA_NACIMIENTO' => '7',
    'EPS' => 'Sura',
    'PACIENTE_CANCER' => 'Si',
    'TIPO_CANCER' => 'Unilateral - Bilateral',
    'TRATAMIENTO_CANCER' => 'Quimioterapia',
    'ANO_DIAGNOSTICO' => '2021',
    'MES_DIAGNOSTICO' => '10',
    'DIA_DIAGNOSTICO' => '4',
    'COMO_CONOCIO_ALMAROSA' => 'Ninguno|',
    'COMO_CONOCIO_MASVIVA' => 'Redes sociales',
  )
);
echo "createContact:";
var_dump($brevo->createContact($data, BREVO_LIST));
echo "<hr/>\n\n";


// Add contact to list
// $email = 'david@davidrios.me';
// echo "addContactToList:";
// var_dump($brevo->addContactToList($email, BREVO_LIST));
// echo "<hr/>\n\n";
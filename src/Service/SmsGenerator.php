<?php
// src/Service/MessageGenerator.php
namespace App\Service;




use Twilio\Rest\Client;

class SmsGenerator
{
    
    public function SendSms(string $number)
{
    $accountSid = $_ENV['twilio_account_sid'];  //Identifiant du compte twilio
    $authToken = $_ENV['twilio_auth_token']; //Token d'authentification
    $fromNumber = $_ENV['twilio_from_number']; // Numéro de test d'envoie sms offert par twilio

    $toNumber = $number; // Le numéro de la personne qui reçoit le message

    // Generate a random verification code
    $verificationCode = rand(100000, 999999);

    $message = 'Your verification code is: ' . $verificationCode; //Contruction du sms

    $client = new Client($accountSid, $authToken);
  

    $client->messages->create(
        $toNumber,
        [
            'from' => $fromNumber,
            'body' => $message,
        ]
    );

    // Return the verification code so it can be stored and checked later
    return $verificationCode;
}
}
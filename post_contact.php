<?php

session_start();
$errors = [];

// PHPMailer settings
use Exception as GlobalException;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

// Recaptcha settigns
$secret = "6LctDXAdAAAAAOd7ZUtkZEDBSx6NxMozs8F91WwY";
$response = htmlspecialchars($_POST['g-recaptcha-response']);
$remoteip = $_SERVER['REMOTE_ADDR'];
$request = "https://www.google.com/recaptcha/api/siteverify?secret=$secret&response=$response&remoteip=$remoteip";

$get = file_get_contents($request);
$decode = json_decode($get, true);

// Check if all imput from the form is correct
if (!isset($_POST['Pseudo']) || $_POST['Pseudo'] == ""){

    $errors['Pseudo'] = "Vous n'avez pas renseigné votre pseudo.";
}
if (!isset($_POST['Sujet']) || $_POST['Sujet'] == ""){

    $errors['Sujet'] = "Vous n'avez pas renseigné de sujet.";
}
if (!isset($_POST['Email']) || $_POST['Email'] == "" || !filter_var($_POST['Email'], FILTER_VALIDATE_EMAIL)){

    $errors['Email'] = "Vous n'avez pas renseigné d'email valide.";
}
if (!isset($_POST['Message']) || $_POST['Message'] == ""){

    $errors['Message'] = "Vous n'avez pas renseigné de message.";
}
// Recpatcha check
if (!isset($decode['success']) || $decode['success'] == false){

    $errors['Recaptcha'] = "Recaptcha invalide.";
}
// Check if there is no error
if (!empty($errors)){

    $_SESSION['errors'] = $errors;
    $_SESSION['inputs'] = $_POST;
    unset($_SESSION['success']);

// Send mail if there is no error
} else {

    $mail = new PHPMailer(true);

    // SMTP Server configuration
    $mail->SMTPDebug = 2;
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'F1PitWall.official@gmail.com';
    $mail->Password = 'luc2587412369';
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;

    // Recipient
    $mail->setFrom($_POST['Email'], $_POST['Pseudo']);
    $mail->addAddress('F1PitWall.official@gmail.com', $_POST['Pseudo']);
    $mail->addReplyTo($_POST['Email'], $_POST['Pseudo']);

    // Content
    $mail->CharSet = 'UTF-8';
    $mail->isHTML(true);
    $mail->Subject = $_POST['Sujet'];
    $mail->Body = "<h2>Nouveau message</h2>
                   <hr>
                   <h3>De: $_POST[Pseudo]<br>
                   Email: $_POST[Email]</h3>
                   <hr>
                   $_POST[Message]";
    // Send mail
    $mail->send();

    // Succeed
    $_SESSION['success'] = 1;
}
header('Location: index.php#contact-anchor');
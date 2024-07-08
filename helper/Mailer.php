<?php
namespace helper;
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Mailer
{
    public function sendEmail($username, $email, $full_name)
    {
        // Cargar configuración
        $config = include('config.php');

        $validation_link = "http://localhost/index.php?controller=user&action=validate&username=$username";

        // Enviar el correo de validación
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host = $config['smtp']['host'];
            $mail->SMTPAuth = true;
            $mail->Username = $config['smtp']['username'];
            $mail->Password = $config['smtp']['password'];
            $mail->SMTPSecure = $config['smtp']['secure'];
            $mail->Port = $config['smtp']['port'];

            // Configuración del correo
            $mail->setFrom($config['from_email'], $config['from_name']);
            $mail->addAddress($email, $full_name);

            $mail->isHTML(true);
            $mail->Subject = 'Validación de cuenta';
            $mail->Body = "Gracias por registrarte. Por favor, verifica tu cuenta haciendo clic en el siguiente enlace: <a href='$validation_link'>$validation_link</a>";

            $mail->send();
            echo "Registro exitoso. Por favor, verifica tu cuenta usando el enlace enviado a tu correo.";
        } catch (Exception $e) {
            echo "El correo de validación no pudo ser enviado. Error de PHPMailer: {$mail->ErrorInfo}";
        }
    }
}

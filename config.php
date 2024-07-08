<?php
return [
    'smtp' => [
        'host' => 'smtp.gmail.com',  // Cambia esto al servidor SMTP que estés usando
        'username' => 'santi.woscoff@gmail.com',  // Tu correo electrónico
        'password' => 'pmmjrwxzvifddehj',  // Tu contraseña de correo (mejor usar una contraseña de aplicación para mayor seguridad)
        'secure' => 'tls',  // o 'ssl'
        'port' => 587  // o 465 para 'ssl'
    ],
    'from_email' => 'santi.woscoff@gmail.com',  // El correo desde el cual se enviarán los correos
    'from_name' => 'Quiz!'  // Nombre que aparecerá como remitente
];

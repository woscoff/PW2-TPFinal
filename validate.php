<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (isset($_GET['username'])) {
    $username = $_GET['username'];
    echo "Recibido el username: " . htmlspecialchars($username) . "<br>";

    $conn = new mysqli('localhost', 'root', '', 'quiz');
    if ($conn->connect_error) {
        die("Conexión fallida: " . $conn->connect_error);
    }
    echo "Conexión a la base de datos establecida.<br>";

    $stmt = $conn->prepare("SELECT is_verified FROM users WHERE username = ?");
    if (!$stmt) {
        die("Error en la preparación de la consulta: " . $conn->error);
    }
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        echo "Usuario encontrado.<br>";
        $stmt->close();

        $stmt = $conn->prepare("UPDATE users SET is_verified = 1 WHERE username = ?");
        if (!$stmt) {
            die("Error en la preparación de la consulta de actualización: " . $conn->error);
        }
        $stmt->bind_param("s", $username);

        if ($stmt->execute()) {
            echo "Cuenta verificada con éxito.";
        } else {
            echo "Error al verificar la cuenta: " . $stmt->error;
        }
    } else {
        echo "Usuario no encontrado.";
    }

    $stmt->close();
    $conn->close();
} else {
    echo "Solicitud inválida.";
}
?>

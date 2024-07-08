<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Iniciar Sesión</title>
</head>
<body>
<h2>Iniciar Sesión</h2>
<form action="login.php" method="post">
    <label for="username">Nombre de Usuario:</label>
    <input type="text" id="username" name="username" required><br>

    <label for="password">Contraseña:</label>
    <input type="password" id="password" name="password" required><br>

    <input type="submit" name="login" value="Iniciar Sesión">
</form>
</body>
</html>

<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $conn = new mysqli('localhost', 'root', '', 'quiz');

    if ($conn->connect_error) {
        die("Conexión fallida: " . $conn->connect_error);
    }

    // Modifica la consulta para incluir los campos 'administrator' y 'editor'
    $stmt = $conn->prepare("SELECT password, is_verified, administrator, editor FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($hashed_password, $is_verified, $administrator, $editor);
        $stmt->fetch();

        if (password_verify($password, $hashed_password)) {
            if ($is_verified) {
                // Establece la información de sesión
                $_SESSION['username'] = $username;
                $_SESSION['administrator'] = $administrator;
                $_SESSION['editor'] = $editor;

                // Redirecciona según el tipo de usuario
                if ($administrator == 1) {
                    header("Location: admin_dashboard.php"); // Página para administradores
                } else if ($editor == 1) {
                    header("Location: editor_dashboard.php"); // Página para editores
                } else {
                    header("Location: lobby.php"); // Página para usuarios normales
                }
                exit();
            } else {
                echo "Cuenta no verificada. Por favor, verifica tu cuenta.";
            }
        } else {
            echo "Contraseña incorrecta.";
        }
    } else {
        echo "Nombre de usuario no encontrado.";
    }

    $stmt->close();
    $conn->close();
}
?>

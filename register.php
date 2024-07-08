<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Registro</title>
</head>
<body>
<h2>Registro</h2>
<form action="register.php" method="post" enctype="multipart/form-data">
    <label for="full_name">Nombre Completo:</label>
    <input type="text" id="full_name" name="full_name" required><br>

    <label for="birth_year">Año de Nacimiento:</label>
    <input type="number" id="birth_year" name="birth_year" required><br>

    <label for="gender">Sexo:</label>
    <select id="gender" name="gender" required>
        <option value="Masculino">Masculino</option>
        <option value="Femenino">Femenino</option>
        <option value="Prefiero no cargarlo">Prefiero no cargarlo</option>
    </select><br>

    <label for="country">País:</label>
    <input type="text" id="country" name="country" required><br>

    <label for="city">Ciudad:</label>
    <input type="text" id="city" name="city" required><br>

    <label for="email">Email:</label>
    <input type="email" id="email" name="email" required><br>

    <label for="password">Contraseña:</label>
    <input type="password" id="password" name="password" required><br>

    <label for="confirm_password">Repetir Contraseña:</label>
    <input type="password" id="confirm_password" name="confirm_password" required><br>

    <label for="username">Nombre de Usuario:</label>
    <input type="text" id="username" name="username" required><br>

    <label for="profile_picture">Foto de Perfil:</label>
    <input type="file" id="profile_picture" name="profile_picture" required><br>

    <input type="submit" name="register" value="Registrarse">
</form>
</body>
</html>

<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name = $_POST['full_name'];
    $birth_year = $_POST['birth_year'];
    $gender = $_POST['gender'];
    $country = $_POST['country'];
    $city = $_POST['city'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $username = $_POST['username'];

    $conn = new mysqli('localhost', 'root', '', 'quiz');

    if ($conn->connect_error) {
        die("Conexión fallida: " . $conn->connect_error);
    }

    $stmt = $conn->prepare("INSERT INTO users (full_name, birth_year, gender, country, city, email, password, username, is_verified) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0)");
    $stmt->bind_param("sissssss", $full_name, $birth_year, $gender, $country, $city, $email, $password, $username);

    if ($stmt->execute()) {
        require 'Mailer.php';
        $mailer = new \helper\Mailer();
        $mailer->sendEmail($username, $email, $full_name);

        echo "Registro exitoso. Por favor, verifica tu cuenta usando el enlace enviado a tu correo.";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>

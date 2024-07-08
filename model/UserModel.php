<?php

class UserModel
{

    private $database;

    public function __construct($database)
    {
        $this->database = $database;
    }



    // Cantidad de usuarios
    public function getCantidadUsuarios() {
        $sql = "SELECT COUNT(*) AS cantidad FROM users";
        $resultado = $this->database->queryArray($sql);
        return $resultado['cantidad'];
    }

    // Cantidad de partidas jugadas
    public function getCantidadPartidasJugadas() {
        $sql = "SELECT SUM(partidasJugadas) AS cantidad FROM users";
        $resultado = $this->database->queryArray($sql);
        return $resultado['cantidad'];
    }

    // Cantidad de preguntas
    public function getCantidadPreguntas() {
        $sql = "SELECT COUNT(*) AS cantidad FROM preguntas";
        $resultado = $this->database->queryArray($sql);
        return $resultado['cantidad'];
    }

    // Cantidad de preguntas creadas
    public function getCantidadPreguntasCreadas() {
        $sql = "SELECT SUM(preguntasCreadas) AS cantidad FROM users";
        $resultado = $this->database->queryArray($sql);
        return $resultado['cantidad'];
    }

    // Cantidad de usuarios nuevos
    public function getCantidadUsuariosNuevos() {
        $sql = "SELECT COUNT(*) AS cantidad FROM users WHERE timestamp >= NOW() - INTERVAL 1 WEEK";
        $resultado = $this->database->queryArray($sql);
        return $resultado['cantidad'];
    }


    // Porcentaje de preguntas respondidas correctamente
    public function getPorcentajePreguntasCorrectas() {
        $sql = "SELECT (SUM(esCorrecta) / COUNT(*)) * 100 AS porcentaje FROM respuestas";
        $resultado = $this->database->queryArray($sql);
        return $resultado['porcentaje'];
    }

    // Cantidad de usuarios por país
    public function getCantidadUsuariosPorPais() {
        $sql = "SELECT country, COUNT(*) AS cantidad FROM users GROUP BY country";
        return $this->database->query($sql);
    }

    // Cantidad de usuarios por sexo
    public function getCantidadUsuariosPorSexo() {
        $sql = "SELECT gender, COUNT(*) AS cantidad FROM users GROUP BY gender";
        return $this->database->query($sql);
    }

    // Cantidad de usuarios por grupo de edad
    public function getCantidadUsuariosPorGrupoDeEdad() {
        $sql = "
            SELECT 
                CASE
                    WHEN TIMESTAMPDIFF(YEAR, birth_year, CURDATE()) < 18 THEN 'Menores'
                    WHEN TIMESTAMPDIFF(YEAR, birth_year, CURDATE()) >= 65 THEN 'Jubilados'
                    ELSE 'Medio'
                END AS grupo,
                COUNT(*) AS cantidad
            FROM users
            GROUP BY grupo
        ";
        return $this->database->query($sql);
    }









    public function getUsuarios()
    {
        $sql = "SELECT * FROM users ORDER BY points DESC";
        $resultado = $this->database->query($sql);

        return $resultado;
    }

    public function getUsuario()
    {
        if ($_SERVER["REQUEST_METHOD"] == "GET") {
            $username = $_GET['user'];

            $sql = "SELECT full_name, birth_year, gender, country, city, email, username, profile_picture, points, partidasJugadas, partidasGanadas, administrator, editor FROM users WHERE username = '$username'";
            $resultado = $this->database->query($sql);

            return $resultado;
        }
    }

    public function getUsuarioFromSession($username)
    {
        $sql = "SELECT full_name, birth_year, gender, country, city, email, username, profile_picture, points, administrator, editor FROM users WHERE username = '$username'";
        $resultado = $this->database->queryArray($sql);

        return $resultado;
    }

    public function loginUsuario()
    {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $username = $_POST['username'];
            $password = $_POST['password'];

            $sql = "SELECT full_name, birth_year, gender, country, city, email, password, username, profile_picture, points, is_verified, administrator, editor FROM users WHERE username = '$username'";
            $resultado = $this->database->queryArray($sql);

            if ($resultado == null || $resultado == false) {
                return "Nombre de usuario o contraseña incorrecta.";
            }

            if (password_verify($password, $resultado["password"])) {
                if ($resultado['is_verified'] == 1) {
                    $_SESSION['usuario'] = $resultado;
                    return $resultado;
                } else {
                    return "Cuenta no verificada. Por favor, verifica tu cuenta con el email que te mandamos a tu correo.";
                }
            } else {
                return "Nombre de usuario o contraseña incorrecta.";
            }
        }
    }

    public function postUsuario()
    {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $full_name = $_POST['full_name'];
            $birth_year = $_POST['birth_year'];
            $gender = $_POST['gender'];
            $country = $_POST['country'];
            $city = $_POST['city'];
            $email = $_POST['email'];
            $password = $_POST['password'];
            $confirm_password = $_POST['confirm_password'];
            $username = $_POST['username'];
            $profile_picture = $_FILES['profile_picture']['name'];
            $profile_picture_tmp = $_FILES['profile_picture']['tmp_name'];
            $points = 0;
            $partidasJugadas = 0;
            $partidasGanadas = 0;
            $is_verified = 0;
            $administrator = 0; // Asignar el rol por defecto
            $editor = 0; // Asignar el rol por defecto

            if ($password != $confirm_password) {
                die("Las contraseñas no coinciden.");
            }

            $hashed_password = password_hash($password, PASSWORD_BCRYPT);

            $upload_dir = 'public/uploads/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            $profile_picture_path = $upload_dir . basename($profile_picture);
            move_uploaded_file($profile_picture_tmp, $profile_picture_path);

            $sql = "INSERT INTO users (full_name, birth_year, gender, country, city, email, password, username, profile_picture, points, partidasJugadas, partidasGanadas, is_verified, administrator, editor) VALUES ('$full_name', '$birth_year', '$gender', '$country', '$city', '$email', '$hashed_password', '$username', '$profile_picture_path', '$points', '$partidasJugadas', '$partidasGanadas', '$is_verified', '$administrator', '$editor')";
            $resultado = $this->database->execute($sql);

            return $resultado;
        }
    }

    public function updatePointsUsuario($username, $pointsUser)
    {
        $sql = "SELECT points FROM users WHERE username = '$username'";
        $resultado = $this->database->queryArray($sql);
        $points = intval($resultado["points"]) + $pointsUser;

        $sql = "UPDATE users SET points = '$points' WHERE username = '$username'";
        $this->database->execute($sql);
    }

    public function updatePreguntasCreadasUsuario($username)
    {
        $sql = "SELECT preguntasCreadas FROM users WHERE username = '$username'";
        $resultado = $this->database->queryArray($sql);
        $preguntaCreada = intval($resultado["preguntasCreadas"]) + 1;

        $sql = "UPDATE users SET preguntasCreadas = '$preguntaCreada' WHERE username = '$username'";
        $this->database->execute($sql);
    }

    public function validate()
    {
        $resultado = $this->model->validate();
        if ($resultado) {
            echo "Cuenta verificada con éxito.";
        } else {
            echo "Solicitud inválida o error en la verificación.";
        }
    }
}

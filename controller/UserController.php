<?php

use helper\Mailer;

include_once("helper/Mailer.php");

class UserController
{

    private $model;

    private $modelJuego;

    private $presenter;

    public function __construct($model, $modelJuego, $presenter)
    {
        $this->model = $model;
        $this->modelJuego = $modelJuego;
        $this->presenter = $presenter;
    }

    public function registerUsuario()
    {
        $usuario = $this->model->postUsuario();

        $username = $_POST['username'];
        $full_name = $_POST['full_name'];
        $email = $_POST['email'];

        $mailer = new Mailer();
        $mailer->sendEmail($username, $email, $full_name);


        header("Location: /user/getViewLogin");
        exit();

    }

    public function loginUsuario()
    {
        session_start();
        $usuario = $this->model->loginUsuario();

        if (is_array($usuario)) {
            $_SESSION['usuario'] = $usuario;
            $_SESSION['username'] = $usuario['username'];
            $_SESSION['editor'] = $usuario['editor'];
            $_SESSION['administrator'] = $usuario['administrator'];
            header("Location: /");
            exit();
        } else {
            $_SESSION['msg'] = $usuario;
            header("Location: /user/getViewLogin");
            exit();
        }
    }



    public function cerrarSesion()
    {

        unset($_SESSION['usuario']);

        header("Location: /");
        exit();
    }

    public function validate()
    {
        if (isset($_GET['username'])) {
            $username = $_GET['username'];

            // Ruta al archivo config.ini dentro de la carpeta config
            $configFilePath = __DIR__ . '/../config/config.ini';
            if (!file_exists($configFilePath)) {
                die("El archivo de configuración no se encontró: " . $configFilePath);
            }

            $config = parse_ini_file($configFilePath);
            if (!$config) {
                die("Error al leer el archivo de configuración.");
            }

            $servername = $config['servername'];
            $username_db = $config['username'];
            $password_db = $config['password'];
            $dbname = $config['dbname'];
            $port = $config['port'];

            try {
                // Conexión a la base de datos utilizando el puerto especificado
                $conn = new mysqli($servername, $username_db, $password_db, $dbname, $port);
                if ($conn->connect_error) {
                    throw new Exception("Conexión fallida: " . $conn->connect_error);
                }

                $stmt = $conn->prepare("UPDATE users SET is_verified = 1 WHERE username = ?");
                if (!$stmt) {
                    throw new Exception("Error en la preparación de la consulta: " . $conn->error);
                }

                $stmt->bind_param("s", $username);

                if ($stmt->execute()) {
                    echo "Cuenta verificada con éxito. Redirigiendo al inicio de sesión...";
                    header("Refresh: 3; URL=/index.php?controller=user&action=getViewLogin");
                    exit();
                } else {
                    throw new Exception("Error al verificar la cuenta: " . $stmt->error);
                }

            } catch (Exception $e) {
                echo $e->getMessage();
            } finally {
                if (isset($stmt)) {
                    $stmt->close();
                }
                if (isset($conn)) {
                    $conn->close();
                }
            }
        } else {
            echo "Solicitud inválida.";
        }
    }





    public function getViewLogin()
    {
        $msg = isset($_SESSION['msg']) ? $_SESSION['msg'] : null;

        $this->presenter->render("view/login.mustache", ["msg" => $msg]);

        unset($_SESSION['msg']);
    }

    public function getViewMiCuenta()
    {

        if (isset($_SESSION['username'])) {
            $usuario = $this->model->getUsuario();
            $usuario['editor'] = $_SESSION['editor'];
            $usuario['administrator'] = $_SESSION['administrator'];

            $this->presenter->render("view/miCuenta.mustache", ["user" => $usuario]);
        } else {
            header("Location: /user/getViewLogin");
        }
    }


    public function jugador()
    {

        $usuario = $this->model->getUsuario();

        $this->presenter->render("view/jugador.mustache", ["user" => $usuario]);

    }

    public function getViewRegister()
    {
        $this->presenter->render("view/register.mustache");
    }

    public function ranking()
    {
        $usuarios = $this->model->getUsuarios();

        $this->presenter->render("view/ranking.mustache", ["users" => $usuarios]);
    }

    public function estadisticas() {
        $cantidadDeUsuarios = $this->model->getCantidadUsuarios();
        $cantidadDePartidas = $this->model->getCantidadPartidasJugadas();
        $cantidadDePreguntas = $this->modelJuego->getCantidadPreguntas();
        $cantidadDePreguntasCreadas = $this->model->getCantidadPreguntasCreadas();
        $usuariosNuevos = $this->model->getCantidadUsuariosNuevos();
        $porcentajeCorrectas = $this->model->getPorcentajePreguntasCorrectas();
        $usuariosPorPais = $this->model->getCantidadUsuariosPorPais();
        $usuariosPorSexo = $this->model->getCantidadUsuariosPorSexo();
        $usuariosPorGrupoDeEdad = $this->model->getCantidadUsuariosPorGrupoDeEdad();

        $this->presenter->render("view/estadisticasAdmin.mustache", [
            "cantidadDeUsuarios" => $cantidadDeUsuarios,
            "cantidadDePartidas" => $cantidadDePartidas,
            "cantidadDePreguntas" => $cantidadDePreguntas,
            "cantidadDePreguntasCreadas" => $cantidadDePreguntasCreadas,
            "usuariosNuevos" => $usuariosNuevos,
            "porcentajeCorrectas" => $porcentajeCorrectas,
            "usuariosPorPais" => $usuariosPorPais,
            "usuariosPorSexo" => $usuariosPorSexo,
            "usuariosPorGrupoDeEdad" => $usuariosPorGrupoDeEdad
        ]);
    }


}
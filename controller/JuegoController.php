<?php

class JuegoController
{

    private $model;

    private $modelUser;

    private $presenter;

    public function __construct($model, $modelUser, $presenter)
    {
        $this->model = $model;
        $this->modelUser = $modelUser;
        $this->presenter = $presenter;
    }

    public function desafiar()
    {
        $desafiador = $_SESSION['usuario']['username'];
        $oponente = $_GET['user'];

        // Generar respuestas correctas aleatorias para el bot (máximo 5)
        $respuestasBot = rand(0, 5);

        // Guardar el desafío y las respuestas del bot en la sesión
        $_SESSION['desafio'] = [
            'desafiador' => $desafiador,
            'oponente' => $oponente,
            'respuestasBot' => $respuestasBot
        ];

        $this->presenter->render("view/main.mustache", ["desafio" => $_SESSION['desafio'], "user" => $_SESSION['usuario']]);
    }



    public function iniciarPartida()
    {
        $this->presenter->render("view/partida.mustache", []);
    }

    public function partida()
    {
        if ($_SESSION["partida"] === NULL) {
            $_SESSION["partida"] = TRUE;
            $this->model->incrementarPartidasJugadas();
            $_SESSION["Racha"] = 0;
            $this->traerPregunta();
        } else {
            $this->repetirPregunta();
        }
    }


    public function traerPregunta()
    {
        $_SESSION["IdPregunta"]= $this->model->obtenerIdPregunta();
        if ($_SESSION["IdPregunta"]==NULL){
            $datos["aviso"] = "Completaste todas las preguntas!";
            $this->presenter->render("view/main.mustache",$datos);
        }else{
            $this->model->preguntaRepetida($_SESSION["IdPregunta"]);
            $_SESSION["pregunta"] = $this->model->elegirPregunta($_SESSION["IdPregunta"]);
            $_SESSION["respuestas"] = $this->model->obtenerRespuestas($_SESSION["IdPregunta"]);
            $_SESSION["categoria"] = $this->model->obtenerCategoria($_SESSION["IdPregunta"]);

            $datos["categoria"] = $_SESSION["categoria"];
            $datos["idPregunta"]=$_SESSION["IdPregunta"];
            $datos["pregunta"]=$_SESSION["pregunta"];
            $datos["respuestas"]=$_SESSION["respuestas"];
            $datos["Racha"]=$_SESSION["Racha"];

            $this->presenter->render("view/partidaView.mustache", ['idPregunta' => $datos["idPregunta"], 'pregunta' => $datos["pregunta"], 'respuestas' => $datos["respuestas"],'Racha' =>$datos["Racha"], 'categoria' => $datos["categoria"]]);
        }}

    public function repetirPregunta()
    {
        $datos["categoria"] = $_SESSION["categoria"];
        $datos["Racha"] = $_SESSION["Racha"];
        $datos["idPregunta"] = $_SESSION["IdPregunta"];
        $datos["pregunta"] = $_SESSION["pregunta"];
        $datos["respuestas"] = $_SESSION["respuestas"];

        $this->presenter->render("view/partidaView.mustache", $datos);
    }

    public function verificarRespuesta()
    {
        $respuesta = $_POST["respuesta"];
        $valor = $this->model->verificarRespuesta($respuesta);
        if ($valor['esCorrecta'] == 1) {
            $_SESSION["Racha"]++;

            if ($_SESSION["Racha"] == 5) {
                $_SESSION["aviso"] = "¡Ganaste!";
                $this->modelUser->updatePointsUsuario($_SESSION["usuario"]["username"], 10);
                $_SESSION["partida"] = NULL;

                if (isset($_SESSION['desafio'])) {
                    $resultado = [
                        'respuestasJugador' => $_SESSION["Racha"],
                        'respuestasBot' => $_SESSION['desafio']['respuestasBot'],
                        'ganador' => $_SESSION["Racha"] > $_SESSION['desafio']['respuestasBot'] ? 'Jugador' : 'Bot',
                        'mensaje' => $_SESSION["Racha"] > $_SESSION['desafio']['respuestasBot'] ? '¡Has ganado el desafío!' : 'Has perdido el desafío.'
                    ];
                    unset($_SESSION['desafio']);
                } else {
                    $resultado = null;
                }

                $this->presenter->render("view/main.mustache", ["resultado" => $resultado, "user" => $_SESSION['usuario']]);
                exit();
            } else {
                $this->traerPregunta();
                exit();
            }
        } else {
            $racha = $_SESSION["Racha"];
            $this->modelUser->updatePointsUsuario($_SESSION["usuario"]["username"], $racha);

            $_SESSION["aviso"] = "Respuesta incorrecta";
            $_SESSION["partida"] = NULL;

            if (isset($_SESSION['desafio'])) {
                $resultado = [
                    'respuestasJugador' => $racha,
                    'respuestasBot' => $_SESSION['desafio']['respuestasBot'],
                    'ganador' => $racha > $_SESSION['desafio']['respuestasBot'] ? 'Vos' : 'Desafiado',
                    'mensaje' => $racha > $_SESSION['desafio']['respuestasBot'] ? '¡Has ganado el desafío!' : 'Has perdido el desafío.'
                ];
                unset($_SESSION['desafio']);
            } else {
                $resultado = null;
            }

            $this->presenter->render("view/main.mustache", ["resultado" => $resultado, "user" => $_SESSION['usuario'], "error" => "Respuesta incorrecta"]);
            exit();
        }
        header("location:/");
    }






    //
    public function reportar()
    {
        if ($_SERVER["REQUEST_METHOD"] == "GET") {
            $idPregunta = $_GET['id'];
            $this->model->reportarPregunta($idPregunta);
            $this->traerPregunta();
        }
    }

    public function getViewCrearPregunta()
    {
        $this->presenter->render("view/crearPregunta.mustache");
    }

    public function crearPregunta()
    {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $pregunta = $_POST['pregunta'];
            $this->model->crearPregunta($pregunta);
            $this->modelUser->updatePreguntasCreadasUsuario($_SESSION["usuario"]["username"]);
            $_SESSION["aviso"] = "Pregunta creada";
            header("location:/");
        }
    }

    public function getViewAdministrarPreguntas()
    {
        if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['editor'] != 1) {
            header('Location: /login');
            exit();
        }

        $preguntasReportadas = $this->model->obtenerLasPreguntasReportadas();
        $preguntasNoReportadas = $this->model->obtenerLasPreguntasNoReportadas();

        $this->presenter->render("view/administrarPregunta.mustache", ["preguntasReportadas" => $preguntasReportadas, "preguntasNoReportadas" => $preguntasNoReportadas]);
    }


    public function dejarDeReportar()
    {
        if ($_SERVER["REQUEST_METHOD"] == "GET") {
            $id = $_GET['id'];
            $this->model->eliminarReportarPregunta($id);
            header("location:/juego/getViewAdministrarPreguntas");
        }
    }

    public function eliminarPregunta()
    {
        if ($_SERVER["REQUEST_METHOD"] == "GET") {
            $id = $_GET['id'];
            $this->model->eliminarPregunta($id);
            header("location:/juego/getViewEditarPreguntas");
        }
    }

    public function aceptarPregunta()
    {
        if ($_SERVER["REQUEST_METHOD"] == "GET") {
            $id = $_GET['id'];
            $this->model->aceptarPregunta($id);
            header("location:/juego/getViewAdministrarPreguntas");
        }
    }

    public function getViewEditarPregunta()
    {
        if ($_SERVER["REQUEST_METHOD"] == "GET") {
            $id = $_GET['id'];
            $pregunta = $this->model->elegirPregunta($id);
            $opciones = $this->model->obtenerRespuestas($id);

            $opcion1="";
            $opcion2="";
            $opcion3="";
            $opcion4="";

            for ($i = 0; $i < sizeof($opciones); $i++) {
                ${"opcion" . ($i + 1)}=$opciones[$i];
            }


            $this->presenter->render("view/editarPregunta.mustache", ["pregunta" => $pregunta, "opcion1" => $opcion1, "opcion2" => $opcion2, "opcion3" => $opcion3, "opcion4" => $opcion4]);
        }

    }

    public function editarPregunta()
    {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $pregunta = $_POST['pregunta'];
            $id = $_GET['id'];

            $this->model->editarPregunta($pregunta, $id);

            header("location:/juego/getViewAdministrarPreguntas");
        }
    }


}
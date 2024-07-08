<?php

class JuegoModel
{

    private $database;

    public function __construct($database)
    {
        $this->database = $database;
    }



    public function obtenerIdPregunta()
    {
        $usuario = $_SESSION["usuario"]["username"];
        $sql = "SELECT id FROM Users WHERE username = '$usuario'";
        $consulta = $this->database->query($sql);

        foreach ($consulta as $user) {
            $idUser = $user["id"];
            $sql = "Select * from Preguntas where id not in(Select id_pregunta from Repetidas where id_usuario = '$idUser')";
            $pregunta = $this->database->query($sql);
            if ($pregunta == null){
                return false;
            }
            foreach ($pregunta as $preguntasId) {
                $array[] = $preguntasId["id"];
            }
            return $arrayPadre[] = $array[array_rand($array, 1)];
        }
    }
    public function obtenerCategoria($id){
        $sql = "SELECT c.descripcion AS categoria FROM Preguntas p JOIN categoria c ON p.id_categoria = c.id WHERE p.id = '$id'";
        return $this->database->queryArray($sql);
    }



    public function getCantidadPreguntas() {
        $sql = "SELECT COUNT(*) AS cantidad FROM preguntas";
        $resultado = $this->database->queryArray($sql);
        return $resultado['cantidad'];
    }

    public function preguntaRepetida($id)
    {
        $usuario = $_SESSION["usuario"]["username"];

        $sql = "SELECT id FROM Users WHERE username = '$usuario'";
        $consulta = $this->database->query($sql);

        foreach ($consulta as $user) {
            $idUser = $user["id"];
        }

        $sql = "INSERT INTO Repetidas (id_usuario,id_pregunta) VALUES ($idUser,$id)";
        $this->database->execute($sql);
    }

    public function elegirPregunta($id)
    {
        $sql = "SELECT * FROM Preguntas WHERE id = '$id'";
        return $this->database->query($sql);
    }

    public function obtenerRespuestas($id)
    {
        $sql = "SELECT * FROM Respuestas WHERE id_pregunta = '$id'";
        return $this->database->query($sql);
    }

    public function verificarRespuesta($texto)
    {
        $sql = "SELECT esCorrecta FROM Respuestas WHERE respuesta = '$texto'";
        return $this->database->queryArray($sql);
    }

    public function incrementarPartidasJugadas()
    {
        $usuario = $_SESSION["usuario"]["username"];
        $sql = "UPDATE Users SET partidasJugadas = partidasJugadas + 1 WHERE username = '$usuario'";
        $this->database->execute($sql);
    }

    public function incrementarPartidasGanadas()
    {
        $usuario = $_SESSION["usuario"]["username"];
        $sql = "UPDATE Users SET partidasGanadas = partidasGanadas + 1 WHERE username = '$usuario'";
        $this->database->execute($sql);
    }

    public function reportarPregunta($id)
    {
        $sql = "UPDATE Preguntas SET reportada = 1 WHERE id = '$id'";
        $this->database->execute($sql);
    }

    public function crearPregunta($pregunta)
    {
        $sql = "INSERT INTO preguntas (pregunta, reportada, aceptada) VALUES ('$pregunta', 0, 0)";
        $this->database->execute($sql);


        $idPregunta = $this->database->getLastInsertedId();


        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $option1 = $_POST['opcionText1'];
            $option2 = $_POST['opcionText2'];
            $option3 = $_POST['opcionText3'];
            $option4 = $_POST['opcionText4'];

            $allOptions = [$option1, $option2, $option3, $option4];
            $i = 0;
            foreach ($allOptions as $option) {
                ++$i;
                $number = substr($_POST['opcion'], -1);

                if (intval($number) == intval($i)) {
                    $correcta = 1;
                    $sqlRespuesta = "INSERT INTO respuestas (id_pregunta, respuesta, esCorrecta) VALUES ('$idPregunta', '$option', '$correcta')";
                    $this->database->execute($sqlRespuesta);
                } else {
                    $correcta = 0;
                    $sqlRespuesta = "INSERT INTO respuestas (id_pregunta, respuesta, esCorrecta) VALUES ('$idPregunta', '$option', '$correcta')";
                    $this->database->execute($sqlRespuesta);
                }

            }
        }
    }

    public function obtenerLasPreguntasReportadas()
    {
        $sql = "SELECT * FROM preguntas WHERE reportada = 1 ORDER BY id";
        $consulta = $this->database->query($sql);

        return $consulta;
    }

    public function obtenerLasPreguntasNoReportadas()
    {
        $sql = "SELECT * FROM preguntas WHERE reportada = 0 ORDER BY id";
        $consulta = $this->database->query($sql);

        return $consulta;
    }

    public function eliminarReportarPregunta($id)
    {
        $sql = "UPDATE preguntas SET reportada = 0 WHERE id = '$id'";
        $this->database->execute($sql);
    }

    public function eliminarPregunta($id)
    {
        $sql = "DELETE FROM repetidas WHERE id_pregunta = '$id'";
        $this->database->execute($sql);

        $sql = "DELETE FROM respuestas WHERE id_pregunta = '$id'";
        $this->database->execute($sql);

        $sql = "DELETE FROM preguntas WHERE id = '$id'";
        $this->database->execute($sql);

    }

    public function aceptarPregunta($id)
    {
        $sql = "UPDATE preguntas SET aceptada = 1 WHERE id = '$id'";
        $this->database->execute($sql);

    }

    public function editarPregunta($pregunta, $id)
    {
        $sql = "UPDATE preguntas SET pregunta = '$pregunta' WHERE id = '$id'";
        $this->database->execute($sql);

        // Fetch answer IDs related to the question ID
        $sqlRespuestaIDs = "SELECT id FROM respuestas WHERE id_pregunta = '$id'";
        $respuestaIDs = $this->database->query($sqlRespuestaIDs);

        // Process each answer
        foreach ($respuestaIDs as $respuesta) {
            $respuesta_id = $respuesta['id'];
            $optionKey = 'opcionText' . $respuesta_id; // Assuming this is how you identify each answer option

            if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST[$optionKey])) {
                $option = $_POST[$optionKey];
                $a=$_POST['opcion'];
                $correcta = ($_POST['opcion'] == $respuesta_id) ? 1 : 0; // Determine if this answer is correct based on $_POST['opcion']

                // Update the answer
                $sqlUpdateRespuesta = "UPDATE respuestas SET respuesta = '$option', esCorrecta = '$correcta' WHERE id = '$respuesta_id'";
                $this->database->execute($sqlUpdateRespuesta);
            }
        }
    }

    public function obtenerPreguntas()
    {
        $sql = "SELECT * FROM preguntas";
        return $this->database->query($sql);
    }

}
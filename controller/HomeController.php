<?php

class HomeController
{
    private $model;
    private $presenter;

    public function __construct($model, $presenter)
    {
        $this->presenter = $presenter;
        $this->model = $model;

    }

    public function home()
    {
        $aviso=isset($_SESSION["aviso"]) ? $_SESSION["aviso"] : null;

        $user=isset($_SESSION["usuario"]) ? $_SESSION["usuario"]["username"] : null;
        $usuario = $this->model->getUsuarioFromSession($user);

        $this->presenter->render("view/main.mustache", ["user" => $usuario, "aviso" => $aviso]);

        unset($_SESSION['aviso']);

//        $nombreUsuario = $_SESSION["usuario"];
//        $this->presenter->render("view/main.mustache",['nombreUsuario' => $nombreUsuario]);
    }

//    public function login(){
//        $this->presenter->render("view/login.mustache");
//    }

}
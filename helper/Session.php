<?php

namespace helper;
class Session
{

    private $session;

    public function __construct($session)
    {
        $this->session = $session;
    }

    public function isSetSessionUsuario()
    {

        $usuario = isset($this->session["usuario"]) ? $this->session["usuario"] : false;
        return $usuario;
    }

    public function setTypeOfLogin()
    {

        $usuario = $this->isSetSessionUsuario();
        $loginHtml = $usuario ? file_get_contents("view/divUser.mustache") : file_get_contents("view/divLogin.mustache");
        return $loginHtml;
    }


}
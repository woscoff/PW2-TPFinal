<?php

namespace helper;

use Mustache_Autoloader;
use Mustache_Engine;
use Mustache_Loader_FilesystemLoader;

require_once 'helper/Session.php';


class MustachePresenter
{
    private $mustache;
    private $partialsPathLoader;

    public function __construct($partialsPathLoader)
    {
        Mustache_Autoloader::register();
        $this->mustache = new Mustache_Engine(
            array(
                'partials_loader' => new Mustache_Loader_FilesystemLoader($partialsPathLoader)
            ));
        $this->partialsPathLoader = $partialsPathLoader;
    }

    public function render($contentFile, $data = array())
    {
        echo $this->generateHtml($contentFile, $data);
    }

    public function generateHtml($contentFile, $data = array())
    {
        $session = new Session($_SESSION);
        $usuario = $session->isSetSessionUsuario();
        if ($usuario) {
            $data += ["usuario" => $usuario];
        }

        $contentAsString = file_get_contents($this->partialsPathLoader . '/header.mustache');
        $contentAsString .= file_get_contents($contentFile);
        $contentAsString .= file_get_contents($this->partialsPathLoader . '/footer.mustache');
        return $this->mustache->render($contentAsString, $data);
    }
}
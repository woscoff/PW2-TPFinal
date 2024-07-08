<?php

use helper\Database;
use helper\MustachePresenter;
use helper\Router;

include_once("controller/UserController.php");
include_once("controller/HomeController.php");
include_once("controller/JuegoController.php");

include_once("model/UserModel.php");
include_once("model/JuegoModel.php");

include_once("helper/Database.php");
include_once("helper/Router.php");

include_once("helper/MustachePresenter.php");

include_once('vendor/mustache/src/Mustache/Autoloader.php');

class Configuration
{

    // CONTROLLERS
    public static function getJuegoController()
    {
        return new JuegoController(self::getJuegoModel(), self::getUserModel(), self::getPresenter());
    }
    public static function getUserController()
    {
        return new UserController(self::getUserModel(), self::getJuegoModel(), self::getPresenter());
    }

    public static function getHomeController()
    {
        return new HomeController(self::getUserModel(), self::getPresenter());
    }




    // MODELS
    public static function getJuegoModel(){
        return new JuegoModel(self::getDatabase());
    }
    private static function getUserModel()
    {
        return new UserModel(self::getDatabase());
    }


    // HELPERS
    private static function getConfig()
    {
        return parse_ini_file("config/config.ini");
    }

    public static function getDatabase()
    {
        $config = self::getConfig();
        return new Database($config["servername"], $config["username"], $config["password"], $config["dbname"], $config["port"]);
    }


    //
    public static function getRouter()
    {
        return new Router("getHomeController", "home");
    }

    private static function getPresenter()
    {
        return new MustachePresenter("view");
    }
}
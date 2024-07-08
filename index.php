<?php
session_start();
include_once("Configuration.php");

$router = Configuration::getRouter();
$router->handleRequest();


// index.php?controller=tours&action=get
// tours/get
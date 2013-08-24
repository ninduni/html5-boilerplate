<?php

// display errors, warnings, and notices
ini_set("display_errors", true);
error_reporting(E_ALL|E_STRICT); 

// enable sessions
session_start();

// Authentication requirement, delete if not necessary
/*
if (!preg_match("{(?:login|logout|register|forgotpass)\.php$}", $_SERVER["PHP_SELF"])){
	if (empty($_SESSION["id"])){
		redirect("login.php");
	}
}
*/

$config = array(  
    "db" => array(  
        "dev" => array(  
            "database" => "test",  //Edit this
            "username" => "root",  
            "password" => "",  
            "server" => "localhost"  
        ),  
        "live" => array(  
            "database" => "",  
            "username" => "",  
            "password" => "",  
            "server" => ""  
        )  
    ),  
    "urls" => array(  
        "baseUrl" => "http://example.com"  
    ),  
    "paths" => array(   
    )  
); 

//Base path
defined("BPATH")
    or define("BPATH", realpath(dirname(__FILE__)).'/../');
//Path for views
defined("VIEW_PATH")  
    or define("VIEW_PATH", BPATH . '/views/');  


// requirements
require_once("functions.php");
require_once(BPATH."inc/db.php");

//Initialize Database
$db = new sugarPDO($config['db']['dev']);

?>

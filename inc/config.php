<?php

// display errors, warnings, and notices
ini_set("display_errors", true);
error_reporting(E_ALL|E_STRCT); 

// enable sessions
session_start();

// requirements
require_once("functions.php");
require_once("dp.php");

// require authentication for most pages
if (!preg_match("{(?:login|logout|register|forgotpass)\.php$}", $_SERVER["PHP_SELF"])){
	if (empty($_SESSION["id"])){
		redirect("login.php");
	}
}

$config = array(  
    "db" => array(  
        "dev" => array(  
            "dbname" => "database",  //Edit this
            "username" => "",  
            "password" => "",  
            "host" => "localhost"  
        ),  
        "live" => array(  
            "dbname" => "",  
            "username" => "",  
            "password" => "",  
            "host" => ""  
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
    or define("BPATH", realpath(dirname(__FILE__)));
//Path for views
defined("VIEW_PATH")  
    or define("VIEW_PATH", BPATH . '/views');  

//Initialize Database
$db = new sugarPDO($config['db']['dev']);

?>

<?php
    require("../../../config/secrets.php");

    $function_files = scandir("functions");

    foreach($function_files as $function_file) {
        if($function_file !== '.' && $function_file !== '..') {
            require("functions/".$function_file);
        }
    }
    
    date_default_timezone_set('America/Los_Angeles');

    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);

    // Connect to database based on server
    if($_SERVER['SERVER_NAME'] == "localhost" || $_SERVER['SERVER_NAME'] == $server["local"]["name"]) {
        $root = "//".$_SERVER["HTTP_HOST"]."/foursquare-places-dev/foursquare-places/";

        $database_server = $database["local"]["server"];
        $database_name = $database["local"]["database-name"];
        $database_username = $database["local"]["username"];
        $database_password = $database["local"]["password"];
    } else {
        $root = "//".$_SERVER["HTTP_HOST"]."/";
        
        $database_server = $database["remote"]["server"];
        $database_name = $database["remote"]["database-name"];
        $database_username = $database["remote"]["username"];
        $database_password = $database["remote"]["password"];
    }

    $db = new PDO(
        'mysql:host='.$database_server.';dbname='.$database_name,
        $database_username,
        $database_password,
        array(
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"
        )
    );

    // these are lists that span across multiple lists – this combines them so that accessing either list shows places of all lists
	$split_list_combos = Array(
		Array(
			"parent" => "567d7b1d38fa9c91825e5c7a",
			"children" => Array("59e5a3ba8a6f1741c057072f", "5be9fdbb0a08ab002c5ca81a")
		), // San Francisco, San Francisco 2, and San Francisco 3
		Array(
			"parent" => "567e16a238fa9c9182a0b903",
			"children" => Array("5e0a930d16de620006642ad8")
		), // New York, New York 2
	);
?>
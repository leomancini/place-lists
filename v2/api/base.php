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
	
	function strip_accents($str) {
	    return strtr(utf8_decode($str), utf8_decode(
		'àáâãäāçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ'),
		'aaaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');
    }
    
	function convert($type, $direction, $old_string) {
		$new_string = $old_string;
		
		if($type == "search-suggestion") {
			$new_string = str_replace(" ", "&nbsp;", $new_string);
		} else {	
			if($direction == "url") {
				$new_string = strtolower($new_string);
				$new_string = str_replace(" ", "-", $new_string);
				$new_string = str_replace("&", "-and-", $new_string);
				$new_string = str_replace("+", "-plus-", $new_string);
				$new_string = str_replace("/", "-slash-", $new_string);
				
				$new_string = str_replace("--and--", "-and-", $new_string);
				$new_string = str_replace("--slash--", "-slash-", $new_string);
				$new_string = str_replace("--plus--", "-plus-", $new_string);
				$new_string = str_replace(".", "", $new_string);
			} elseif($direction == "display") {
				$new_string = str_replace("-plus-", " + ", $new_string);
				$new_string = str_replace("-slash-", " / ", $new_string);
				$new_string = str_replace("-and-", " & ", $new_string);
				$new_string = str_replace("-", " ", $new_string);
				$new_string = custom_ucwords($new_string);
				
				if($type == "search-query") {
					$new_string = str_replace(":", " / ", $new_string);
				}
			}
		}
		
		return $new_string;
	}

	function places_count($list) {
		global $db;
		global $split_list_combos;
		
		$split_lists_parent_ids = Array();
		
		foreach($split_list_combos as $split_list_combo) {
			array_push($split_lists_parent_ids, $split_list_combo["parent"]);
		}
						
		if(in_array($list["foursquare_id"], $split_lists_parent_ids)) {
			foreach($split_list_combos as $split_list_combo) {
				if($list["foursquare_id"] === $split_list_combo["parent"]) {
					$split_list_query_string = "SELECT * FROM lists WHERE foursquare_id = '".$list["foursquare_id"]."'";

					foreach($split_list_combo["children"] as $split_list_child_id) {
						$split_list_query_string .= " OR foursquare_id = '".$split_list_child_id."'";
					}

                    $split_list_query = $db->prepare($split_list_query_string);
                    $split_list_query->execute();
                    $split_list_query->setFetchMode(PDO::FETCH_ASSOC);

					$split_lists_total_count = 0;

					while($split_list_data = $split_list_query->fetch()) {
						$split_lists_total_count += $split_list_data["places_count"];
					}

					$count = $split_lists_total_count;
				}
			}
	    } else {
			$count = $list["places_count"];
		}
		
		return $count;
    }
    
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
			"children" => Array("5e0a930d16de620006642ad8", "5026bca3e4b0dfdf254a3d8a")
		), // New York, New York 2, and New York 3
	);
?>
<?php
	require("../../../config/secrets.php");
	date_default_timezone_set('America/Los_Angeles');

	// Connect to database based on server
	if($_SERVER['SERVER_NAME'] == "localhost" || $_SERVER['SERVER_NAME'] == $server["local"]["name"]) {
		$root = "//".$_SERVER["HTTP_HOST"]."/foursquare-places-dev/foursquare-places/";
		
		$db = mysqli_init();
		mysqli_real_connect(
		   $db, 
		   $database["local"]["server"], 
		   $database["local"]["username"], 
		   $database["local"]["password"], 
		   $database["local"]["database-name"],
		   8889
		);
	} else {
		$root = "//".$_SERVER["HTTP_HOST"]."/";
		
		$db = mysqli_init();
		mysqli_real_connect(
		   $db, 
		   $database["remote"]["server"], 
		   $database["remote"]["username"], 
		   $database["remote"]["password"], 
		   $database["remote"]["database-name"],
		   3306
		);
	}
	
	mysqli_set_charset($db, 'UTF8');

	function get_all_category_info() {
		global $db;
		
		$x_parent_categories_query = mysqli_query($db, "SELECT * FROM categories");
	
		while($x_parent_category_info_result = mysqli_fetch_array($x_parent_categories_query)) {
			$x_parent_category_infos[$x_parent_category_info_result["foursquare_id"]] = $x_parent_category_info_result;
		}
		
		$categories_query = mysqli_query($db, "SELECT * FROM categories");
		$x_parent_category_labels = Array("parent", "grandparent", "greatgrandparent", "greatgreatgrandparent");
			
		while($category = mysqli_fetch_array($categories_query)) {
			
			$category_info[$category["foursquare_id"]] = Array(
				"id" => $category["id"],
				"foursquare_id" => $category["foursquare_id"],
				"name" => $category["name"],
				"plural_name" => $category["plural_name"],
				"short_name" => $category["short_name"]
			);	
			
			foreach($x_parent_category_labels as $x_parent_category_label) {
				$x_parent_category_id_label = $x_parent_category_label."_category_foursquare_id";
				$x_parent_category_name_label = $x_parent_category_label."_category_name";
				$x_parent_category_plural_name_label = $x_parent_category_label."_category_plural_name";
				$x_parent_category_short_name_label = $x_parent_category_label."_category_short_name";
					
				if($category[$x_parent_category_id_label] != "") {
					$x_parent_category_info = $x_parent_category_infos[$category[$x_parent_category_id_label]];
					
					$category_info[$category["foursquare_id"]][$x_parent_category_id_label] = $category[$x_parent_category_id_label];
					$category_info[$category["foursquare_id"]][$x_parent_category_name_label] = $x_parent_category_info["name"];
					$category_info[$category["foursquare_id"]][$x_parent_category_plural_name_label] = $x_parent_category_info["plural_name"];
					$category_info[$category["foursquare_id"]][$x_parent_category_short_name_label] = $x_parent_category_info["short_name"];
				}
			}
			
		}
		
		return $category_info;
	}
	
	function custom_ucwords($string) {
		// Mostly from http://www.codingforums.com/php/216104-want-ucwords-leave-conjunctions-prepositions-uncapitalised.html
		$string_words = explode(" ", $string);
	    $small_words = array('and', 'or', 'by', 'for', 'the', 'of'); 
		
		if(in_array($string_words[0], $small_words)) {
			$small_words = str_replace(strtolower($string_words[0]), " ", $small_words);
		}
		
	    $string = preg_replace('#\b('.implode('|', $small_words).')\b#', '@@DO_NOT_CAPITALIZE@@$1', $string); 
	    $string = ucwords($string); 
	    $string = str_replace('@@DO_NOT_CAPITALIZE@@', '', $string); 
		$big_words = array('bbq'); 
		foreach($big_words as $big_word) { $string = str_replace(ucwords($big_word), strtoupper($big_word), $string); }
	    return $string; 
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
	
	function is_mobile(){
	    $aMobileUA = array(
	        '/iphone/i' => 'iPhone', 
	        '/ipod/i' => 'iPod', 
	        '/ipad/i' => 'iPad', 
	        '/android/i' => 'Android', 
	        '/blackberry/i' => 'BlackBerry', 
	        '/webos/i' => 'Mobile'
	    );

	    //Return true if Mobile User Agent is detected
	    foreach($aMobileUA as $sMobileKey => $sMobileOS){
	        if(preg_match($sMobileKey, $_SERVER['HTTP_USER_AGENT'])){
	            return true;
	        }
	    }
	    //Otherwise return false..  
	    return false;
	}
	
	function strip_accents($str) {
	    return strtr(utf8_decode($str), utf8_decode(
		'àáâãäāçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ'),
		'aaaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');
	}
	
	function google_location_metadata($type, $data, $data_label) {
		global $google_api_key;
		$base_url = "https://maps.googleapis.com/maps/api/geocode/json";
		
		if($type == "latlng") {
			$url = $base_url."?latlng=".$data."&key=".$google_api_key;
		} else {
			$url = $base_url."?address=".$data."&key=".$google_api_key;
		}
	    
		$ch = curl_init(); 
	    curl_setopt($ch, CURLOPT_URL, $url); 
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 

	    $google_response_json = curl_exec($ch); 
	    curl_close($ch); 

		$json = json_decode($google_response_json, true);
		$address_components = $json["results"][0]["address_components"];
	
		foreach($address_components as $address_component => $address_component_data) {
			foreach($address_component_data["types"] as $type) {
				$address_data[$type] = Array("long_name" => $address_component_data["long_name"], "short_name" => $address_component_data["short_name"]);
			}
		}
		
		return $address_data[$data_label];
	}
	
	function places_count($list) {
		global $db;
		global $split_list_combos;
		
		foreach($split_list_combos as $split_list_combo) {
			if(in_array($list["foursquare_id"], $split_list_combo)) {
				$split_list_query_string = "SELECT * FROM lists WHERE foursquare_id = '".$list["foursquare_id"]."'";
				
				foreach($split_list_combo as $split_list_id) {
					if($split_list_id !== $list['foursquare_id']) {
						$split_list_query_string .= " OR foursquare_id = '".$split_list_id."'";
					}
				}

				$split_list_query = mysqli_query($db, $split_list_query_string);
				
				$split_lists_total_count = 0;
				
				while($split_list_data = mysqli_fetch_array($split_list_query)) {
					$split_lists_total_count += $split_list_data["places_count"];
				}
				
				$count = $split_lists_total_count;
			} else {
				$count = $list["places_count"];
			}
		}
		
		return $count;
	}
	
	function premium_places_data_fields($data) {
		if($data) {
			return Array(
				"phone_number" => $data["response"]["venue"]["contact"]["phone"],
				"website_url" => $data["response"]["venue"]["url"],
				"menu_url" => $data["response"]["venue"]["menu"]["url"],
				"rating" => $data["response"]["venue"]["rating"],
				"rating_color" => $data["response"]["venue"]["ratingColor"],
				"rating_signal" => $data["response"]["venue"]["ratingSignals"],
			);
		} else {
			return Array(
				"phone_number",
				"website_url",
				"menu_url",
				"rating",
				"rating_color",
				"rating_signal"
			);
		}
	}
	
	// these are lists that span across multiple lists – this combines them so that accessing either list shows places of all lists
	$split_list_combos = Array(
		Array("567d7b1d38fa9c91825e5c7a", "59e5a3ba8a6f1741c057072f", "5be9fdbb0a08ab002c5ca81a"),
		Array("567e16a238fa9c9182a0b903", "5e0a930d16de620006642ad8"),
	);
	
	function convert_range($input, $input_max, $input_min, $output_max, $output_min) {
		return (($input - $input_min) / ($input_max - $input_min)) * ($output_max - $output_min) + $output_min;
	}
?>
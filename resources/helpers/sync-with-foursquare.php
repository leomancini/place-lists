<pre>
<?php
	include("base.php");
	require("country-continent-mapping.php");
	
	function get_lists($offset, $limit) {
		global $foursquare_auth_token;	
		
		$url = "https://api.foursquare.com/v2/users/self/lists?oauth_token=".$foursquare_auth_token."&v=20170427&group=created&limit=".$limit."&offset=".$offset;
		$ch = curl_init(); 
		curl_setopt($ch, CURLOPT_URL, $url); 
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 

		$output = curl_exec($ch);
		curl_close($ch); 
		
		return $output;
	}
	
	function get_all_lists() {
		global $foursquare_lists;
		global $foursquare_lists_places_count;
		global $foursquare_auth_token;
		
		$limit = 200;
		$offset = 0;
		
		while($count <= $limit) {
			$array = get_lists($offset, $limit);		
			$json = json_decode($array, true);
			
			// $count = $json["response"]["list"]["listItems"]["count"];
			$foursquare_lists_from_json = $json["response"]["lists"]["items"];			
			$count = count($foursquare_lists_from_json);
			
			$offset = $count + $offset;
			
			foreach($foursquare_lists_from_json as $foursquare_list_from_json) {
				$foursquare_lists[$foursquare_list_from_json["id"]] = $foursquare_list_from_json["name"];
				$foursquare_lists_places_count[$foursquare_list_from_json["id"]] = $foursquare_list_from_json["listItems"]["count"];
			}
				
			// DEBUG
			/*
			echo "<br><br>ok getting ".$list_id."<br>";
			$next = $offset + $limit;
			$offset_display = $offset - $limit;
			echo "got ".$offset_display." - ".$offset." = ".$count."<br>";
			*/
			
			if($count < $limit) { break; }
		}	
	}
	
	function sync_lists() {
		global $db;
	    global $foursquare_lists;
		global $foursquare_lists_places_count;
		global $country_continent_mapping;
		
		$database_lists = Array();
		$database_lists_query = "SELECT * FROM lists"; 
		$database_lists_results = mysqli_query($db, $database_lists_query);

		while($database_list_result = mysqli_fetch_array($database_lists_results)){
			$database_lists[$database_list_result['foursquare_id']] = $database_list_result['name'];
		}
		
		// ARRAYS FOR LIST ID/NAME PAIR COMPARISON

		$database_list_id_and_name_pair = Array();
		foreach($database_lists as $database_list_id => $database_list_name) {
			$database_list_id_and_name_pair[] = $database_list_id."/".$database_list_name;
		}
				
		$foursquare_list_id_and_name_pair = Array();
		foreach($foursquare_lists as $foursquare_list_id => $foursquare_list_name) {
			$foursquare_list_id_and_name_pair[] = $foursquare_list_id."/".$foursquare_list_name;
		}
		
		echo "<h3>Database check:</h3>";
		foreach($database_lists as $database_list_id => $database_list_name) {
			if(in_array($database_list_id."/".$database_list_name, $foursquare_list_id_and_name_pair)) {
				// Database item still exists in Foursquare and is unchanged
				echo "KEEP: ".$database_list_id." '".$database_list_name."' (still exists on Foursquare)<br>";

				// Update count
				$places_count = $foursquare_lists_places_count[$database_list_id];
				mysqli_query($db, "UPDATE lists SET places_count='".$places_count."' WHERE foursquare_id='".$database_list_id."'");
			} else {
				// Database item doesn't exists in Foursquare
				echo "DELETE: ".$database_list_id." '".$database_list_name."' (missing from Foursquare)<br>";
				$database_list_id = mysqli_real_escape_string($db, $database_list_id);
				
				// DELETE THIS LIST FROM DATABASE
				mysqli_query($db, "DELETE FROM lists WHERE foursquare_id = '".$database_list_id."'");
				
				// DELETE ALL PLACES ON THIS LIST FROM DATABASE
				mysqli_query($db, "DELETE FROM places WHERE foursquare_list_id = '".$database_list_id."'");
				
				// DELETE HEADER IMAGE DIRECTORY
				mkdir("../images/list-headers/".convert("list", "url", $foursquare_list_name)."/");
				
			}
		}
	
		echo "<h3>Foursquare check:</h3>";
		foreach($foursquare_lists as $foursquare_list_id => $foursquare_list_name) {
			if(in_array($foursquare_list_id."/".$foursquare_list_name, $database_list_id_and_name_pair)) {
				// Foursquare item still exists in database and is unchanged
				echo "KEEP: ".$foursquare_list_id." '".$foursquare_list_name."' (still exists in db)<br>";

				// Update count
				$places_count = $foursquare_lists_places_count[$foursquare_list_id];
				mysqli_query($db, "UPDATE lists SET places_count='".$places_count."' WHERE foursquare_id='".$foursquare_list_id."'");
			} else {
				// Foursquare item doesn't exist in database
				echo "ADD: ".$foursquare_list_id." '".$foursquare_list_name."' (missing from db)<br>";

				// CREATE HEADER IMAGE DIRECTORY
				mkdir("../images/list-headers/".convert("list", "url", $foursquare_list_name)."/");
				
				$country_info = google_location_metadata("address", urlencode($foursquare_list_name), "country");
				$state_info = google_location_metadata("address", urlencode($foursquare_list_name), "administrative_area_level_1");
				
				$places_count = $foursquare_lists_places_count[$foursquare_list_id];
				
				$foursquare_list_name = mysqli_real_escape_string($db, $foursquare_list_name);
				$foursquare_list_id = mysqli_real_escape_string($db, $foursquare_list_id);
				
				// ADD THIS TO DATABASE
				mysqli_query($db, "INSERT INTO lists (
						name,
						foursquare_id,
						country_code,
						country,
						state_code,
						state,
						continent,
						places_count
					) VALUES (
						'".$foursquare_list_name."',
						'".$foursquare_list_id."',
						'".$country_info["short_name"]."',
						'".$country_info["long_name"]."',
						'".$state_info["short_name"]."',
						'".$state_info["long_name"]."',
						'".$country_continent_mapping[$country_info["short_name"]]."',
						'".$places_count."'
					)");

				$foursquare_lists[$foursquare_list_id] = $foursquare_list_name;
			}
		}
				
	}

	function get_places_on_list($list_id, $offset, $limit) {
		global $foursquare_auth_token;
		
		$url = "https://api.foursquare.com/v2/lists/".$list_id."?oauth_token=".$foursquare_auth_token."&v=20170427&limit=".$limit."&offset=".$offset;
	    $ch = curl_init(); 
	    curl_setopt($ch, CURLOPT_URL, $url); 
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 

	    $output = curl_exec($ch); 
	    curl_close($ch); 
		
		return $output;
	}
	
	function places_data_fields($data, $list_id) {
		return Array(
			"foursquare_id" => $data["venue"]["id"],
			"foursquare_list_id" => $list_id,
			"name" => $data["venue"]["name"],
			"phone_number" => $data["venue"]["contact"]["phone"],
			"address" => $data["venue"]["location"]["address"],
			"zip" => $data["venue"]["location"]["postalCode"],
			"city" => $data["venue"]["location"]["city"],
			"state" => $data["venue"]["location"]["state"],
			"country" => $data["venue"]["location"]["country"],
			"country_code" => $data["venue"]["location"]["cc"],
			"formatted_address" => $data["venue"]["location"]["formattedAddress"][0]."; ".$data["venue"]["location"]["formattedAddress"][1],
			"location_lat" => $data["venue"]["location"]["lat"],
			"location_long" => $data["venue"]["location"]["lng"],
			"category_id" => $data["venue"]["categories"][0]["id"],
			"website_url" => $data["venue"]["url"],
			"menu_url" => $data["venue"]["menu"]["url"],
			"photo_url_prefix" => $data["photo"]["prefix"],
			"photo_url_suffix" => $data["photo"]["suffix"],
			"rating" => $data["venue"]["rating"],
			"rating_color" => $data["venue"]["ratingColor"],
			"rating_signal" => $data["venue"]["ratingSignals"],
			"saved_timestamp" => $data["venue"]["saves"]["groups"][0]["items"][0]["sharedAt"],	
		);
	}
	
	function get_places($list_id) {
		global $db;

		// FOURSQUARE QUERY
		
		$limit = 50;
		$offset = 0;
		
		while($count <= $limit) {
			$array = get_places_on_list($list_id, $offset, $limit);		
			$json = json_decode($array, true);
			
			$count = $json["response"]["list"]["listItems"]["count"];
			$foursquare_places_from_json = $json["response"]["list"]["listItems"]["items"];
			
			$offset = $count + $offset;
				
			foreach($foursquare_places_from_json as $foursquare_place_from_json) {
				$foursquare_places[$foursquare_place_from_json["venue"]["id"]] = Array(
					"foursquare_list_id" => $list_id,
					"foursquare_id" => $foursquare_place_from_json["venue"]["id"],
					"name" => $foursquare_place_from_json["venue"]["name"]
				);
				$foursquare_places_data[$foursquare_place_from_json["venue"]["id"]] = places_data_fields($foursquare_place_from_json, $list_id);
			}
			
			// DEBUG
			/*
			echo "<br><br>ok getting ".$list_id."<br>";
			$next = $offset + $limit;
			$offset_display = $offset - $limit;
			echo "got ".$offset_display." - ".$offset." = ".$count."<br>";
			*/
			
			if($count < $limit) { break; }
		}

		// DATABASE QUERIES
		
		$database_places = Array();
		$database_places_query = "SELECT * FROM places WHERE foursquare_list_id = '".$list_id."'"; 
		$database_places_results = mysqli_query($db, $database_places_query);

		while($database_place_result = mysqli_fetch_array($database_places_results)){
			$database_places[$database_place_result['id']] = Array(
				"foursquare_id" => $database_place_result['foursquare_id'],
				"foursquare_list_id" => $database_place_result['foursquare_list_id'],
				"name" => $database_place_result['name'],
			);
		}
		
		
		// ARRAYS FOR LIST/PLACE PAIR COMPARISON

		$database_list_and_place_pair = Array();
		foreach($database_places as $database_place) {
			$database_list_and_place_pair[] = $database_place['foursquare_list_id']."/".$database_place["foursquare_id"];
		}
				
		$foursquare_list_and_place_pair = Array();
		foreach($foursquare_places as $foursquare_place) {
			$foursquare_list_and_place_pair[] = $foursquare_place["foursquare_list_id"]."/".$foursquare_place["foursquare_id"];
		}
				
		// COMPARISON
		
		echo "<h3>Database check:</h3>";
		foreach($database_places as $database_place) {
			if(in_array($database_place['foursquare_list_id']."/".$database_place["foursquare_id"], $foursquare_list_and_place_pair)) {
				// Database item still exists in Foursquare and is unchanged
				echo "KEEP: ".$database_place['foursquare_list_id']."/".$database_place["foursquare_id"]." '".$database_place["name"]."' (still exists on Foursquare)<br>";
				// DO NOTHING
			} else {
				// Database item doesn't exists in Foursquare
				echo "DELETE: ".$database_place['foursquare_list_id']."/".$database_place["foursquare_id"]." '".$database_place["name"]."' (missing from Foursquare)<br>";
				$database_place["foursquare_list_id"] = mysqli_real_escape_string($db, $database_place["foursquare_list_id"]);
				$database_place["foursquare_id"] = mysqli_real_escape_string($db, $database_place["foursquare_id"]);
				
				// DELETE THIS FROM DATABASE
				mysqli_query($db, "DELETE FROM places WHERE foursquare_list_id = '".$database_place["foursquare_list_id"]."' AND foursquare_id = '".$database_place["foursquare_id"]."'");
				
			}
		}
		
		echo "<h3>Foursquare check:</h3>";
		foreach($foursquare_places as $foursquare_place) {
			if(in_array($foursquare_place['foursquare_list_id']."/".$foursquare_place["foursquare_id"], $database_list_and_place_pair)) {
				// Foursquare item still exists in database and is unchanged
				echo "KEEP: ".$foursquare_place['foursquare_list_id']."/".$foursquare_place['foursquare_id']." '".$foursquare_place["name"]."' (still exists in db)<br>";
				// DO NOTHING
			} else {
				// Foursquare item doesn't exist in database
				echo "ADD: ".$foursquare_place['foursquare_list_id']."/".$foursquare_place['foursquare_id']." '".$foursquare_place["name"]."' (missing from db)<br>";
				// ADD THIS TO DATABASE
													
				foreach($foursquare_places_data[$foursquare_place['foursquare_id']] as $key => $value) {
					$new_place[$key] = mysqli_real_escape_string($db, $value);
				}

				$neighborhood = google_location_metadata("latlng", urlencode($new_place["location_lat"]).",".urlencode($new_place["location_long"]), "neighborhood");
				
				mysqli_query($db, "INSERT INTO places (
					foursquare_id,
					foursquare_list_id,
					name,
					address,
					zip,
					neighborhood,
					city,
					state,
					country,
					country_code,
					formatted_address,
					location_lat,
					location_long,
					category_id,
					photo_url_prefix,
					photo_url_suffix,
					saved_timestamp
				) VALUES (
					'".$new_place["foursquare_id"]."',
					'".$new_place["foursquare_list_id"]."',
					'".$new_place["name"]."',
					'".$new_place["address"]."',
					'".$new_place["zip"]."',
					'".$neighborhood["long_name"]."',
					'".$new_place["city"]."',
					'".$new_place["state"]."',
					'".$new_place["country"]."',
					'".$new_place["country_code"]."',
					'".$new_place["formatted_address"]."',
					'".$new_place["location_lat"]."',
					'".$new_place["location_long"]."',
					'".$new_place["category_id"]."',
					'".$new_place["photo_url_prefix"]."',
					'".$new_place["photo_url_suffix"]."',
					'".$new_place["saved_timestamp"]."'
				)");  

			}
		}

	}

	if($_GET["refresh_cache"] != 1) {
		$foursquare_lists = Array();
		get_all_lists();
		sync_lists();
		foreach($foursquare_lists as $list_id => $list_name) {
			get_places($list_id);
		}
	} else {
		// clear cache by deleting all place data and redownloading from Foursquare
		// doesn't touch list database
		mysqli_query($db, "TRUNCATE TABLE `places`");
		$foursquare_lists = Array();
		get_all_lists();
		foreach($foursquare_lists as $list_id => $list_name) {
			get_places($list_id);
		}
	}
?>
</pre>
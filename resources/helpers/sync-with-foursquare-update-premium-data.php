<?php
	require("base.php");
	
	// download new premium data from foursquare for a particular place
	function get_premium_place_data($foursquare_id) {
		global $foursquare_auth_token;
		
		$url = "https://api.foursquare.com/v2/venues/".$foursquare_id."?oauth_token=".$foursquare_auth_token."&v=20180616";
		$ch = curl_init(); 
		curl_setopt($ch, CURLOPT_URL, $url); 
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 

		$output = curl_exec($ch); 
		curl_close($ch); 
	
		return $output;
	}
	
	// premium_places_data_fields is in base.php
	
	// add new places from regular places database to places_premium_data database
	$places_info_query = mysqli_query($db, "SELECT * FROM places");
	while($place = mysqli_fetch_array($places_info_query)) {
		mysqli_query($db, "INSERT IGNORE INTO places_premium_data
			SET foursquare_list_and_place_id_combo = '".$place["foursquare_list_id"]."-".$place["foursquare_id"]."',
			foursquare_list_id = '".$place["foursquare_list_id"]."',
			foursquare_id = '".$place["foursquare_id"]."';");
	}
	
	// find the oldest 500 rows (by sorting by last_updated)
	$limit = 480;
	$premium_data_info_query = mysqli_query($db, "SELECT * FROM places_premium_data ORDER BY last_updated ASC LIMIT ".$limit);
	while($place_premium_data = mysqli_fetch_array($premium_data_info_query)) {
		
		// download new premium data from foursquare	
		$foursquare_premium_place_data_json = get_premium_place_data($place_premium_data["foursquare_id"]);	
		$foursquare_premium_place_data = json_decode($foursquare_premium_place_data_json, true);
		
		$premium_place_data[$place_premium_data["foursquare_id"]] = premium_places_data_fields($foursquare_premium_place_data);
		
		// insert new foursquare data into database
		// update last_updated time for those rows
		$now = date("Y-m-d H:i:s");
		mysqli_query($db, "UPDATE places_premium_data
			SET last_updated = '".$now."',
				phone_number = '".$premium_place_data[$place_premium_data["foursquare_id"]]["phone_number"]."',
				website_url = '".$premium_place_data[$place_premium_data["foursquare_id"]]["website_url"]."',
				menu_url = '".$premium_place_data[$place_premium_data["foursquare_id"]]["menu_url"]."',
				rating = '".$premium_place_data[$place_premium_data["foursquare_id"]]["rating"]."',
				rating_color = '".$premium_place_data[$place_premium_data["foursquare_id"]]["rating_color"]."',
				rating_signal = '".$premium_place_data[$place_premium_data["foursquare_id"]]["rating_signal"]."'
			WHERE foursquare_list_and_place_id_combo = '".$place_premium_data["foursquare_list_id"]."-".$place_premium_data["foursquare_id"]."';");
	}
?>
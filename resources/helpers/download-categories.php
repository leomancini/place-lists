<pre>
<?php
	/*
	error_reporting(E_ALL);
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);

	include("base.php");
	
	$url = "https://api.foursquare.com/v2/venues/categories?oauth_token=".$foursquare_auth_token."&v=20170427";
    $ch = curl_init(); 
    curl_setopt($ch, CURLOPT_URL, $url); 
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 

    $output = curl_exec($ch); 
    curl_close($ch); 
		
	$json = json_decode($output, true);
	
	$foursquare_categories_from_json = $json["response"]["categories"];
	
	// print_r($foursquare_categories_from_json);
	
	mysqli_query($db, "TRUNCATE TABLE categories"); 
	
	function save_category(
				$id,
				$name,
				$plural_name,
				$short_name,
				$icon_url_prefix,
				$icon_url_suffix,
				$parent_category_foursquare_id,
				$grandparent_category_foursquare_id,
				$greatgrandparent_category_foursquare_id,
				$greatgreatgrandparent_category_foursquare_id) {

		global $db;

		$id = mysqli_real_escape_string($db, $id);
		$name = mysqli_real_escape_string($db, $name);
		$plural_name = mysqli_real_escape_string($db, $plural_name);
		$icon_url_prefix = mysqli_real_escape_string($db, $icon_url_prefix);
		$icon_url_suffix = mysqli_real_escape_string($db, $icon_url_suffix);
		$parent_category_foursquare_id = mysqli_real_escape_string($db, $parent_category_foursquare_id);
		$grandparent_category_foursquare_id = mysqli_real_escape_string($db, $grandparent_category_foursquare_id);
		$greatgrandparent_category_foursquare_id = mysqli_real_escape_string($db, $greatgrandparent_category_foursquare_id);
		$greatgreatgrandparent_category_foursquare_id = mysqli_real_escape_string($db, $greatgreatgrandparent_category_foursquare_id);
		
		mysqli_query($db, "INSERT INTO categories (foursquare_id, name, plural_name, short_name, icon_url_prefix, icon_url_suffix, parent_category_foursquare_id, grandparent_category_foursquare_id, greatgrandparent_category_foursquare_id, greatgreatgrandparent_category_foursquare_id) VALUES('".$id."', '".$name."', '".$plural_name."', '".$short_name."', '".$icon_url_prefix."', '".$icon_url_suffix."', '".$parent_category_foursquare_id."', '".$grandparent_category_foursquare_id."', '".$greatgrandparent_category_foursquare_id."', '".$greatgreatgrandparent_category_foursquare_id."')");
	} 
		
	foreach($foursquare_categories_from_json as $foursquare_category_from_json => $foursquare_category_data_from_json) {
		echo $foursquare_category_data_from_json["id"];
		save_category($foursquare_category_data_from_json["id"], $foursquare_category_data_from_json["name"], $foursquare_category_data_from_json["pluralName"], $foursquare_category_data_from_json["shortName"], $foursquare_category_data_from_json["icon"]["prefix"], $foursquare_category_data_from_json["icon"]["suffix"], "", "", "", "");
		echo "<br>";
		
		foreach($foursquare_category_data_from_json["categories"] as $sub_category => $sub_category_info) {
			echo $foursquare_category_data_from_json["id"]."/".$sub_category_info["id"];
			save_category($sub_category_info["id"], $sub_category_info["name"], $sub_category_info["pluralName"], $sub_category_info["shortName"], $sub_category_info["icon"]["prefix"], $sub_category_info["icon"]["suffix"], $foursquare_category_data_from_json["id"], "", "", "");
			echo "<br>";
			
			foreach($sub_category_info["categories"] as $sub_sub_category => $sub_sub_category_info) {
				echo $foursquare_category_data_from_json["id"]."/".$sub_category_info["id"]."/".$sub_sub_category_info["id"];
				save_category($sub_sub_category_info["id"], $sub_sub_category_info["name"], $sub_sub_category_info["pluralName"], $sub_sub_category_info["shortName"], $sub_sub_category_info["icon"]["prefix"], $sub_sub_category_info["icon"]["suffix"], $sub_category_info["id"], $foursquare_category_data_from_json["id"], "", "");
				echo "<br>";
				
				foreach($sub_sub_category_info["categories"] as $sub_sub_sub_category => $sub_sub_sub_category_info) {
					echo $foursquare_category_data_from_json["id"]."/".$sub_category_info["id"]."/".$sub_sub_category_info["id"]."/".$sub_sub_sub_category_info["id"];
					save_category($sub_sub_sub_category_info["id"], $sub_sub_sub_category_info["name"], $sub_sub_sub_category_info["pluralName"], $sub_sub_sub_category_info["shortName"], $sub_sub_sub_category_info["icon"]["prefix"], $sub_sub_sub_category_info["icon"]["suffix"], $sub_sub_category_info["id"], $sub_category_info["id"], $foursquare_category_data_from_json["id"], "");
					echo "<br>";
					
					foreach($sub_sub_sub_category_info["categories"] as $sub_sub_sub_sub_category => $sub_sub_sub_sub_category_info) {
						echo $foursquare_category_data_from_json["id"]."/".$sub_category_info["id"]."/".$sub_sub_category_info["id"]."/".$sub_sub_sub_category_info["id"]."/".$sub_sub_sub_sub_category_info["id"];
						save_category($sub_sub_sub_sub_category_info["id"], $sub_sub_sub_sub_category_info["name"], $sub_sub_sub_sub_category_info["pluralName"], $sub_sub_sub_sub_category_info["shortName"], $sub_sub_sub_sub_category_info["icon"]["prefix"], $sub_sub_sub_sub_category_info["icon"]["suffix"], $sub_sub_sub_category_info["id"], $sub_sub_category_info["id"], $sub_category_info["id"], $foursquare_category_data_from_json["id"]);
						echo "<br>";
					}
					
				}
			}
			
		}
		
	}
	
	include("end.php");	
?>
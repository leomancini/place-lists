<pre>
<?php
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
	
	mysqli_query($link, "TRUNCATE TABLE categories") or die(mysql_error()); 
	
	function save_category(
				$id,
				$name,
				$plural_name,
				$short_name,
				$parent_category_foursquare_id,
				$grandparent_category_foursquare_id,
				$greatgrandparent_category_foursquare_id,
				$greatgreatgrandparent_category_foursquare_id) {
		$id = mysql_real_escape_string($id);
		$name = mysql_real_escape_string($name);
		$plural_name = mysql_real_escape_string($plural_name);
		$short_name = mysql_real_escape_string($short_name);
		$parent_category_foursquare_id = mysql_real_escape_string($parent_category_foursquare_id);
		$grandparent_category_foursquare_id = mysql_real_escape_string($grandparent_category_foursquare_id);
		$greatgrandparent_category_foursquare_id = mysql_real_escape_string($greatgrandparent_category_foursquare_id);
		$greatgreatgrandparent_category_foursquare_id = mysql_real_escape_string($greatgreatgrandparent_category_foursquare_id);
		
		mysqli_query($link, "INSERT INTO categories (foursquare_id, name, plural_name, short_name, parent_category_foursquare_id, grandparent_category_foursquare_id, greatgrandparent_category_foursquare_id, greatgreatgrandparent_category_foursquare_id) VALUES('".$id."', '".$name."', '".$plural_name."', '".$short_name."', '".$parent_category_foursquare_id."', '".$grandparent_category_foursquare_id."', '".$greatgrandparent_category_foursquare_id."', '".$greatgreatgrandparent_category_foursquare_id."')") or die(mysql_error());
	} 
		
	foreach($foursquare_categories_from_json as $foursquare_category_from_json => $foursquare_category_data_from_json) {
		echo $foursquare_category_data_from_json["id"];
		save_category($foursquare_category_data_from_json["id"], $foursquare_category_data_from_json["name"], $foursquare_category_data_from_json["pluralName"], $foursquare_category_data_from_json["shortName"]);
		echo "<br>";
		
		foreach($foursquare_category_data_from_json["categories"] as $sub_category => $sub_category_info) {
			echo $foursquare_category_data_from_json["id"]."/".$sub_category_info["id"];
			save_category($sub_category_info["id"], $sub_category_info["name"], $sub_category_info["pluralName"], $sub_category_info["shortName"], $foursquare_category_data_from_json["id"]);
			echo "<br>";
			
			foreach($sub_category_info["categories"] as $sub_sub_category => $sub_sub_category_info) {
				echo $foursquare_category_data_from_json["id"]."/".$sub_category_info["id"]."/".$sub_sub_category_info["id"];
				save_category($sub_sub_category_info["id"], $sub_sub_category_info["name"], $sub_sub_category_info["pluralName"], $sub_sub_category_info["shortName"], $sub_category_info["id"], $foursquare_category_data_from_json["id"]);
				echo "<br>";
				
				foreach($sub_sub_category_info["categories"] as $sub_sub_sub_category => $sub_sub_sub_category_info) {
					echo $foursquare_category_data_from_json["id"]."/".$sub_category_info["id"]."/".$sub_sub_category_info["id"]."/".$sub_sub_sub_category_info["id"];
					save_category($sub_sub_sub_category_info["id"], $sub_sub_sub_category_info["name"], $sub_sub_sub_category_info["pluralName"], $sub_sub_sub_category_info["shortName"], $sub_sub_category_info["id"], $sub_category_info["id"], $foursquare_category_data_from_json["id"]);
					echo "<br>";
					
					foreach($sub_sub_sub_category_info["categories"] as $sub_sub_sub_sub_category => $sub_sub_sub_sub_category_info) {
						echo $foursquare_category_data_from_json["id"]."/".$sub_category_info["id"]."/".$sub_sub_category_info["id"]."/".$sub_sub_sub_category_info["id"]."/".$sub_sub_sub_sub_category_info["id"];
						save_category($sub_sub_sub_sub_category_info["id"], $sub_sub_sub_sub_category_info["name"], $sub_sub_sub_sub_category_info["pluralName"], $sub_sub_sub_sub_category_info["shortName"], $sub_sub_sub_category_info["id"], $sub_sub_category_info["id"], $sub_category_info["id"], $foursquare_category_data_from_json["id"]);
						echo "<br>";
					}
					
				}
			}
			
		}
		
	}
	
?>
<?php
	require("base.php");
	require("render-list.php");
	header('Content-Type: text/html; charset=utf-8');

	$list = $_GET['list'];
	$list = explode("list-id:", $list);
	
	if($list[0] != "") {
		if(preg_match("/:/", $list[0])) {
			$list_name_and_neighborhood = explode(":", $list[0]);
			$list_name_display = $list_name_and_neighborhood[0];
		
			$url_neighborhood["raw"] = str_replace($list_name_display.":", "", $list[0]);
			
			$url_neighborhood["url"] = convert("search-query", "url", $url_neighborhood["raw"]);
				
			$url_neighborhood_terms = explode(":", $url_neighborhood["raw"]);
			$url_neighborhood_term_count = 0;
			foreach($url_neighborhood_terms as $url_neighborhood_term) {
				$url_neighborhood_term_count++;
				if($url_neighborhood_term_count > 1) { $separator = " / "; }
				$url_neighborhood["display"] .= $separator.convert("search-query", "display", $url_neighborhood_term);
			}
		
		} else {
			$list_name_display = $list[0];
		}
	
		// URL is a list name
		$list_name_display = convert("list", "display", $list_name_display);
		$list_name_display = mysql_real_escape_string($list_name_display);

		$lists_query = mysqli_query($link, "SELECT * FROM lists WHERE name = '".$list_name_display."'") or die(mysql_error());
		$count = mysql_num_rows($lists_query);

		$list_counter = 0;

		if($count > 1) {
			// Multiple lists match URL request name
			while($list = mysqli_fetch_array($lists_query)) {
				$list_counter++;
				echo "<a href='./id:".$list['foursquare_id']."'>".$list['name']."-".$list_counter."</a>";
				echo "<br>";
			}
			break;
		} else {
			// One list matches URL request name
			$list = mysqli_fetch_array($lists_query);
		}
	} else {
		// URL is a list id
		$list_id = $list[1];
		$list_id = mysql_real_escape_string($list_id);

		$list_query = mysqli_query($link, "SELECT * FROM lists WHERE foursquare_id = '".$list_id."'") or die(mysql_error());
		$list = mysqli_fetch_array($list_query);
	}
	
	if($list) {
		// List exists
		$list_name_url = convert("list", "url", $list["name"]);
		$list_name_url_without_neighborhood = $list_name_url;
		if($url_neighborhood) { $list_name_url .= ":".$url_neighborhood["url"]; }

		$header_image_directory = "../../resources/images/list-headers/".$list_name_url_without_neighborhood."/";
		$header_image_directory_files = scandir($header_image_directory);
		foreach($header_image_directory_files as $header_image_directory_file) {
			if($header_image_directory_file != "." && $header_image_directory_file != "..") {
				$header_image_path = $header_image_directory_file;
			}
		}
?>
	<!DOCTYPE HTML>
	<html>
		<head>
			<title><?php if($list) { echo $list["name"]; } else { echo "Untitled"; }?></title>
			<link rel="stylesheet" href="<?php echo $root; ?>/resources/css/fonts.css">
			<link rel="stylesheet" href="<?php echo $root; ?>/resources/css/common.css">
			<link rel="stylesheet" href="<?php echo $root; ?>/resources/css/list-<?php echo (is_mobile() ? "mobile" : "desktop"); ?>.css">
			<meta name="viewport" content="width = device-width, initial-scale = 1, user-scalable = no" />
			<script src="<?php echo $root; ?>resources/js/jquery.js"></script>
			<script src="<?php echo $root; ?>resources/js/stretchy.js" data-filter=".stretchy"></script>
			<?php if(is_mobile()) { ?><script>$(document).ready(function() { is_mobile(); });</script><?php } ?>
			<script src="<?php echo $root; ?>resources/js/list.js"></script>
		</head>
		<body ontouchstart="">

		<?php
			if(($_GET['category1'] == "" &&
				$_GET['category2'] == "" &&
				$_GET['category3'] == "" &&
				$_GET['category4'] == "" &&
				$_GET['category5'] == "" &&
				$url_neighborhood["url"] == "")
			|| is_mobile() == false) {			
		?>
			<div id="header-image">
				<?php if($header_image_path) { ?>
					<img class="photo" src="<?php echo $root; ?>/resources/images/list-headers/<?php echo $list_name_url_without_neighborhood; ?>/<?php echo $header_image_path; ?>">	
				<?php
					} else {
						if($url_neighborhood["url"]) {
							foreach($url_neighborhood_terms as $url_neighborhood_term) {
								$places_with_this_neighborhood_query = mysqli_query($link, "SELECT neighborhood FROM places WHERE neighborhood = '".convert("search-query", "display", $url_neighborhood_term)."' LIMIT 1") or die(mysql_error());
								$is_real_neighborhood = mysql_num_rows($places_with_this_neighborhood_query);
								if($is_real_neighborhood == 1) {
									$place = mysqli_fetch_array($places_with_this_neighborhood_query);
								}
								$map_neighborhood = $place["neighborhood"];
							}
						}
						
						if($url_neighborhood["url"] && $map_neighborhood) {
							$map["center"] = $list["name"]." ".$map_neighborhood;
							$map["zoom"] = 15;
						} else {
							$map["center"] = $list["name"];
							$map["zoom"] = 11;
						}
				?>
					<div id="map"></div>
						<script>
							function initMap() {
								var map = new google.maps.Map(document.getElementById('map'), {
									zoom: <?php echo $map["zoom"]; ?>,
									disableDefaultUI: true,
									gestureHandling: 'none',
									scrollwheel: false,
									disableDoubleClickZoom: true,
									panControl: false,
									streetViewControl: false,
									styles: [
										{
											elementType: 'geometry', stylers: [{color: '#ebe3cd'}]}, {elementType: 'labels.text.fill', stylers: [{color: '#523735'}]}, {elementType: 'labels.text.stroke', stylers: [{color: '#f5f1e6'}]}, { featureType: 'administrative', elementType: 'geometry.stroke', stylers: [{color: '#c9b2a6'}]}, { featureType: 'administrative.land_parcel', elementType: 'geometry.stroke', stylers: [{color: '#dcd2be'}]}, { featureType: 'administrative.land_parcel', elementType: 'labels.text.fill', stylers: [{color: '#ae9e90'}]}, { featureType: 'landscape.natural', elementType: 'geometry', stylers: [{color: '#dfd2ae'}]}, { featureType: 'poi', elementType: 'geometry', stylers: [{color: '#dfd2ae'}]}, { featureType: 'poi', elementType: 'labels.text.fill', stylers: [{color: '#93817c'}]}, { featureType: 'poi.park', elementType: 'geometry.fill', stylers: [{color: '#a5b076'}]}, { featureType: 'poi.park', elementType: 'labels.text.fill', stylers: [{color: '#447530'}]}, { featureType: 'road', elementType: 'geometry', stylers: [{color: '#f5f1e6'}]}, { featureType: 'road.arterial', elementType: 'geometry', stylers: [{color: '#fdfcf8'}]}, { featureType: 'road.highway', elementType: 'geometry', stylers: [{color: '#f8c967'}]}, { featureType: 'road.highway', elementType: 'geometry.stroke', stylers: [{color: '#e9bc62'}]}, { featureType: 'road.highway.controlled_access', elementType: 'geometry', stylers: [{color: '#e98d58'}]}, { featureType: 'road.highway.controlled_access', elementType: 'geometry.stroke', stylers: [{color: '#db8555'}]}, { featureType: 'road.local', elementType: 'labels.text.fill', stylers: [{color: '#806b63'}]}, { featureType: 'transit.line', elementType: 'geometry', stylers: [{color: '#dfd2ae'}]}, { featureType: 'transit.line', elementType: 'labels.text.fill', stylers: [{color: '#8f7d77'}]}, { featureType: 'transit.line', elementType: 'labels.text.stroke', stylers: [{color: '#ebe3cd'}]}, { featureType: 'transit.station', elementType: 'geometry', stylers: [{color: '#dfd2ae'}]}, { featureType: 'water', elementType: 'geometry.fill', stylers: [{color: '#b9d3c2'}]}, { featureType: 'water', elementType: 'labels.text.fill', stylers: [{color: '#92998d'}]
										}
									],

									hiding: [
										{
											featureType: 'poi.business', stylers: [{visibility: 'off'}]}, { featureType: 'transit', elementType: 'labels.icon', stylers: [{visibility: 'off'}]
										}
									]
								});
								var geocoder = new google.maps.Geocoder();
								var address = "<?php echo $map["center"]; ?>";

								geocoder.geocode({'address': address}, function(results, status) {
									if (status === 'OK') {
										map.setCenter(results[0].geometry.location);
									}
								});
							}
							</script>
							<script async defer src="https://maps.googleapis.com/maps/api/js?key=<?php echo $google_api_key; ?>&callback=initMap"></script>
					<?php } ?>
				</div>
		<?php } ?>		
			<div id="master">
				<div id="container">
					<div id="list">
						<?php render_list("WHERE foursquare_list_id = '".$list['foursquare_id']."'"); ?>
					</div>
				</div>
			</div>
		</body>
	</html>
<?php
	} else {
		// List doesn't exist
		// header('Location: ./');
	}
?>
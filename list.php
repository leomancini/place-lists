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
		$list_name_display = mysqli_real_escape_string($db, $list_name_display);

		$lists_query = mysqli_query($db, "SELECT * FROM lists WHERE name = '".$list_name_display."'");
		$count = mysqli_num_rows($lists_query);

		$list_counter = 0;

		if($count > 1) {
			// Multiple lists match URL request name
			while($list = mysqli_fetch_array($lists_query)) {
				$list_counter++;
				echo "<a href='./id:".$list['foursquare_id']."'>".$list['name']."-".$list_counter."</a>";
				echo "<br>";
			}
		} else {
			// One list matches URL request name
			$list = mysqli_fetch_array($lists_query);
		}
	} else {
		// URL is a list id
		$list_id = $list[1];
		$list_id = mysqli_real_escape_string($db, $list_id);

		$list_query = mysqli_query($db, "SELECT * FROM lists WHERE foursquare_id = '".$list_id."'");
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
	
		// Generate title including list name, neighborhood, categories, 
		$title = $list["name"];
		if($url_neighborhood["url"]) { $title .= " / ".convert("neighborhood", "display", $url_neighborhood["url"]); }
		if($_GET["category1"]) { $title .= " / ".convert("category", "display", $_GET["category1"]); }
		if($_GET["category2"]) { $title .= " / ".convert("category", "display", $_GET["category2"]); }
		if($_GET["category3"]) { $title .= " / ".convert("category", "display", $_GET["category3"]); }
		if($_GET["category4"]) { $title .= " / ".convert("category", "display", $_GET["category4"]); }
		if($_GET["category5"]) { $title .= " / ".convert("category", "display", $_GET["category5"]); }
		
		if($url_neighborhood["url"]) {
			foreach($url_neighborhood_terms as $url_neighborhood_term) {
				$check_neighborhood_is_real_query = mysqli_query($db, "SELECT neighborhood_long_name FROM neighborhoods WHERE neighborhood_long_name = '".convert("search-query", "display", $url_neighborhood_term)."' LIMIT 1");
				$is_real_neighborhood = mysqli_num_rows($check_neighborhood_is_real_query);
				if($is_real_neighborhood == 1) {
					$neighborhood = mysqli_fetch_array($check_neighborhood_is_real_query);
				}
				$map_neighborhood = $neighborhood["neighborhood_long_name"];
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
	<!DOCTYPE HTML>
	<html>
		<head>
			<title><?php echo $title; ?></title>
			<meta property="og:title" content="<?php echo $title; ?>">
			<meta property="og:description" content="by Leo Mancini">
			<meta property="og:type" content="website">
			<meta property="og:image:width" content="600">
			<meta property="og:image:height" content="315">
			<meta property="og:image" content="https://maps.googleapis.com/maps/api/staticmap?key=<?php echo $google_api_key; ?>&center=<?php echo $map["center"]; ?>&zoom=<?php echo $map["zoom"]; ?>&format=png&maptype=roadmap&style=element:geometry%7Ccolor:0xebe3cd&style=element:labels.text.fill%7Ccolor:0x523735&style=element:labels.text.stroke%7Ccolor:0xf5f1e6&style=feature:administrative%7Celement:geometry.stroke%7Ccolor:0xc9b2a6&style=feature:administrative.land_parcel%7Celement:geometry.stroke%7Ccolor:0xdcd2be&style=feature:administrative.land_parcel%7Celement:labels.text.fill%7Ccolor:0xae9e90&style=feature:landscape.natural%7Celement:geometry%7Ccolor:0xdfd2ae&style=feature:poi%7Celement:geometry%7Ccolor:0xdfd2ae&style=feature:poi%7Celement:labels.text.fill%7Ccolor:0x93817c&style=feature:poi.park%7Celement:geometry.fill%7Ccolor:0xa5b076&style=feature:poi.park%7Celement:labels.text.fill%7Ccolor:0x447530&style=feature:road%7Celement:geometry%7Ccolor:0xf5f1e6&style=feature:road.arterial%7Celement:geometry%7Ccolor:0xfdfcf8&style=feature:road.highway%7Celement:geometry%7Ccolor:0xf8c967&style=feature:road.highway%7Celement:geometry.stroke%7Ccolor:0xe9bc62&style=feature:road.highway.controlled_access%7Celement:geometry%7Ccolor:0xe98d58&style=feature:road.highway.controlled_access%7Celement:geometry.stroke%7Ccolor:0xdb8555&style=feature:road.local%7Celement:labels.text.fill%7Ccolor:0x806b63&style=feature:transit.line%7Celement:geometry%7Ccolor:0xdfd2ae&style=feature:transit.line%7Celement:labels.text.fill%7Ccolor:0x8f7d77&style=feature:transit.line%7Celement:labels.text.stroke%7Ccolor:0xebe3cd&style=feature:transit.station%7Celement:geometry%7Ccolor:0xdfd2ae&style=feature:water%7Celement:geometry.fill%7Ccolor:0xb9d3c2&style=feature:water%7Celement:labels.text.fill%7Ccolor:0x92998d&size=600x315">	
			<link rel="stylesheet" href="<?php echo $root; ?>/resources/css/fonts.css">
			<link rel="stylesheet" href="<?php echo $root; ?>/resources/css/common.css">
			<link rel="stylesheet" href="<?php echo $root; ?>/resources/css/list-<?php echo (is_mobile() ? "mobile" : "desktop"); ?>.css">
			<meta name="viewport" content="width = device-width, initial-scale = 1, user-scalable = no" />
			<script src="<?php echo $root; ?>resources/js/jquery.js"></script>
			<script src="<?php echo $root; ?>resources/js/stretchy.js" data-filter=".stretchy"></script>
			<?php if(is_mobile()) { ?><script>$(document).ready(function() { is_mobile(); });</script><?php } ?>
			<script src="<?php echo $root; ?>resources/js/list.js"></script>
			<!-- Global site tag (gtag.js) - Google Analytics -->
			<script async src="https://www.googletagmanager.com/gtag/js?id=UA-112757234-2"></script>
			<script>
			  window.dataLayer = window.dataLayer || [];
			  function gtag(){dataLayer.push(arguments);}
			  gtag('js', new Date());

			  gtag('config', 'UA-112757234-2');
			</script>
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
					<div id="list" data-list-url="<?php echo $list_name_url; ?>">
						<?php
							$list_query = "WHERE foursquare_list_id = '".$list['foursquare_id']."'";
							
							foreach($split_list_combos as $split_list_combo) {
								if(in_array($list['foursquare_id'], $split_list_combo)) {
									foreach($split_list_combo as $split_list_id) {
										if($split_list_id !== $list['foursquare_id']) {
											$list_query .= " OR foursquare_list_id = '".$split_list_id."'";
										}
									}
								}
							}
							
							render_list($list_query);
						?>
					</div>
				</div>
			</div>
		</body>
	</html>
<?php
	} else {
		// List doesn't exist
		header('Location: ./');
	}
?>
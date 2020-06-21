<pre>
<?php
	/*
	require("../resources/helpers/base.php");
	
	// $neighborhood = google_location_metadata("latlng", urlencode($_GET['lat']).",".urlencode($_GET['lng']), "neighborhood")["short_name"];
	// echo $neighborhood;

	require("../resources/helpers/country-continent-mapping.php");
	$country_info = google_location_metadata("address", urlencode($_GET['data']), "country");

	echo "Country = ".$country_info["long_name"];
	echo "<br>";
	echo "Continent = ".$country_continent_mapping[$country_info["short_name"]];
?>
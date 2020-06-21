<html>
<?php
	/*
	$max_width = 900;
?>
<head>
	<style>
		* {
			-webkit-font-smoothing: antialiased;
		}
		
		.label, .bar {
			height: 12px;
			margin: 0;
		}
		
		.label {
			display: inline-block;
			width: 200px;
			font: 12px "Helvetica";
			overflow: hidden;
		}
		
		.label.after {
			margin-left: 4px;
			font-size: 10px;
			color: grey;
		}
		
		.bar {
			background: lightblue;
			display: inline-block;
			max-width: <?php echo $max_width; ?>px;
			// border-radius: 0 3px 3px 0;
		}
		
		.bar.max {
			background: darkblue;
		}
	</style>
</head>

<body>
	<?php
		require("../resources/helpers/base.php");
	
		$places_info_query = mysqli_query($db, "SELECT * FROM places");
	
		while($place = mysqli_fetch_array($places_info_query)) {
		
			if($place["saved_timestamp"] != "") {
				if($_GET['scale'] == "year") {
					$scale = "Y";
					$scale_display = 1;
				} elseif($_GET['scale'] == "month") {
					$scale = "Y-m";
					$scale_display = 5;
				} elseif($_GET['scale'] == "week") {
					$scale = "Y-m-W";
					$scale_display = 10;
				} elseif($_GET['scale'] == "day") {
					$scale = "Y-m-j";
					$scale_display = 10;
				} else {
					$scale = "Y-m";
					$scale_display = 10;
				}
				$unit = date($scale, $place["saved_timestamp"]);
				$time_series[$unit][] = $place["name"];
			}
		}
	
		// print_r($time_series);
	
		ksort($time_series);
		
		foreach($time_series as $unit => $places) {
			$bar_width = 0;
			$count = count($places);
			echo '<div class="label">'.$unit."</div>";
			$bar_width = $count * $scale_display;
			$extra_classes = "";
			if($bar_width >= $max_width) { $extra_classes .= "max"; }
			echo '<div class="bar '.$extra_classes.'" style="width: '.$bar_width.'px;"></div>';
			echo '<div class="label after"> '.$count."</div>";
			echo "<br>";
		}
	?>
</body>
</html>
<?php
	require("base.php");
?>
<!DOCTYPE HTML>
<html>

<head>
	<title>Places</title>
	<link rel="stylesheet" href="<?php echo $root; ?>/resources/css/fonts.css">
	<link rel="stylesheet" href="<?php echo $root; ?>/resources/css/common.css">
	<link rel="stylesheet" href="<?php echo $root; ?>/resources/css/lists.css">
	<meta name="viewport" content = "width = device-width, initial-scale = 1, user-scalable = no" />
	<script src="<?php echo $root; ?>resources/js/jquery.js"></script>
	<script src="<?php echo $root; ?>resources/js/lists.js"></script>
	<meta property="og:image" content="<?php echo $root; ?>resources/images/map.png">
</head>
<body ontouchstart="">

	<div id="master">
		<div id="container">
			<div id="content">
				<input type="text" id="search" placeholder="Search lists..." autocapitalize="none" autocorrect="off">
				<div id="lists">

					<?php
						$lists_query = mysqli_query($link, "SELECT * FROM lists") or die(mysql_error());
						while($list = mysqli_fetch_array($lists_query)) {
							$lists_by_section[$list["continent"]][str_pad($list["places_count"], 20, "0", STR_PAD_LEFT)."-----".$list["name"]] = $list;
						}
						
						krsort($lists_by_section);
						
						foreach($lists_by_section as $section_label => $lists_of_section) {
							if($section_label != "") {
								echo "<div class='section-header' id='".convert("section-header", "url", $section_label)."'>".$section_label."</div>";
							} else {
								echo "<div class='section-header-replacement'></div>";
							}
						
							krsort($lists_of_section);
						
							foreach($lists_of_section as $list_id_and_name => $list) {
								echo '<div class="list" ';
								echo 'data-search-terms="'.$list['continent']." ".$list['country']." ".$list['state']." ".$list['name'].'"';
								echo 'data-section="'.convert("section-header", "url", $section_label).'"';
								echo '>';
									echo '<a href="'.convert("list", "url", $list["name"]).'">';
										echo "<span class='label'>";
											echo $list["name"];	
										echo "</span>";
									echo '</a>';
									echo "<span class='count'>&nbsp;&nbsp;";
										echo $list["places_count"];
									echo "</span>";
								echo '</div>';
							}
						}
					?>
					
					<div id='empty-search-results'>Nothing found...<br><a id='clear-search'>clear search</a></div>
	
				</div>
			</div>
		</div>
	</div>
	
</body>
</html>
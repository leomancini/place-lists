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
	<meta property="og:url" content="https://places.leo.gd">
	<meta property="og:type" content="website">
	<meta property="og:title" content="Places">
	<meta property="og:og:description" content="Lists of places around the world, curated by Leo Mancini.">
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

	<div id="master">
		<div id="container">
			<div id="content">
				<input type="text" id="search" placeholder="Search lists..." autocapitalize="none" autocorrect="off">
				<div id="lists">

					<?php
						$hidden_lists = Array();
						foreach($split_list_combos as $split_list_combo) {
							foreach($split_list_combo as $split_list_key => $split_list_id) {
								if($split_list_key != 0) {
									array_push($hidden_lists, $split_list_id); 
								}
							}
						}
						
						$lists_query = mysqli_query($db, "SELECT * FROM lists WHERE private <> 1");
						while($list = mysqli_fetch_array($lists_query)) {
							if(!in_array($list["foursquare_id"], $hidden_lists)) {
								$lists_by_section[$list["continent"]][str_pad($list["places_count"], 20, "0", STR_PAD_LEFT)."-----".$list["name"]] = $list;
							}
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
									echo '<a target="_blank" href="'.convert("list", "url", $list["name"]).'">';
										echo "<span class='label'>";
											echo $list["name"];	
										echo "</span>";
									echo '</a>';
									echo "<span class='count'>&nbsp;&nbsp;";
										echo places_count($list);
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
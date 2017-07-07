<?php
	$city = $_GET['city'];
	require("../resources/helpers/base.php");
	require("../resources/helpers/render-list.php");
	
	$city_safe = mysql_real_escape_string($city);
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
	<?php if(is_mobile()) { ?><script>$(document).ready(function() { is_mobile(); });</script><?php } ?>
	<script src="<?php echo $root; ?>resources/js/list.js"></script>
</head>

	<div id="master">
		<div id="container">
			<div id="list">
				<?php render_list("WHERE city = '".$city_safe."'"); ?>
			</div>
		</div>
	</div>

</body>
</html>
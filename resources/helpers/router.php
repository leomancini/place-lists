<?php
	if($_GET['list'] != "") {
		if($_GET['list'] == "c"
		|| $_GET['list'] == "category"
		|| $_GET['list'] == "all") {
			require("../../category.php");
		} else {
			require("../../list.php");
		}
	} else {
		require("../../lists.php");
	}
?>
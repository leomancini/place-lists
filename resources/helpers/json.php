<?php
	require("base.php");

	if($_GET["data"] == "lists") {
		$lists_query = mysql_query("SELECT * FROM lists") or die(mysql_error());
	
		$lists = Array();
	
		while($list = mysql_fetch_array($lists_query)) {
			$lists[] = $list["name"];
		}
	
		$lists = json_encode($lists);
		echo $lists;
	}
?>
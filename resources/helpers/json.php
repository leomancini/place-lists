<?php
	require("base.php");

	if($_GET["data"] == "lists") {
		$lists_query = mysqli_query($db, "SELECT * FROM lists");
	
		$lists = Array();
	
		while($list = mysqli_fetch_array($lists_query)) {
			$lists[] = $list["name"];
		}
	
		$lists = json_encode($lists);
		echo $lists;
	}
?>
<?php
	$link = mysqli_connect("localhost", "webuser", "password", "test"); (change 'password' to your pass)
	if ($link) {
		$query = mysqli_query($link, "SELECT * FROM test");
		while($array = mysqli_fetch_array($query)) {
			echo $array['data']."<br />";
		}
	} else {
		echo "MySQL error :".mysqli_error();
	}
?>

<?php

include('config.php');

header("Content-Type: text/html");

function get_values($values) {
    $array = array();
    foreach ($values as $value) {
    	$array[$value] = trim($_POST[$value]);
    }
    return $array;
}

if (isset($_POST["tepo"]) && $_POST["tepo"] == "don") {
	$names = array(
		'table_name',
		'contents'
	);
	$values = get_values($names);
	$mysqli = new mysqli("localhost", "root");
	if ($mysqli) {
		$mysqli->set_charset("utf8");

		$mysqli->select_db(constant('DB_NAME'));

		$q = "INSERT INTO tables (";
		$i = 0;
		foreach ($values as $key => $value) {
			if ($i != 0) {
				$q .= ", ";
			}
			$q .= $key;
			$i++;
		}
		$q .= ") VALUES (";
		$i = 0;
		foreach ($values as $key => $value) {
			if ($i != 0) {
				$q .= ", ";
			}
			$q .= "'" . $mysqli->real_escape_string($value) . "'";
			$i++;
		}
		$q .= ")";
		if ($mysqli->query($q)) {
			header("Location: entries.php");
			exit;
		} else {
			echo $mysqli->error;
		}

		$mysqli->close();
	} else {
		echo "cannot connect";
	}
}

?>
<html><head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>Add Table</title>
</head>
<body>
<form method="POST">
<input type="hidden" name="tepo" value="don" />
Table Name: <input type="text" size="32" name="table_name" /><br />
Contents:<br />
<textarea rows="10" cols="55" name="contents"></textarea><br />
<input type="submit" value="Send" />
</form>
<p>
	<a href="entries.php">Entries</a>
</p>
</body></html>

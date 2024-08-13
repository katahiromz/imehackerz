<?php

include('config.php');

header("Content-Type: text/html");

$mysqli = new mysqli("localhost", "root");
if ($mysqli) {
	$mysqli->set_charset("utf8");

	$mysqli->select_db(constant('DB_NAME'));

	$q = "SELECT entry_name, entry_type FROM entries";
	$result = $mysqli->query($q);
	if ($result) {
		$names = array();
		$types = array();
		$translations = array();
		while ($row = $result->fetch_row()) {
			$names[] = $row[0];
			$types[] = $row[1];
			$translations[] = imehack_translate($mysqli, $row[0]);
		}
	} else {
		echo $mysqli->error;
	}

	$mysqli->close();
} else {
	echo "cannot connect";
}

?>
<html><head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>Entries</title>
</head>
<body>
<h1>Entries</h1>

<ul>
<?php
	$i = 0;
	foreach ($names as $name) {
		echo "<li>";
		echo "<a href=\"show_entry.php?entry_name=" . $name . "\">" . $translations[$i] . " " . $types[$i] . "</a>";
		echo "</li>";
		$i++;
	}
?>
</ul>

<p>
	<a href="add_entry.php">Add Entry</a> |
	<a href="add_table.php">Add Table</a>
</p>

</body></html>

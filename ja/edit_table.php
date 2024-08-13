<?php

include('config.php');

header("Content-Type: text/html");

function get_values($values) {
    $array = array();
    foreach ($values as $value) {
        if (isset($_POST[$value])) {
        	$array[$value] = trim($_POST[$value]);
        } else if (isset($_GET[$value])) {
        	$array[$value] = trim($_GET[$value]);
        }
    }
    return $array;
}

function get_field($name) {
	global $assoc;
	if (isset($assoc) && isset($assoc[$name])) {
		return htmlspecialchars($assoc[$name]);
	}
	return NULL;
}

$names = array(
	'table_name',
	'contents'
);

if (isset($_POST["tepo"]) && $_POST["tepo"] == "don") {
	$values = get_values($names);
	$mysqli = new mysqli("localhost", "root");
	if ($mysqli) {
		$mysqli->set_charset("utf8");

		$mysqli->select_db(constant('DB_NAME'));

		$q = "UPDATE tables SET ";
		$assoc = array();
		$i = 0;
		foreach ($values as $key => $value) {
			if ($i != 0) {
				$q .= ", ";
			}
			$q .= $key;
			$q .= "='";
			$q .= $mysqli->real_escape_string($value);
			$q .= "'";
			$assoc[$key] = $value;
			$i++;
		}
		$q .= "WHERE table_name='";
		$q .= $mysqli->real_escape_string($_GET["table_name"]);
		$q .= "'";
		if ($mysqli->query($q)) {
			echo "Updated.";
		} else {
			echo $mysqli->error;
		}
		$mysqli->close();
	} else {
		echo "cannot connect";
	}
} else {
	$mysqli = new mysqli("localhost", "root");
	if ($mysqli) {
		$mysqli->set_charset("utf8");

		$mysqli->select_db(constant('DB_NAME'));

		$q = "SELECT ";
		$i = 0;
		foreach ($names as $name) {
			if ($i != 0) {
				$q .= ", ";
			}
			$q .= $name;
			$i++;
		}
		$q .= " FROM tables ";
		$q .= "WHERE table_name='";
		$q .= $mysqli->real_escape_string($_GET["table_name"]);
		$q .= "'";
		$result = $mysqli->query($q);
		if ($result) {
			$assoc = $result->fetch_array(MYSQLI_ASSOC);
		} else {
			echo $mysqli->error;
		}

		$mysqli->close();
	} else {
		echo "cannot connect";
	}
}

?><html><head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>表の編集</title>
</head>
<body>
<form method="POST">
<input type="hidden" name="tepo" value="don" />
表の名前: <input type="text" size="32" name="table_name" value="<?= get_field("table_name");?>" /><br />
中身:<br />
<textarea rows="10" cols="55" name="contents"><?= get_field("contents");?></textarea><br />
<input type="submit" value="送信" />
</form>

<p>
	<a href="entries.php">項目一覧</a> |
	<a href="add_entry.php">項目を追加する</a> | 
	<a href="add_table.php">表を追加する</a> | 
</p>
</body></html>

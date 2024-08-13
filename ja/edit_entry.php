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
	'entry_name',
	'entry_type',
	'description',
	'syntax',
	'parameters',
	'return_value',
	'remarks',
	'see_also',
	'header',
	'modules',
	'sources',
	'translators',
	'ros_location',
	'analysis',
	'prev_page',
	'next_page'
);

if (isset($_POST["tepo"]) && $_POST["tepo"] == "don") {
	$values = get_values($names);
	$mysqli = new mysqli("localhost", "root");
	if ($mysqli) {
		$mysqli->set_charset("utf8");

		$mysqli->select_db(constant('DB_NAME'));

		$q = "UPDATE entries SET ";
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
		$q .= "WHERE entry_name='";
		$q .= $mysqli->real_escape_string($_GET["entry_name"]);
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
		$q .= " FROM entries ";
		$q .= "WHERE entry_name='";
		$q .= $mysqli->real_escape_string($_GET["entry_name"]);
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
<title>編集</title>
</head>
<body>
<form method="POST">
<input type="hidden" name="tepo" value="don" />
項目名: <input type="text" size="32" name="entry_name" value="<?= get_field("entry_name");?>" /><br />
項目の種類: <input type="text" size="32" name="entry_type" value="<?= get_field("entry_type");?>" /><br />
説明:<br />
<textarea rows="5" cols="55" name="description"><?= get_field("description");?></textarea><br />
文法:<br />
<textarea rows="5" cols="55" name="syntax"><?= get_field("syntax");?></textarea><br />
パラメーター:<br />
<textarea rows="5" cols="55" name="parameters"><?= get_field("parameters");?></textarea><br />
戻り値:<br />
<textarea rows="5" cols="55" name="return_value"><?= get_field("return_value");?></textarea><br />
解説:<br />
<textarea rows="5" cols="55" name="remarks"><?= get_field("remarks");?></textarea><br />
参照 (複数可。'|'で区切る): <input type="text" size="32" name="see_also" value="<?= get_field("see_also");?>" /><br />
ヘッダー: <input type="text" size="32" name="header" value="<?= get_field("header");?>" /><br />
モジュール: <input type="text" size="32" name="modules" value="<?= get_field("modules");?>" /><br />
情報源:<br />
<textarea rows="5" cols="55" name="sources"><?= get_field("sources");?></textarea><br />
翻訳者:<br />
<textarea rows="5" cols="55" name="translators"><?= get_field("translators");?></textarea><br />
ROSにおける所在:<br />
<textarea rows="5" cols="55" name="ros_location"><?= get_field("ros_location");?></textarea><br />
分析:<br />
<textarea rows="5" cols="55" name="analysis"><?= get_field("analysis");?></textarea><br />
前のページ: <input type="text" size="32" name="prev_page" value="<?= get_field("prev_page");?>" /><br />
次のページ: <input type="text" size="32" name="next_page" value="<?= get_field("next_page");?>" /><br />
<input type="submit" value="送信" />
</form>

<p>
	<a href="entries.php">項目一覧</a> |
	<a href="add_entry.php">項目を追加する</a> | 
	<a href="add_table.php">表を追加する</a> | 
	<a href="show_entry.php?entry_name=<?= $_GET["entry_name"] ?>"><?= $_GET["entry_name"] ?>を表示する</a>
</p>
</body></html>

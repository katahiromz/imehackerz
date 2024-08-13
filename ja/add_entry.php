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
	$values = get_values($names);
	$mysqli = new mysqli("localhost", "root");
	if ($mysqli) {
		$mysqli->set_charset("utf8");

		$mysqli->select_db(constant('DB_NAME'));

		$q = "INSERT INTO entries (";
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
			header("Location: show.php?entry_name=" . $values['entry_name']);
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
<title>Add Entry</title>
</head>
<body>
<form method="POST">
<input type="hidden" name="tepo" value="don" />
項目名: <input type="text" size="32" name="entry_name" /><br />
項目の種類: <input type="text" size="32" name="entry_type" /><br />
説明:<br />
<textarea rows="5" cols="55" name="description"></textarea><br />
文法:<br />
<textarea rows="5" cols="55" name="syntax"></textarea><br />
パラメーター:<br />
<textarea rows="5" cols="55" name="parameters"></textarea><br />
戻り値:<br />
<textarea rows="5" cols="55" name="return_value"></textarea><br />
解説:<br />
<textarea rows="5" cols="55" name="remarks"></textarea><br />
参照: <input type="text" size="32" name="see_also" /><br />
ヘッダー: <input type="text" size="32" name="header" /><br />
モジュール (複数可。'|'で区切る): <input type="text" size="32" name="modules" /><br />
情報源:<br />
<textarea rows="5" cols="55" name="sources"></textarea><br />
翻訳者:<br />
<textarea rows="5" cols="55" name="translators"></textarea><br />
ROSにおける所在:<br />
<textarea rows="5" cols="55" name="ros_location"></textarea><br />
分析:<br />
<textarea rows="5" cols="55" name="analysis"></textarea><br />
前のページ: <input type="text" size="32" name="prev_page" /><br />
次のページ: <input type="text" size="32" name="next_page" /><br />
<input type="submit" value="送信" />
</form>
<p>
	<a href="entries.php">項目一覧</a>
</p>
</body></html>

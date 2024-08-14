<?php

define('DB_NAME', "imehackerz_ja");
define('LANG', "ja");
define('LANGNAME', "Japanese");
define('ALTLANG', "en");
define('ALTLANGNAME', "English");
define('PARAM_SEP', "///");
define('PARAM_DASH', "---");
define('TYPE_ARTICLE', '');
define('TYPE_FUNCTION', '関数');
define('TYPE_MESSAGE', 'メッセージ');
define('TYPE_NOTIF_CODE', '通知コード');
define('TYPE_ACTION', 'アクション');

function imehack_translate($mysqli, $original)
{
	$q = "SELECT translated FROM translation WHERE original = '";
	$q .= $mysqli->real_escape_string($original);
	$q .= "'";
	$result = $mysqli->query($q);
	if ($result) {
		if ($row = $result->fetch_row()) {
			return $row[0];
		}
	}
	return $original;
}

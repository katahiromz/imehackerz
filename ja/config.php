<?php

define('DB_NAME', "imehackerz_ja");
define('LANG', "ja");
define('LANGNAME', "Japanese");
define('ALTLANG', "en");
define('ALTLANGNAME', "English");
define('PARAM_SEP', "///");

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

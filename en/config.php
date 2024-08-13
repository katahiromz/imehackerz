<?php

define('DB_NAME', "imehackerz");
define('LANG', "en");
define('LANGNAME', "English");
define('ALTLANG', "ja");
define('ALTLANGNAME', "Japanese");

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

<?php

include('config.php');

header("Content-Type: text/html");

function translate($text) {
	global $translation;
	if (isset($translation) && isset($translation[$text])) {
		return $translation[$text];
	}
	return $text;
}

function get_values($values) {
    $array = array();
    foreach ($values as $value) {
        if (isset($_POST[$value])) {
            $array[$value] = $_POST[$value];
        } else if (isset($_GET[$value])) {
            $array[$value] = $_GET[$value];
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

function replace_table_callback($matches) {
	return get_table($matches[1]);
}

function get_table($table_name) {
	$names = array(
		'contents'
	);
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
		$q .= $mysqli->real_escape_string($table_name);
		$q .= "'";
		$result = $mysqli->query($q);
		if ($result) {
			$row = $result->fetch_row();
		} else {
			echo $mysqli->error;
		}

		$mysqli->close();
		
		if ($row) {
			$value = $row[0];
			$lines = explode("///", $value);
			$a = array();
			foreach ($lines as $line) {
				$a[] = explode(" --- ", $line);
			}
			$str = "<table border=\"1\" align=\"center\">\n";
			$i = 0;
			foreach ($a as $item) {
				$str .= "<tr\n>";
				if ($i == 0) {
					$str .= "\t<th>" . $item[0] . "</th>\n";
					$str .= "\t<th>" . $item[1] . "</th>\n";
				} else {
					$str .= "\t<td>" . $item[0] . "</td>\n";
					$str .= "\t<td>" . $item[1] . "</td>\n";
				}
				$str .= "</tr>\n";
				$i++;
			}
			$str .= "</table>\n";
			$str = preg_replace_callback('/\[[A-Za-z0-9_]+\]/m', "replace_table_callback", $str);
			return $str;
		}
	} else {
		echo "cannot connect";
	}
	return $table;
}

function get_paragraph($field) {
	$field = preg_replace('/\r\n/', "\n", $field);
	$field = preg_replace('/\n\n\n+/', "\n\n", $field);
	$field = preg_replace('/\n +\n/', "\n\n", $field);
	$paragraphs = explode("\n\n", $field);
	$array = array();
	foreach ($paragraphs as $paragraph) {
		if (preg_match('/^ *\* /', $paragraph)) {
			$items = preg_split('/(^|\n) *\*/', $paragraph);
			array_shift($items);
			$paragraph = "<ul>\n";
			foreach ($items as $item) {
				$paragraph .= "<li>";
				$paragraph .= $item;
				$paragraph .= "</li>\n";
			}
			$paragraph .= "</ul>\n";
		} else if (preg_match('/ *^\d+\. /m', $paragraph)) {
			$items = preg_split('/(^|\n) *\d+\./', $paragraph);
			array_shift($items);
			$paragraph = "<ol>\n";
			foreach ($items as $item) {
				$paragraph .= "<li>";
				$paragraph .= $item;
				$paragraph .= "</li>\n";
			}
			$paragraph .= "</ol>\n";
		} else if (preg_match('/\[table:([^\]]+)\]/m', $paragraph, $matches)) {
			$paragraph = get_table($matches[1]);
		} else if (preg_match('/&lt;pre&gt;/m', $paragraph, $matches) &&
                   preg_match('/&lt;\/pre&gt;/m', $paragraph, $matches)) {
            $paragraph = str_replace("&lt;pre&gt;", "", $paragraph);
            $paragraph = str_replace("&lt;/pre&gt;", "", $paragraph);
            $paragraph = "<pre>" . $paragraph . "</pre>\n";
		} else {
			$paragraph = "<p>" . $paragraph . "</p>\n";
		}
		$array[] = $paragraph;
	}
	return implode("\n", $array);
}

function get_parameters($name) {
	$value = get_field($name);
	if (strstr($value, "---") === FALSE) {
		return $value;
	}
	$lines = explode("///", $value);
	$a = array();
	foreach ($lines as $line) {
		$a[] = explode(" --- ", $line);
	}
	$str = "<dl>\n";
	foreach ($a as $item) {
		$str .= "<dt>" . $item[0] . "</dt>\n";
		$str .= "<dd>" . get_paragraph($item[1]) . "</dd>\n";
	}
	$str .= "</dl>\n";
	return $str;
}

function get_sources($name) {
	$sources = get_field($name);
	return preg_replace('/^((https?|ftp)(:\/\/[-_.!~*\'()a-zA-Z0-9;\/?:\@&=+\$,%#]+))$/', '<a href="${1}" target="_blank">${1}</a>', $sources);
}

function get_see_also() {
	$see_also = get_field("see_also");
	if ($see_also) {
		$see_also = explode("|", $see_also);
		$a = array();
		foreach ($see_also as $item) {
			$item = trim($item);
			$a[] = "<a href=\"show_entry.php?entry_name=" . $item . "\">" . $item . "</a>";
		}
		return implode(", ", $a);
	}
	return "";
}

function replace_keyword($text) {
	$see_also = get_field("see_also");
	if ($see_also) {
		$see_also = explode("|", $see_also);
		sort($see_also);
		$see_also = array_reverse($see_also);
		foreach ($see_also as $item) {
			$item = trim($item);
			$text = preg_replace("/\\b" . $item . "\\b/",
				'<a href="show_entry.php?entry_name=' . $item . '">' . $item . "</a>",
				$text
			);
		}
	}
	return $text;
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

$mysqli = new mysqli("localhost", "root");
if ($mysqli) {
	$mysqli->set_charset("utf8");

	$mysqli->select_db(constant('DB_NAME'));

	$translation = array();
	$q = "SELECT original, translated FROM translation";
	$result = $mysqli->query($q);
	if ($result) {
		while ($row = $result->fetch_row()) {
			$translation[$row[0]] = $row[1];
		}
	}

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

?><html><head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title><?= get_field("entry_name") ?></title>
</head>
<body>
<h1><?= translate(get_field("entry_name")) . " " . get_field("entry_type") ?></h1>

<?= replace_keyword(get_paragraph(get_field("description"))) ?>

<pre><?= get_field("syntax") ?></pre>

<h2>Parameters</h2>

<?= replace_keyword(get_parameters("parameters"))  ?>

<h2>Return Value</h2>

<?= replace_keyword(get_paragraph(get_field("return_value"))) ?>

<h2>Remarks</h2>

<?= replace_keyword(get_paragraph(get_field("remarks"))) ?>

<h2>See Also</h2>

<p>
	<?= get_see_also() ?>
</p>

<table border="1" align="center">
	<tr>
		<th>Header</th>
		<td><?= get_field("header") ?></td>
	</tr>
	<tr>
		<th>Modules</th>
		<td><?= get_field("modules") ?></td>
	</tr>
</table>

<h2>Info Sources</h2>

<p>
	<?= get_sources("sources") ?>
</p>

<h2>Translators</h2>

<p>
	<?= get_sources("translators") ?>
</p>

<h2>Location on ROS</h2>

<p>
	<?= get_field("ros_location") ?>
</p>

<h2>Analysis</h2>

<?= get_field("analysis") ?>

<h2>Previous Page</h2>

<?= get_field("prev_page") ?>

<h2>Next Page</h2>

<?= get_field("next_page") ?>

<p>
	<a href="entries.php">Entries</a> | 
	<a href="add_entry.php">Add Entry</a> | 
	<a href="add_table.php">Add Table</a> | 
	<a href="edit_entry.php?entry_name=<?= $_GET["entry_name"] ?>">Edit "<?= $_GET["entry_name"] ?>"</a>
</p>

</body></html>

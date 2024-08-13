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

function entry_name_to_file_name($entry_name) {
	$entry_name = str_replace(",", "", $entry_name);
	$entry_name = str_replace(" ", "-", $entry_name);
	$entry_name = str_replace("/", "-", $entry_name);
	return $entry_name;
}

function get_header($title, $entry_name) {
	return '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html><head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>' . $title . '</title>
<meta http-equiv="Content-Style-Type" content="text/css" />
<link rel="stylesheet" type="text/css" href="base.css" />
</head>
<body>
<div class="header">
    <h1><a href="index.html">IME Hackerz</a></h1>
    <ul class="menu clearFix">
        <li><a href="articles.html">Articles</a></li>
        <li><a href="functions.html">Functions</a></li>
        <li><a href="messages.html">Messages</a></li>
        <li><a href="structures.html">Structures</a></li>
        <li><a href="macros.html">Macros</a></li>
        <li><a href="../' . constant('ALTLANG') . '/' . entry_name_to_file_name($entry_name) . '.html">' . constant('ALTLANGNAME') . '</a></li>
    </ul>
</div>
<div class="contents">' . "\n";
}

function get_footer($entry_name) {
	return '</div>
<div class="footer">
    <small>&copy;Katayama Hirofumi MZ</small><br/>
	<small><a href="mailto:katayama.hirofumi.mz@gmail.com?subject=' . entry_name_to_file_name($entry_name) . '">Report the mistake of this page</a></small><br/>
	<small><a href="mailto:katayama.hirofumi.mz@gmail.com">katayama.hirofumi.mz@gmail.com</a></small>
</div>
</body></html>';
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
					if (count($item) == 3) {
						$str .= "\t<th>" . $item[2] . "</th>\n";
					}
				} else {
					$str .= "\t<td>" . $item[0] . "</td>\n";
					$str .= "\t<td>" . get_paragraph($item[1]) . "</td>\n";
					if (count($item) == 3) {
						$str .= "\t<td>" . get_paragraph($item[2]) . "</td>\n";
					}
				}
				$str .= "</tr>\n";
				$i++;
			}
			$str .= "</table>\n";
			$str = preg_replace_callback('/\[[A-Za-z0-9_]+\]/m', "replace_table_callback", $str);
			return $str;
		}
		echo "\nERROR: table $table_name !!!\n";
	} else {
		echo "cannot connect";
	}
	return "";
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
			$a[] = "<a href=\"" . entry_name_to_file_name($item) . ".html\">" . translate($item) . "</a>";
		}
		return implode(", ", $a);
	}
	return "";
}

function replace_keyword($text) {
	$see_also = get_field("see_also");
	$entry_name = get_field("entry_name");
	if ($see_also) {
		$see_also = explode("|", $see_also);
		sort($see_also);
		$see_also = array_reverse($see_also);
		$a = array();
		$i = 0;
		$left = "<<<";
		$right = ">>>";
		{
			$item = trim($entry_name);
			$a[] = '<b>' . translate($item) . "</b>";
			$text = str_replace(translate($item), 
				$left . $i . $right,
				$text);
			$i++;
		}
		foreach ($see_also as $item) {
			$item = trim($item);
			$a[] = '<a href="' . entry_name_to_file_name($item) . '.html">' . translate($item) . "</a>";
			$text = preg_replace("/\\b" . translate($item) . "\\b/",
				$left . $i . $right,
				$text);
			$i++;
		}
		$i = 0;
		{
			$text = str_replace($left . $i . $right, 
				$a[$i],
				$text
			);
			$i++;
		}
		foreach ($see_also as $item) {
			$text = str_replace($left . $i . $right, 
				$a[$i],
				$text
			);
			$i++;
		}
	}
	return $text;
}

function get_body($entry_type) {
	return 
	'<h2>' . translate(get_field("entry_name")) . ' ' . get_field("entry_type") . '</h2>' . "\n" .
	replace_keyword(get_paragraph(get_field("description"))) .
	'<pre>' . get_field("syntax") . '</pre>' . "\n" .
	(
		get_field("parameters") != '' ?
		(
			$entry_type == "structure" ?
			'<h2>Members</h2>' . "\n" :
			'<h2>Paramters</h2>' . "\n"
		) . 
		replace_keyword(get_parameters("parameters")) : ""
	) . (
		get_field("return_value") != '' ?
		'<h2>Return Value</h2>' . "\n" .
		replace_keyword(get_paragraph(get_field("return_value"))) :
		""
	) . (
		get_field("remarks") != '' ?
		'<h2>Remarks</h2>' . "\n" .
		replace_keyword(get_paragraph(get_field("remarks"))) :
		""
	) . (
		get_field("header") . get_field("modules") != '' ?
		'<table border="1" align="center" cellspacing="4">' . "\n" .
		'<tr>' . "\n" .
			'<th>Header</th>' . "\n" .
			'<td>' . (
				get_field('header') != '' ? get_field('header') : "(Unknown)"
			) . "</td>\n" .
		'</tr>' . "\n" .
		'<tr>' . "\n" .
			'<th>Modules</th>' . "\n" .
			'<td>' . (
				get_field('modules') != '' ? get_field('modules') : "(Unknown)"
			) . "</td>\n" .
		'</tr>' . "\n" .
		'</table>' . "\n" : ""
	) . (
		get_see_also() != '' ?
		'<h2>See Also</h2>' . "\n" .
		'<p>' . get_see_also() . '</p>' . "\n" :
		""
	) . (
		get_sources("sources") != '' ?
		'<h2>Info Sources</h2>' . "\n" . get_sources("sources") :
		""
	) . (
		get_field("translators") != '' ?
		'<h2>Translators</h2>' . "\n" . get_sources("translators") :
		""
	) . (
		(get_field("prev_page") . get_field("next_page")) != '' ?
		'<p>' . (
			get_field("prev_page") != '' ?
			'<a href="' . entry_name_to_file_name(get_field("prev_page")) . '.html">&lt;&lt;Previous Page</a> ' :
			""
		) . (
			get_field("next_page") != '' ?
			'<a href="' . entry_name_to_file_name(get_field("next_page")) . '.html">Next Page&gt;&gt;</a> ' :
			""
		) . '</p>' : ""
	);
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

set_time_limit(1000);

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
	$q .= " FROM entries";
	$result = $mysqli->query($q);
	if ($result) {
		$arrays = array();
		while ($assoc = $result->fetch_array(MYSQLI_ASSOC)) {
			$arrays[] = $assoc;
			$entry_name = $assoc['entry_name'];
			$entry_type = $assoc['entry_type'];
			echo $entry_name . "\n";
			$header = get_header($entry_name . ' ' . $entry_type, $entry_name);
			$body = get_body($entry_type);
			$footer = get_footer($entry_name);
			$fp = fopen(constant('LANG') . "/" . entry_name_to_file_name($entry_name) . ".html", "w");
			if ($fp) {
				fputs($fp, $header);
				fputs($fp, $body);
				fputs($fp, $footer);
				fclose($fp);
			}
		}

		$fp = fopen(constant('LANG') . "/articles.html", "w");
		if ($fp) {
			$header = get_header("Articles", "articles");
			fputs($fp, $header);
			fputs($fp, 
				"<h2>Articles</h2>\n" .
				"<ul>\n"
			);
			foreach ($arrays as $assoc) {
				$entry_name = $assoc['entry_name'];
				$entry_type = $assoc['entry_type'];
				if ($entry_type != '') continue;
				fputs($fp, '<li><a href="' . entry_name_to_file_name($entry_name) . '.html">' . translate($entry_name) . " " . $entry_type . '</a></li>' . "\n");
			}
			fputs($fp, 
				"</ul>\n"
			);
			$footer = get_footer("articles");
			fputs($fp, $footer);
			fclose($fp);
		}

		$fp = fopen(constant('LANG') . "/functions.html", "w");
		if ($fp) {
			$header = get_header("Functions", "functions");
			fputs($fp, $header);
			fputs($fp, 
				"<h2>Functions</h2>\n" .
				"<ul>\n"
			);
			foreach ($arrays as $assoc) {
				$entry_name = $assoc['entry_name'];
				$entry_type = $assoc['entry_type'];
				if ($entry_type != 'function') continue;
				fputs($fp, '<li><a href="' . entry_name_to_file_name($entry_name) . '.html">' . $entry_name . " " . $entry_type . '</a></li>' . "\n");
			}
			fputs($fp, 
				"</ul>\n"
			);
			$footer = get_footer('functions');
			fputs($fp, $footer);
			fclose($fp);
		}

		$fp = fopen(constant('LANG') . "/messages.html", "w");
		if ($fp) {
			$header = get_header("Messages", "messages");
			fputs($fp, $header);
			fputs($fp, 
				"<h2>Messages</h2>\n" .
				"<ul>\n"
			);
			foreach ($arrays as $assoc) {
				$entry_name = $assoc['entry_name'];
				$entry_type = $assoc['entry_type'];
				if ($entry_type != 'message' && $entry_type != 'notification code' && $entry_type != 'action') continue;
				fputs($fp, '<li><a href="' . entry_name_to_file_name($entry_name) . '.html">' . $entry_name . " " . $entry_type . '</a></li>' . "\n");
			}
			fputs($fp, 
				"</ul>\n"
			);
			$footer = get_footer('messages');
			fputs($fp, $footer);
			fclose($fp);
		}

		$fp = fopen(constant('LANG') . "/structures.html", "w");
		if ($fp) {
			$header = get_header("Structures", "structures");
			fputs($fp, $header);
			fputs($fp, 
				"<h2>Structures</h2>\n" .
				"<ul>\n"
			);
			foreach ($arrays as $assoc) {
				$entry_name = $assoc['entry_name'];
				$entry_type = $assoc['entry_type'];
				if ($entry_type != 'structure') continue;
				fputs($fp, '<li><a href="' . entry_name_to_file_name($entry_name) . '.html">' . $entry_name . " " . $entry_type . '</a></li>' . "\n");
			}
			fputs($fp, 
				"</ul>\n"
			);
			$footer = get_footer('structures');
			fputs($fp, $footer);
			fclose($fp);
		}

		$fp = fopen(constant('LANG') . "/macros.html", "w");
		if ($fp) {
			$header = get_header("Macros", "macros");
			fputs($fp, $header);
			fputs($fp, 
				"<h2>Macros</h2>\n" .
				"<ul>\n"
			);
			foreach ($arrays as $assoc) {
				$entry_name = $assoc['entry_name'];
				$entry_type = $assoc['entry_type'];
				if ($entry_type != 'macro') continue;
				fputs($fp, '<li><a href="' . entry_name_to_file_name($entry_name) . '.html">' . $entry_name . " " . $entry_type . '</a></li>' . "\n");
			}
			fputs($fp, 
				"</ul>\n"
			);
			$footer = get_footer('macros');
			fputs($fp, $footer);
			fclose($fp);
		}
	} else {
		echo $mysqli->error;
	}

	$mysqli->close();

	copy('index.html', constant('LANG') . "/index.html");
	copy('base.css', constant('LANG') . "/base.css");
	copy('../ime-api.pdf', constant('LANG') . "/ime-api.pdf");
	copy('../ime-api_ja.pdf', constant('LANG') . "/ime-api_ja.pdf");
	copy('../ime-overview.pdf', constant('LANG') . "/ime-overview.pdf");
	copy('../ime-overview_ja.pdf', constant('LANG') . "/ime-overview_ja.pdf");
} else {
	echo "cannot connect";
}

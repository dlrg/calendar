<?php declare(strict_types=1);

error_reporting(E_ALL);

require_once 'config.inc.php';
require_once 'CalDAVParser.class.php';

const TOK = '__%%__';

const TARGET_TIMEZONE = 'Europe/Berlin';
$calendars = array(
	'sport' => 'bvmxnqdz',
	'veranstaltungen' => 'frrbzoh',
	'gremien' => 'lthjrsx',
	'ljv' => 'uieyex'
);

$calendarKey = 'gremien';

$selectedCalendarID = $calendars[$calendarKey];

echo <<<EOD
<DOCTYPE html>
<html>
	<head>
		<title></title>
	</head>
	<body>
EOD;

try {
	$parser = new CalDAVParser(new SimpleCalDAVClient());
	$parser->connect(ENDPOINT, USERNAME, PASSWORD);

	echo <<<EOD
<table>
	<th><td>Name</td>
	<td>Beginn</td><td>Ende</td>
</th>
EOD;

	foreach ($parser->events($selectedCalendarID) as $e) {
		$mapping = [
			'SUMMARY' => $e->summary(),
			'DTSTART' => $e->startTime(),
			'DTEND'   => $e->endTime()
		];
		$row = '<tr><td>' . TOK . 'SUMMARY' . TOK . '</td><td>' . TOK . 'DTSTART' . TOK . '</td><td>' . TOK . 'DTEND' . TOK . '</td></tr>';
		foreach ($mapping as $key => $value) {
			$row = str_replace(TOK . $key . TOK, $value, $row);
		}
		echo $row;
	}
	echo '</table>';
} catch (Exception $e) {
	echo '<h1>' . $e->__toString() . '</h1>';
}

echo '</body></html>';

?>

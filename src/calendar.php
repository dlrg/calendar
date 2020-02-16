<?php declare(strict_types=1);

error_reporting(E_ALL);

require_once 'config.inc.php';
require_once 'CalDAVParser.class.php';

require_once 'HTMLRenderer.class.php';

const TARGET_TIMEZONE = 'Europe/Berlin';
$calendars = array(
	'sport' => 'bvmxnqdz',
	'veranstaltungen' => 'frrbzoh',
	'gremien' => 'lthjrsx',
	'ljv' => 'uieyex'
);

if ($_GET) {
	$calendarKey = $_GET['calendar'];
} else {
	$calendarKey = 'gremien';
}
if (empty($calendarKey)) $calendarKey = 'gremien';

$selectedCalendarID = $calendars[$calendarKey];

$renderer = new HTMLRenderer();

if ($selectedCalendarID == NULL) {
	$renderer->setError('Calendar not found ¯\_(ツ)_/¯');
} else {
	try {
		$parser = new CalDAVParser(function($base_url, $user, $pass) { return new CalDAVClient($base_url, $user, $pass); });
		$parser->connect(ENDPOINT, USERNAME, PASSWORD);
		$events = $parser->events($selectedCalendarID);
		usort($events, array("CalDAVParserEvent", "compare"));
		$renderer->setEvents($events);
	} catch (Exception $e) {
		$renderer->setError('An error occured :\'(');
	}
}

header('Content-Security-Policy: frame-ancestors *.dlrg.de');

echo $renderer->render();

?>

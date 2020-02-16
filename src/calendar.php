<?php declare(strict_types=1);

require_once 'config.inc.php';
require_once 'CalDAVParser.class.php';

require_once 'HTMLRenderer.class.php';
require_once 'ICSRenderer.class.php';

const TARGET_TIMEZONE = 'Europe/Berlin';
$calendars = array(
	'sport' => 'bvmxnqdz',
	'veranstaltungen' => 'frrbzoh',
	'gremien' => 'lthjrsx',
	'ljv' => 'uieyex'
);

if ($_GET) {
	$calendarKey = $_GET['calendar'];
	$download = $_GET['download'] == 1;
} else {
	$calendarKey = 'gremien';
	$download = false;
}
if (empty($calendarKey)) $calendarKey = 'gremien';

$selectedCalendarID = $calendars[$calendarKey];

if ($download) {
	$renderer = new ICSRenderer();
} else {
	$renderer = new HTMLRenderer();
}

if ($selectedCalendarID == NULL) {
	$renderer->setError('Calendar not found ¯\_(ツ)_/¯');
} else {
	try {
		$parser = new CalDAVParser(function($base_url, $user, $pass) { return new CalDAVClient($base_url, $user, $pass); });
		$parser->connect(ENDPOINT, USERNAME, PASSWORD);
		$events = $parser->events($selectedCalendarID);
		usort($events, array("CalDAVParserEvent", "compare"));
		$renderer->setEvents($calendarKey, $events);
	} catch (Exception $e) {
		$renderer->setError('An error occured :\'(');
	}
}

$renderer->render();

?>

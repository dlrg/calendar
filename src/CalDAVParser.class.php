<?php declare(strict_types=1);


use ICal\ICal;

require_once '../vendor/autoload.php';
require_once '../vendor/simpleCalDAV/SimpleCalDAVClient.php';


interface ICalDAVParserEvent {
	public function /** string **/ startTime();
	public function /** string **/ endTime();
	public function /** string **/ summary();
}

interface ICalDAVParser {
	public function connect($endpoint, $user, $pass);
	public function events($calendarID) /** [ICalDAVParserEvent] **/;
}

class CalDAVParserEvent implements ICalDAVParserEvent {
	private /** string **/ $_startTime;
	private /** string **/ $_endTime;
	private /** string **/ $_summary;

	public function __construct(/** string **/ $text) {
		$ics = new ICal();
		$ics->initString($text);

		$event = $ics->events()[0];

		$this->_startTime = CalDAVParserEvent::convertDate($event->dtstart_array);
		$this->_endTime = CalDAVParserEvent::convertDate($event->dtend_array);
		$this->_summary = $event->summary;
	}

	public function startTime() {
		return $this->_startTime;
	}

	public function endTime() {
		return $this->_endTime;
	}

	public function summary() {
		return $this->_summary;
	}

	private static function convertDate($dtarray) {
		$tz = $dtarray[0]['TZID'];
		if (empty($tz)) {
			$tz = TARGET_TIMEZONE;
		}
		$d = new DateTime($dtarray[1], new DateTimeZone($tz));
		$d->setTimezone(new DateTimeZone(TARGET_TIMEZONE));
		return $d->format('d.m.Y H:i');
	}
}

class CalDAVParser implements ICalDAVParser {
	private /** SimpleCalDAVClient **/ $client;
	private /** [CalDAVCalendar] **/ $calendars;

	public function __construct(/** SimpleCalDAVClient */$client) {
		$this->client = $client;
	}

	public function connect($endpoint, $user, $pass) {
		$this->client->connect($endpoint, $user, $pass);
		$this->calendars = $this->client->findCalendars();
	}

	public function events($calendarID) {
		if (!$this->calendars) {
			throw new InvalidArgumentException('Calendars is undefined');
		}

		$calendar = $this->calendars[$calendarID];
		if ($calendar == NULL) {
			throw new InvalidArgumentException('No such calendar: '. $calendarID);
		}

		$this->client->setCalendar($calendar);
		return array_map(function($e) { return new CalDAVParserEvent($e->getData()); }, $this->client->getEvents());
	}
}

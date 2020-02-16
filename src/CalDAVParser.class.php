<?php declare(strict_types=1);


use ICal\ICal;

require_once '../vendor/autoload.php';
require_once 'caldav-client-v2.php';


interface ICalDAVParserEvent {
	public function /** string **/ startTime();
	public function /** string **/ endTime();
	public function /** string **/ summary();
	public function /** string **/ location();
	public function /** string **/ vevent();
}

interface ICalDAVParser {
	public function connect($endpoint, $user, $pass);
	public function events($calendarID) /** [ICalDAVParserEvent] **/;
}

class CalDAVParserEvent implements ICalDAVParserEvent {
	private /** DateTime **/ $_startTime;
	private /** DateTime **/ $_endTime;
	private /** string **/ $_summary;
	private /** string **/ $_location;
	private /** string **/ $_vevent;

	const DATE_FMT = 'd.m.Y H:i';

	public function __construct(/** string **/ $text) {
		$ics = new ICal();
		$ics->initString($text);

		$event = $ics->events()[0];

		$this->_startTime = CalDAVParserEvent::convertDate($event->dtstart_array);
		$this->_endTime = CalDAVParserEvent::convertDate($event->dtend_array);
		$this->_summary = $event->summary;
		$this->_location = $event->location;
		$this->_vevent = $text . "\n";
	}

	public function startTime() {
		return $this->_startTime->format(CalDAVParserEvent::DATE_FMT);
	}

	public function endTime() {
		return $this->_endTime->format(CalDAVParserEvent::DATE_FMT);
	}

	public function summary() {
		return $this->_summary;
	}

	public function location() {
		return $this->_location;
	}

	public function vevent() {
		return $this->_vevent;
	}

	private static function convertDate($dtarray) {
		$tz = $dtarray[0]['TZID'];
		if (empty($tz)) {
			$tz = TARGET_TIMEZONE;
		}
		$d = new DateTime($dtarray[1], new DateTimeZone($tz));
		$d->setTimezone(new DateTimeZone(TARGET_TIMEZONE));
		return $d;
	}

	public static function compare($a, $b) {
		if ($a->_startTime < $b->_startTime) {
			return -1;
		} else if ($a->_startTime > $b->_startTime) {
			return 1;
		} else {
			return $a->_endTime < $b->_endTime;
		}
	}
}

class CalDAVParser implements ICalDAVParser {
	private /** (string, string, string) -> Client **/ $clientFactory;
	private /** CalDAVClient **/ $client;
	private /** string **/ $user;

	public function __construct(/** (string, string, string) -> Client **/$clientFactory) {
		$this->clientFactory = $clientFactory;
	}

	public function connect($endpoint, $user, $pass) {
		$this->user = $user;
		$this->client = ($this->clientFactory)($endpoint, $user, $pass);
	}

	public function events($calendarID) {
		if ($this->client == NULL || $this->user == NULL) {
			throw new InvalidArgumentException('Must call connect() first');
		}
		$url = '/caldav/' . $this->user . '/' . $calendarID;
		$events = $this->client->GetEvents(null, null, $url);
		return array_map(function($e) { return new CalDAVParserEvent($e['data']); }, $events);
	}
}

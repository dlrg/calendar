<?php declare(strict_types=1);


use ICal\ICal;

require_once '../vendor/autoload.php';
require_once 'caldav-client-v2.php';


interface ICalDAVParserEvent {
	public function /** string **/ startTime();
	public function /** string **/ endTime();
	public function /** string **/ summary();
	public function /** string **/ location();
}

interface ICalDAVParser {
	public function connect($endpoint, $user, $pass);
	public function events($calendarID) /** [ICalDAVParserEvent] **/;
}

class CalDAVParserEvent implements ICalDAVParserEvent {
	private /** string **/ $_startTime;
	private /** string **/ $_endTime;
	private /** string **/ $_summary;
	private /** string **/ $_location;

	public function __construct(/** string **/ $text) {
		$ics = new ICal();
		$ics->initString($text);

		$event = $ics->events()[0];

		$this->_startTime = CalDAVParserEvent::convertDate($event->dtstart_array);
		$this->_endTime = CalDAVParserEvent::convertDate($event->dtend_array);
		$this->_summary = $event->summary;
		$this->_location = $event->location;
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

	public function location() {
		return $this->_location;
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

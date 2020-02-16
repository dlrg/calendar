<?php

class ICSRenderer implements CalDAVEventTemplate, ErrorTemplate, Renderer {
	private /** string **/ $error;
	private /** string **/ $name;
	private /** ICalDAVEvent[] **/ $events;

	function setError(/** string **/ $error) {
		$this->error = $error;
		$this->events = NULL;
	}

	function setEvents(/** string **/ $name, /** ICalDAVEvent[] **/ $events) {
		$this->error = NULL;
		$this->name = $name;
		$this->events = $events;
	}

	function render() {
		header('Content-Disposition: attachment; filename="' . $this->name . '.ics"');
		header('Content-Type: text/calendar');
		echo array_reduce($this->events, 
			function($acc, $e) { 
				$line = preg_replace('~\R~u', "\r\n", $e->vevent());
				return $acc . $line;
			}, '');
	}
}

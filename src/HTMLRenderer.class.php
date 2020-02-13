<?php
interface Renderer {
	function render() /**: string **/;
}

interface ErrorTemplate{
	function setError(/** string **/ $error);
}

interface CalDAVEventTemplate {
	function setEvents(/** ICalDAVEvent[] **/ $events);
}

class HTMLRenderer implements CalDAVEventTemplate, ErrorTemplate, Renderer {
	private /** string **/ $error;
	private /** ICalDAVEvent[] **/ $events;

	const TOK = '__%%__';

	function setError(/** string **/ $error) {
		$this->error = $error;
		$this->events = NULL;
	}

	function setEvents(/** ICalDAVEvent[] **/ $events) {
		$this->error = NULL;
		$this->events = $events;
	}

	function render() {
		$out = '<!DOCTYPE html><html><head><title></title></head><body>';

		if ($this->error) {
		}
		$out .= '<table><tr><th>Name</th><th>Ort</th><th>Beginn</th><th>Ende</th></tr>';
		foreach ($this->events as $e) {
			$mapping = [
				'SUMMARY' => $e->summary(),
				'DTSTART' => $e->startTime(),
				'DTEND'   => $e->endTime(),
				'LOCATION'=> $e->location()
			];
			$row = '<tr><td>' . HTMLRenderer::TOK . 'SUMMARY' . HTMLRenderer::TOK . '</td><td>' . HTMLRenderer::TOK . 'LOCATION' . HTMLRenderer::TOK . '</td><td>' . HTMLRenderer::TOK . 'DTSTART' . HTMLRenderer::TOK . '</td><td>' . HTMLRenderer::TOK . 'DTEND' . HTMLRenderer::TOK . '</td></tr>';
			foreach ($mapping as $key => $value) {
				$row = str_replace(HTMLRenderer::TOK . $key . HTMLRenderer::TOK, $value, $row);
			}
			$out .= $row;
		}
		$out .= '</table>';
		$out .= '</body></html>';
		return $out;
	}

}
?>

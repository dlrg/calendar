<?php
interface Renderer {
	function render() /**: string **/;
}

interface ErrorTemplate{
	function setError(/** string **/ $error);
}

interface CalDAVEventTemplate {
	function setEvents(/** string **/ $name, /** ICalDAVEvent[] **/ $events);
}

class HTMLRenderer implements CalDAVEventTemplate, ErrorTemplate, Renderer {
	private /** string **/ $error;
	private /** string **/ $name;
	private /** ICalDAVEvent[] **/ $events;

	const TOK = '__%%__';

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
		$css_files = array(
			'https://sh.dlrg.de/typo3temp/assets/css/e35cdd6a3b.css',
			'https://tv.dlrg.de/global/layout/2014/css/screen.css',
			'style.css',
		);
		$out = '<!DOCTYPE html><html><head>';
		$out .= array_reduce($css_files, function($acc, $href) { return $acc . "<link rel='stylesheet' type='text/css' href='$href'>"; }, '');
		$out .= '</head><body>';

		if ($this->error) {
		}
		$out .= '<table class="ce-table stacktable"><tr><th>Name</th><th>Ort</th><th>Beginn</th><th>Ende</th></tr>';
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
		$out .= '<p style="text-align: right;"><a href="' . $_SERVER['REQUEST_URI'] . '&download=1">Kalender abonnieren</a></p>';
		$out .= '</body></html>';
		header('Content-Security-Policy: frame-ancestors *.dlrg.de');

		echo $out;
	}

}
?>

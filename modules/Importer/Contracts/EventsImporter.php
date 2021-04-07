<?php


namespace modules\Importer\Contracts;


interface EventsImporter {
	public function setEvents($events):EventsImporter;
}
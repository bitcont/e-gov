<?php

namespace Bitcont\EGov\Bulletin\Scraper;

use DateTime,
	Bitcont\EGov\Bulletin\Record,
	Bitcont\EGov\Bulletin\Document;


class ScrapedRecord
{

	/**
	 * Hash calculated to remove duplicates.
	 *
	 * @var string
	 */
	public $hash;

	/**
	 * Source url.
	 *
	 * @var string
	 */
	public $url;

	/**
	 * Title.
	 *
	 * @var string
	 */
	public $title;

	/**
	 * Department.
	 *
	 * @var string
	 */
	public $department;

	/**
	 * Category.
	 *
	 * @var string
	 */
	public $category;

	/**
	 * Issue identifier.
	 *
	 * @var string
	 */
	public $issueIdentifier;

	/**
	 * Originating subject.
	 *
	 * @var string
	 */
	public $originator;

	/**
	 * Target subject.
	 *
	 * @var string
	 */
	public $addressee;

	/**
	 * Beginning of display.
	 *
	 * @var DateTime
	 */
	public $showFrom;

	/**
	 * End of display.
	 *
	 * @var DateTime
	 */
	public $showTo;

	/**
	 * List of documents.
	 *
	 * @var ScrapedDocument[]
	 */
	public $documents = [];


	/**
	 * Creates Record instance from itself.
	 *
	 * @return Record
	 */
	public function getRecord()
	{
		$record = new Record;

		$record->hash = $this->hash;
		$record->url = $this->url;
		$record->title = $this->title;
		$record->department = $this->department;
		$record->category = $this->category;
		$record->issueIdentifier = $this->issueIdentifier;
		$record->originator = $this->originator;
		$record->addressee = $this->addressee;
		$record->showFrom = $this->showFrom;
		$record->showTo = $this->showTo;

		// documents
		foreach ($this->documents as $scrapedDocument) {
			$document = new Document($record);
			$document->fileName = $scrapedDocument->fileName;
			$document->url = $scrapedDocument->url;
		}

		return $record;
	}
}



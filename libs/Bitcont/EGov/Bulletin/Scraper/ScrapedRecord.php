<?php

namespace Bitcont\EGov\Bulletin\Scraper;

use DateTime;
use Bitcont\EGov\Bulletin\Record;
use Bitcont\EGov\Bulletin\Document;
use Bitcont\EGov\Gov\Municipality;


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
	 * @var string
	 */
	public $title;

	/**
	 * @var string
	 */
	public $description;

	/**
	 * @var string
	 */
	public $department;

	/**
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
	 * @var DateTime
	 */
	public $publishedFrom;

	/**
	 * @var DateTime
	 */
	public $publishedTo;

	/**
	 * @var ScrapedDocument[]
	 */
	public $documents = [];


	/**
	 * @param string $url Absolute URL for calculating hash
	 */
	public function __construct($url)
	{
		$this->url = $url;
		$this->hash = sha1($this->url);
	}


	/**
	 * Assembles Record instance from itself.
	 *
	 * @return Record
	 */
	public function getRecord(Municipality $municipality)
	{
		$record = new Record($municipality);

		$record->hash = $this->hash;
		$record->url = $this->url;
		$record->title = $this->title;
		$record->description = $this->description;
		$record->department = $this->department;
		$record->category = $this->category;
		$record->issueIdentifier = $this->issueIdentifier;
		$record->originator = $this->originator;
		$record->addressee = $this->addressee;
		$record->publishedFrom = $this->publishedFrom;
		$record->publishedTo = $this->publishedTo;

		// documents
		foreach ($this->documents as $scrapedDocument) {
			$document = new Document($record);
			$document->fileName = $scrapedDocument->fileName;
			$document->url = $scrapedDocument->url;
		}

		return $record;
	}
}



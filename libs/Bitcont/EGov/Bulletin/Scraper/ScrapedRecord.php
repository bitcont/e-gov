<?php

namespace Bitcont\EGov\Bulletin\Scraper;

use DateTime;


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
}



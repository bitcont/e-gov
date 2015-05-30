<?php

namespace Bitcont\EGov\Bulletin\Scraper\Scrapers\Praha;

use DateTime;
use DOMDocument;
use DomXPath;
use Kdyby\Curl\Request;
use Bitcont\EGov\Bulletin\Scraper\IScraper;
use Bitcont\EGov\Bulletin\Scraper\ScrapedRecord;
use Bitcont\EGov\Bulletin\Scraper\ScrapedDocument;



class Praha1 implements IScraper
{

	/**
	 * Resource base url.
	 *
	 * @var string
	 */
	const BASE_URL = 'http://www.praha1.cz/';

	/**
	 * @var string
	 */
	const HOMEPAGE = 'cps/rde/xchg/praha1new/xsl/uredni-deska.html';


	/**
	 * Returns scraped records.
	 *
	 * @return ScrapedRecord[]
	 */
	public function scrape()
	{
		$records = [];

		foreach ($this->scrapeRecordList() as $url) {
			$record = $this->scrapeRecord($url);
			$records[$record->hash] = $record;
		}

		return $records;
	}


	/**
	 * Returns URLs of individual records.
	 *
	 * @return string[]
	 */
	protected function scrapeRecordList()
	{
		$homepage = static::BASE_URL . static::HOMEPAGE;
		$itemUrls = [];

		$request = new Request($homepage);
		$response = $request->get()->getResponse();

		// parse response into DOM
		libxml_use_internal_errors(TRUE);
		$dom = new DOMDocument;
		$dom->loadHTML($response);
		libxml_clear_errors();
		libxml_use_internal_errors(FALSE);

		foreach ($dom->getElementsByTagName('h3') as $h3) {
			$itemUrls[] = static::BASE_URL . $h3->getElementsByTagName('a')->item(0)->getAttribute('href');
		}

		return $itemUrls;
	}


	/**
	 * @param string $url
	 * @return ScrapedRecord
	 */
	protected function scrapeRecord($url)
	{
		$request = new Request($url);
		$response = $request->get()->getResponse();

		// parse response into DOM
		$dom = new DOMDocument;
		$dom->loadHTML($response);
		$xpath = new DomXPath($dom);

		$detail = $xpath->query("//div[contains(@class, 'udDetail')]")->item(0);
		$title = $detail->getElementsByTagName('h1')->item(0)->nodeValue;

		$info = $xpath->query($detail->getNodePath() . "//div[contains(@class, 'data')]");

		$record = new ScrapedRecord($url);
		$record->title = trim($title);
		$record->department = trim($info->item(0)->nodeValue);
		$record->category = trim($info->item(1)->nodeValue);
		$record->publishedFrom = new DateTime($info->item(2)->nodeValue);
		$record->publishedTo = new DateTime($info->item(3)->nodeValue);


		// documents
		$links = $xpath->query($detail->getNodePath() . "//div[contains(@class, 'docList')]//div[contains(@class, 'file')]//a");
		foreach ($links as $link) {

			// remove file size from filename
			$fileName = trim($link->getAttribute('title'));
			$fileName = preg_replace('/\s-\s\d+KB$/i', '', $fileName);

			$document = new ScrapedDocument;
			$document->fileName = $fileName;
			$document->url = trim($link->getAttribute('href'));
			$record->documents[] = $document;
		}

		return $record;
	}





//	/**
//	 * Trim string and remove whitespaces from within the string.
//	 *
//	 * @param string $string
//	 * @return string
//	 */
//	protected static function clearWhitespaces($string)
//	{
//		return preg_replace('/\s+/', ' ', trim($string));
//	}
//
//
//	/**
//	 * Prepare the string to be saved to DB.
//	 *
//	 * @param string $string
//	 * @return string
//	 */
//	protected static function fix($string)
//	{
//		return Encoding::fixUTF8(static::clearWhitespaces($string));
//	}
}



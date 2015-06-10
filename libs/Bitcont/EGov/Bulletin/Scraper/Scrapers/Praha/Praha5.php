<?php

namespace Bitcont\EGov\Bulletin\Scraper\Scrapers\Praha;

use DateTime;
use DOMDocument;
use DomXPath;
use Kdyby\Curl\Request;
use Bitcont\EGov\Bulletin\Scraper\IScraper;
use Bitcont\EGov\Bulletin\Scraper\ScrapedRecord;
use Bitcont\EGov\Bulletin\Scraper\ScrapedDocument;



class Praha5 implements IScraper
{

	/**
	 * Resource base url.
	 *
	 * @var string
	 */
	const BASE_URL = 'http://www.praha5.cz';

	/**
	 * @var string
	 */
	const HOMEPAGE = '/cs/sekce/uredni-deska/';


	/**
	 * Returns scraped records.
	 *
	 * @return ScrapedRecord[]
	 */
	public function scrape()
	{
		$homepage = static::BASE_URL . static::HOMEPAGE;

		$request = new Request($homepage);
		$response = $request->get()->getResponse();

		// parse response into DOM
		libxml_use_internal_errors(TRUE);
		$dom = new DOMDocument;
		$dom->loadHTML($response);
		libxml_clear_errors();
		libxml_use_internal_errors(FALSE);

		$xpath = new DomXPath($dom);
		$departmentNodes = $xpath->query("//li[contains(@class, 'root-item')]");

		$records = [];

		foreach ($departmentNodes as $departmentNode) {
			$department = $departmentNode->nodeValue;
			$itemList = $xpath->query($departmentNode->getNodePath() . "/following-sibling::ul")->item(0);

			foreach ($itemList->getElementsByTagName('li') as $li) {
				$url = $li->getElementsByTagName('a')->item(0)->getAttribute('href');

				$record = $this->scrapeRecord(static::BASE_URL . $url);
				$record->department = trim($department);

				$records[$record->hash] = $record;
			}
		}

		return $records;
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
		libxml_use_internal_errors(TRUE);
		$dom = new DOMDocument;
		$dom->loadHTML($response);
		libxml_clear_errors();
		libxml_use_internal_errors(FALSE);

		$xpath = new DomXPath($dom);
		$contentNode = $xpath->query("//div[contains(@class, 'middle')]")->item(0);

		$record = new ScrapedRecord($url);
		$record->title = trim($contentNode->getElementsByTagName('h2')->item(0)->nodeValue);

		$h2 = $contentNode->getElementsByTagName('h2')->item(0);
		$descriptionParagraphs = $xpath->query($h2->getNodePath() . "/following-sibling::p");

		if ($descriptionParagraphs->length === 2) {
			$record->description = trim($descriptionParagraphs->item(1)->nodeValue);
		}

		$docInfoNode = $xpath->query("//div[contains(@class, 'document-info')]")->item(0);
		$half = explode('Publikováno: ', $docInfoNode->nodeValue);
		$quater = explode(',', $half[1]);
		$published = new DateTime($quater[0]);
		$rest = explode(' ', $quater[1]);
		$lastChanged = new DateTime(end($rest));
		$record->publishedFrom = $published > $lastChanged ? $published : $lastChanged;

		// documents
		$items = $xpath->query($contentNode->getNodePath() . "//div[contains(@class, 'attachment-box')]//li");
		foreach ($items as $li) {
			$fileName = trim($li->nodeValue);
			if (!$fileName) continue;

			$document = new ScrapedDocument;
			$document->fileName = trim($li->nodeValue);
			$document->url = static::BASE_URL . trim($li->getElementsByTagName('a')->item(0)->getAttribute('href'));
			$record->documents[] = $document;
		}

		return $record;
	}
}



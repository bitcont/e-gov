<?php

namespace Bitcont\EGov\Bulletin\Scraper\Scrapers\Praha;

use DateTime;
use DOMDocument;
use DomXPath;
use Kdyby\Curl\Request;
use Bitcont\EGov\Bulletin\Scraper\IScraper;
use Bitcont\EGov\Bulletin\Scraper\ScrapedRecord;
use Bitcont\EGov\Bulletin\Scraper\ScrapedDocument;



class Praha6 implements IScraper
{

	/**
	 * Resource base url.
	 *
	 * @var string
	 */
	const BASE_URL = 'http://www.praha6.cz/tabule';

	/**
	 * Resource base url.
	 *
	 * @var string
	 */
	const LISTPAGE = '/?vynechat=';

	/**
	 * Results per page. Only 25 is allowed.
	 *
	 * @var int
	 */
	const STEP = 25;


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
		$listPageBase = static::BASE_URL . static::LISTPAGE;
		$itemUrls = [];
		$offset = 0;


		do {
			$listPage = $listPageBase . $offset;


			echo $listPage;

			$request = new Request($listPage);
			$response = $request->get()->getResponse();


			echo " x $response x ";


			// parse response into DOM
			$dom = new DOMDocument;
			$dom->loadHTML($response);


			echo " a ";

			echo $dom->nodeValue;



//			foreach ($dom->getElementsByTagName('h3') as $h3) {
//				$itemUrls[] = static::BASE_URL . $h3->getElementsByTagName('a')->item(0)->getAttribute('href');
//			}



			$offset = $offset + static::STEP;

			die();

		} while (1 === 2);






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
}



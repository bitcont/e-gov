<?php

namespace Bitcont\EGov\Bulletin\Scraper;

use DateTime,
	Kdyby\Curl\Request,
	DOMDocument;


class Praha2
{

	/**
	 * Resource base url.
	 *
	 * @var string
	 */
	const BASE_URL = 'http://82.208.47.250:8080/eDeska/';

	/**
	 * Homepage.
	 *
	 * @var string
	 */
	const HOMEPAGE = 'eDeskaAktualni.jsp';

	/**
	 * Results per page. Allowed values: 25, 50, 100.
	 *
	 * @var int
	 */
	const STEP = 50;


	/**
	 * Returns scraped records.
	 *
	 * @return ScrapedRecord[]
	 */
	public function scrape()
	{
		$homepage = static::BASE_URL . static::HOMEPAGE;
		$offset = 0;
		$step = static::STEP;
		$records = [];

		do {
			$postData = [
				'order' => 'vyveseno',
				'desc' => TRUE,
				'first' => $offset,
				'count' => $step
			];

			$request = new Request($homepage);
			$response = $request->post(http_build_query($postData))->getResponse();


			// parse DOM
			$dom = new DOMDocument;
			$dom->loadHTML($response);
			$table = $dom->getElementsByTagName('table')->item(3);

			$recordsPage = [];
			$i = 0;
			foreach ($table->getElementsByTagName('tr') as $tr) {

				// skip first two rows
				if ($i < 2) {
					$i++;
					continue;
				}

				$tds = $tr->getElementsByTagName('td');
				$itemUrl = static::BASE_URL . static::clearWhitespaces($tds->item(2)->getElementsByTagName('a')->item(0)->getAttribute('href'));
				$itemHash = sha1($itemUrl);

				$record = new ScrapedRecord;
				$record->hash = $itemHash;
				$record->url = $itemUrl;
				$record->title = trim($tds->item(2)->nodeValue);
				$record->department = trim($tds->item(0)->nodeValue);
				$record->category = trim($tds->item(1)->nodeValue);
				$record->issueIdentifier = trim($tds->item(3)->nodeValue);
				$record->originator = static::clearWhitespaces($tds->item(4)->nodeValue);
				$record->addressee = static::clearWhitespaces($tds->item(5)->nodeValue);


				// dates



				$record->showFrom = new DateTime(trim($tds->item(6)->nodeValue));
				$record->showTo = new DateTime(trim($tds->item(7)->nodeValue));



				// files
				foreach ($tds->item(8)->getElementsByTagName('a') as $a) {
					$document = new ScrapedDocument;
					$document->title = static::clearWhitespaces($a->nodeValue);
					$document->url = static::BASE_URL . $a->getAttribute('href');
					$record->documents[] = $document;
				}

				$recordsPage[$itemHash] = $record;

			}

			$records = array_merge($records, $recordsPage);
			$offset += $step;

		} while (count($recordsPage) === $step);

		// remove hash keys & return
		return array_values($records);
	}


	/**
	 * Trim string and remove whitespaces from within the string.
	 *
	 * @param string $string
	 * @return string
	 */
	protected static function clearWhitespaces($string) {
		return preg_replace('/\s+/', ' ', trim($string));
	}
}



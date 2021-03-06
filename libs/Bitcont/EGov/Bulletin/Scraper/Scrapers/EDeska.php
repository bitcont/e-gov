<?php

namespace Bitcont\EGov\Bulletin\Scraper\Scrapers;

use DateTime;
use Kdyby\Curl\Request;
use ForceUTF8\Encoding;
use DOMDocument;
use Bitcont\EGov\Bulletin\Scraper\IScraper;
use Bitcont\EGov\Bulletin\Scraper\ScrapedRecord;
use Bitcont\EGov\Bulletin\Scraper\ScrapedDocument;


abstract class EDeska implements IScraper
{

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
				$itemUrl = static::BASE_URL . static::fix($tds->item(2)->getElementsByTagName('a')->item(0)->getAttribute('href'));

				$record = new ScrapedRecord($itemUrl);
				$record->title = trim($tds->item(2)->nodeValue);
				$record->department = trim($tds->item(0)->nodeValue);
				$record->category = trim($tds->item(1)->nodeValue);
				$record->issueIdentifier = trim($tds->item(3)->nodeValue);
				$record->originator = static::fix($tds->item(4)->nodeValue);
				$record->addressee = static::fix($tds->item(5)->nodeValue);
				$record->publishedFrom = new DateTime(trim($tds->item(6)->nodeValue));
				$record->publishedTo = new DateTime(trim($tds->item(7)->nodeValue));

				// attachments
				foreach ($tds->item(8)->getElementsByTagName('a') as $a) {
					$document = new ScrapedDocument;
					$document->fileName = static::fix($a->nodeValue);
					$document->url = static::BASE_URL . $a->getAttribute('href');
					$record->documents[] = $document;
				}

				$recordsPage[$record->hash] = $record;

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
	protected static function clearWhitespaces($string)
	{
		return preg_replace('/\s+/', ' ', trim($string));
	}


	/**
	 * Prepare the string to be saved to DB.
	 *
	 * @param string $string
	 * @return string
	 */
	protected static function fix($string)
	{
		return Encoding::fixUTF8(static::clearWhitespaces($string));
	}
}



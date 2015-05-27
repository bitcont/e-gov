<?php

namespace Bitcont\EGov\Bulletin\Scraper;

use Bitcont\EGov\Gov\Municipality;


interface IScraper
{

	/**
	 * Returns scraped records.
	 *
	 * @return ScrapedRecord[]
	 */
	public function scrape();


//	/**
//	 * Returns TRUE if applicable to given municipality.
//	 *
//	 * @return bool
//	 */
//	public function isApplicable(Municipality $municipality);

}



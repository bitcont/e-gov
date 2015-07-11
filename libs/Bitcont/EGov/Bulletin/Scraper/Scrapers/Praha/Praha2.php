<?php

namespace Bitcont\EGov\Bulletin\Scraper\Scrapers\Praha;

use Bitcont\EGov\Bulletin\Scraper\Scrapers\EDeska;


class Praha2 extends EDeska
{

	/**
	 * Resource base url.
	 *
	 * @var string
	 */
	const BASE_URL = 'http://85.207.99.7:8080/eDeska/';

	/**
	 * @var string
	 */
	const HOMEPAGE = 'eDeskaAktualni.jsp';

	/**
	 * Results per page. Allowed values: 25, 50, 100.
	 *
	 * @var int
	 */
	const STEP = 50;
}



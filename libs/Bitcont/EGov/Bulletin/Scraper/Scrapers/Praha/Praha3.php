<?php

namespace Bitcont\EGov\Bulletin\Scraper\Scrapers\Praha;

use Bitcont\EGov\Bulletin\Scraper\Scrapers\EDeska;


class Praha3 extends EDeska
{

	/**
	 * Resource base url.
	 *
	 * @var string
	 */
	const BASE_URL = 'http://www.praha3.cz/eDeska/';

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



<?php

namespace App\Presenters;

use Nette\Application\Responses\TextResponse,
	Nette\DI\Container,
	Google_Client,
	Google_Service_Drive,
	Google_Service_Drive_DriveFile,
	Google_Service_Drive_ParentReference,
	Kdyby\Curl\Request,
	Kdyby\CurlCaBundle\CertificateHelper,
	Doctrine\ORM\EntityManager,
	Bitcont\EGov\Bulletin\Record,
	Bitcont\EGov\Bulletin\Document,
	Masterminds\HTML5,
	DOMDocument,
	Nette\Utils\Strings,
	Bitcont\Google\Drive,
	Bitcont\EGov\Bulletin\Scraper\Scrapers\Praha\Praha1,
	Bitcont\EGov\Bulletin\Harvester;


class TestPresenter extends BasePresenter
{

	public function renderScrape()
	{

		$scraper = new Praha1;
		$records = $scraper->scrape();
		\Tracy\Debugger::dump($records);


		$this->sendResponse(new TextResponse(' scrape '));
	}
}

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
	Doctrine\ODM\MongoDB\DocumentManager,
	Doctrine\ORM\EntityManager,
	Bitcont\EGov\Bulletin\Record,
	Bitcont\EGov\Bulletin\Document,
	Masterminds\HTML5,
	DOMDocument,
	Nette\Utils\Strings,
	Bitcont\Google\Drive,
	Bitcont\EGov\Bulletin\Scraper\Praha2,
	Bitcont\EGov\Bulletin\Harvester;


class TestPresenter extends BasePresenter
{

	/**
	 * @var \Nette\DI\Container
	 */
	protected $container;

	/**
	 * @var \Doctrine\ODM\MongoDB\DocumentManager
	 */
	protected $documentManager;

	/**
	 * @var \Doctrine\ORM\EntityManager
	 */
	protected $entityManager;


	public function renderDefault()
	{
		$params = $this->container->getParameters();
		$fileName = 'drazebni_vyhlaska_c.j._39636-2015.pdf';
		$filePath = __DIR__ . "/../../resources/google/$fileName";

		$drive = new Drive($params['google']['accountFile']);
		$uploadedFile = $drive->upload($filePath, $params['google']['folderId']);
		$plainText = $drive->getPlainText($uploadedFile);


		\Tracy\Debugger::dump($plainText);



		$this->sendResponse(new TextResponse(''));
	}


	public function renderHarvest()
	{
		$params = $this->container->getParameters();
		$em = $this->entityManager;
		$drive = new Drive($params['google']['accountFile'], $params['google']['folderId']);


		$scraper = new Praha2;
		$records = $scraper->scrape();
		$scrapedRecord = reset($records);




		$harvester = new Harvester($em, $drive);
		$record = $harvester->harvest($scrapedRecord);



		\Tracy\Debugger::dump($record);

// https://googledrive.com/host/0B3HFawTFS6HFfnAtUkNqTHUwTTRQd3dOcTBCUmNidFVHVkJxdHpQeVV0ZzZXXzV3S1Uxamc/1_15-403.pdf


		https://googledrive.com/host/0B3HFawTFS6HFfmdjX2duclprdVRMZmlsVy1HWlByaGx1UzNnYlpxWmJaOEpJcjBrZEh1dm8/1_15-403.pdf

//		https://drive.google.com/folderview?id=0B3HFawTFS6HFfmdjX2duclprdVRMZmlsVy1HWlByaGx1UzNnYlpxWmJaOEpJcjBrZEh1dm8&usp=sharing



		$this->sendResponse(new TextResponse(' har '));
	}


	public function renderTransport()
	{
		$params = $this->container->getParameters();

		$url = "http://82.208.47.250:8080/eDeska/download.jsp?idPriloha=7343";
		$title = "15-403.pdf";


		$tmpFile = tmpfile();
		$filePath = stream_get_meta_data($tmpFile)['uri'];
		stream_copy_to_stream(fopen($url, 'r'), $tmpFile);


		$drive = new Drive($params['google']['accountFile']);
		$uploadedFile = $drive->upload($filePath, $params['google']['folderId'], $title);
		$plainText = $drive->getPlainText($uploadedFile);



		\Tracy\Debugger::dump($plainText);



		$this->sendResponse(new TextResponse(' trans '));
	}


	public function renderDoctrine()
	{
		$dm = $this->documentManager;




		$this->sendResponse(new TextResponse(''));
	}


	public function renderOrm()
	{
		$em = $this->entityManager;

		$board = new Board;
		$board->title = 'Necum';
		$board->camelCase = 'camel';

		$em->persist($board);
		$em->flush();


		$this->sendResponse(new TextResponse(' orm '));
	}


	public function renderScrape()
	{

		$scraper = new Praha2;
		$records = $scraper->scrape();
		\Tracy\Debugger::dump($records);


		$this->sendResponse(new TextResponse(' scrape '));
	}


	public function renderDoc()
	{
		$em = $this->entityManager;


		$record = $em->getRepository('Bitcont\EGov\Bulletin\Record')->findOneBy(['hash' => '8d54858f208e9cd0324b112ebaefa83c94d52c88']);



		\Tracy\Debugger::dump($record->title);






		$this->sendResponse(new TextResponse(' doc '));
	}


	protected static function clearWhitespaces($string) {
		return preg_replace('/\s+/', ' ', trim($string));
	}


	public function injectContainer(Container $container) {
		$this->container = $container;
	}


	public function injectDocumentManager(DocumentManager $documentManager) {
		$this->documentManager = $documentManager;
	}


	public function injectDoctrine(EntityManager $entityManager) {
		$this->entityManager = $entityManager;
	}

}

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
	Bitcont\Google\Drive;


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
		$url = 'http://82.208.47.250:8080/eDeska/';
		$homepage = $url . 'eDeskaAktualni.jsp';
		$offset = 0;
		$step = 50;
		$results = [];

		do {
			$postData = [
				'order' => 'vyveseno',
				'desc' => TRUE,
				'first' => $offset,
				'count' => $step
			];


			$request = new Request($homepage);
			$response = $request->post(http_build_query($postData))->getResponse();

			$dom = new DOMDocument;
			$dom->loadHTML($response);

			$table = $dom->getElementsByTagName('table')->item(3);


			$resultsPage = [];
			$i = 0;
			foreach ($table->getElementsByTagName('tr') as $tr) {

				// skip first two rows
				if ($i < 2) {
					$i++;
					continue;
				}

				$tds = $tr->getElementsByTagName('td');
				$itemUrl = $url . static::clearWhitespaces($tds->item(2)->getElementsByTagName('a')->item(0)->getAttribute('href'));
				$itemHash = sha1($itemUrl);

				$result = [
					'hash' => $itemHash,
					'url' => $itemUrl,
					'title' => trim($tds->item(2)->nodeValue),
					'department' => trim($tds->item(0)->nodeValue),
					'category' => trim($tds->item(1)->nodeValue),
					'issueIdentifier' => trim($tds->item(3)->nodeValue),
					'originator' => static::clearWhitespaces($tds->item(4)->nodeValue),
					'addressee' => static::clearWhitespaces($tds->item(5)->nodeValue),
					'showFrom' => trim($tds->item(6)->nodeValue),
					'showTo' => trim($tds->item(7)->nodeValue),
					'docs' => []
				];

				// files
				foreach ($tds->item(8)->getElementsByTagName('a') as $a) {
					$result['docs'][] = [
						'title' => static::clearWhitespaces($a->nodeValue),
						'url' => $url . $a->getAttribute('href')
					];
				}

				$resultsPage[$itemHash] = $result;

			}

			$results = array_merge($results, $resultsPage);
			$offset += $step;

		} while (count($resultsPage) === $step);

		// remove hash keys
		$results = array_values($results);


//		\Tracy\Debugger::dump($results);
		\Tracy\Debugger::dump(count($results));




		$em = $this->entityManager;

		foreach ($results as $result) {
			$record = $em->getRepository('Bitcont\EGov\Bulletin\Record')->findBy(['hash' => $result['hash']]);
			if ($record) {
				continue;
			}


			$record = new Record;
			$record->hash = $result['hash'];
			$record->title = $result['title'];

			$em->persist($record);

			foreach ($result['docs'] as $doc) {
				$document = new Document($record);
				$document->title = $doc['title'];
				$document->url = $doc['url'];

				$em->persist($document);
			}


			break; // debug
		}

		$em->flush();



		\Tracy\Debugger::dump($record->title);






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

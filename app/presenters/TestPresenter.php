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
	Nette\Utils\Strings;


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

		\Tracy\Debugger::$maxLen = NULL;


		$params = $this->container->getParameters();


		$client = new Google_Client;
		$credentials = $client->loadServiceAccountJson($params['google']['accountFile'], [Google_Service_Drive::DRIVE]);

		// master workaround
		$credentials->privateKeyPassword = NULL;

		$client->setAssertionCredentials($credentials);



//		$json = file_get_contents($params['google']['accountFile']);
//		$creds = json_decode($json);
//
//		echo $creds->private_key;
//
//		$certs = [];
//		$result = openssl_pkcs12_read($creds->private_key, $certs, '');
//
//		var_dump($result);
//		$err = openssl_error_string();
//		echo $err;




		if ($client->getAuth()->isAccessTokenExpired()) {
			$client->getAuth()->refreshTokenWithAssertion($credentials);
		}

		$client->getAccessToken();
		$drive = new Google_Service_Drive($client);




//		$result = $drive->files->listFiles();
//		print_r($result);





//		$fileName = 'testfile.doc';
		$fileName = 'drazebni_vyhlaska_c.j._39636-2015.pdf';




		$filePath = __DIR__ . "/../../resources/google/$fileName";
		$fileData = file_get_contents($filePath);



		$parent = new Google_Service_Drive_ParentReference;
		$parent->setId($params['google']['folderId']);




		$file = new Google_Service_Drive_DriveFile;
		$file->setTitle($fileName);
		$file->setParents([$parent]);


		$uploadedFile = $drive->files->insert(
			$file,
			[
				'data' => $fileData,
				'mimeType' => 'application/octet-stream',
				'uploadType' => 'multipart'
			]
		);


		\Tracy\Debugger::dump($uploadedFile);




		$fileId = $uploadedFile->getId();

		$file = new Google_Service_Drive_DriveFile;
		$file->setTitle($fileName);
		$file->setParents([$parent]);

		$convertedFile = $drive->files->copy($fileId, $file, ['convert' => TRUE]);


		\Tracy\Debugger::dump($convertedFile);


		$exportLinks = $convertedFile->getExportLinks();
		$plainTextLink = $exportLinks['text/plain'];
//		echo " PLAIN TEXT: $plainTextLink";


//		$curl = new CurlSender;
//		CertificateHelper::setCurlCaInfo($curl);



		$request = new Request($plainTextLink);
		$request->setTrustedCertificate(CertificateHelper::getCaInfoFile());
		$plainText = $request->get()->getResponse();
//		$plainText = $curl->send($request)->getResponse();

		\Tracy\Debugger::dump($plainText);


		// remove temp google doc file
		$drive->files->delete($convertedFile->getId());



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


		\Tracy\Debugger::dump($results);




		$em = $this->entityManager;

		foreach ($results as $result) {
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
		}

		$em->flush();






		$this->sendResponse(new TextResponse(' scrape '));
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

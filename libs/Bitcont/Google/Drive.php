<?php

namespace Bitcont\Google;

use Google_Client,
	Google_Service_Drive,
	Google_Service_Drive_DriveFile,
	Google_Service_Drive_ParentReference,
	Kdyby\Curl\Request,
	Kdyby\CurlCaBundle\CertificateHelper;


class Drive
{
	
	/**
	 * Google drive client.
	 * 
	 * @var Google_Client
	 */
	protected $client;

	/**
	 * Google drive folder id.
	 *
	 * @var string
	 */
	protected $folderId;


	/**
	 * @param string $accountJsonFile
	 * @param string $folderId Google drive folder where to put files
	 */
	public function __construct($accountJsonFile, $folderId)
	{
		$this->folderId = $folderId;

		// prepare client
		$client = new Google_Client;
		$credentials = $client->loadServiceAccountJson($accountJsonFile, [Google_Service_Drive::DRIVE]);

		// workaround until this gets resolved: https://github.com/google/google-api-php-client/pull/507
		$credentials->privateKeyPassword = NULL;
		$client->setAssertionCredentials($credentials);

		if ($client->getAuth()->isAccessTokenExpired()) {
			$client->getAuth()->refreshTokenWithAssertion($credentials);
		}

		$client->getAccessToken();
		$this->client = new Google_Service_Drive($client);
	}


	/**
	 * Uploads file to drive and returns its instance.
	 *
	 * @param string $file
	 * @param string $uploadFileName File will be uploaded to google drive under this name
	 * @return DriveFile
	 */
	public function upload($file, $uploadFileName = NULL)
	{
		// if $fileName not provided, use the $file name
		if (!$uploadFileName) $uploadFileName = basename($file);

		$fileData = file_get_contents($file);
		$googleFile = new Google_Service_Drive_DriveFile;
		$googleFile->setTitle($uploadFileName);

		$parent = new Google_Service_Drive_ParentReference;
		$parent->setId($this->folderId);
		$googleFile->setParents([$parent]);

		$uploadedFile = $this->client->files->insert(
			$googleFile,
			[
				'data' => $fileData,
				'mimeType' => 'application/octet-stream',
				'uploadType' => 'multipart'
			]
		);

		return new DriveFile($uploadedFile);
	}


	/**
	 * Returns plaintext or NULL.
	 *
	 * @param DriveFile $file
	 * @return string
	 */
	public function getPlainText(DriveFile $file)
	{
		// copy file into google document
		$fileId = $file->getId();
		$googleFile = new Google_Service_Drive_DriveFile;
		$googleFile->setTitle($file->getOriginalFilename());

		$parent = new Google_Service_Drive_ParentReference;
		$parent->setId($this->folderId);
		$googleFile->setParents([$parent]);

		$convertedFile = $this->client->files->copy($fileId, $googleFile, ['convert' => TRUE]);
		$exportLinks = $convertedFile->getExportLinks();
		$plainTextLink = $exportLinks['text/plain'];


		// plaintext not available
		if (!$plainTextLink) return NULL;


		// download plaintext
		$request = new Request($plainTextLink);
		$request->setTrustedCertificate(CertificateHelper::getCaInfoFile());
		$plainText = $request->get()->getResponse();


		// remove temp google doc file
		$this->client->files->delete($convertedFile->getId());


		// return plaintext
		return $plainText;
	}
}



<?php

namespace Bitcont\EGov\Bulletin;

use Doctrine\ORM\EntityManager;
use	Doctrine\Common\Collections\Criteria;
use Bitcont\EGov\Gov\Municipality;
use Bitcont\EGov\Bulletin\Scraper\ScrapedRecord;
use Bitcont\Google\Drive;
use Exception;


class Harvester
{

	/**
	 * Expetion code.
	 */
	const EXCEPTION_RECORD_ALREADY_IN_DB = 1;

	/**
	 * Entity manager.
	 *
	 * @var EntityManager
	 */
	protected $entityManager;

	/**
	 * Google drive client.
	 *
	 * @var Drive
	 */
	protected $googleDrive;


	/**
	 * @param EntityManager $entityManager
	 * @param Drive $googleDrive
	 */
	public function __construct(EntityManager $entityManager, Drive $googleDrive)
	{
		$this->entityManager = $entityManager;
		$this->googleDrive = $googleDrive;
	}


	/**
	 * Saves scraped record to db.
	 *
	 * @return Record
	 */
	public function saveRecord(Municipality $municipality, ScrapedRecord $scrapedRecord)
	{
		$em = $this->entityManager;

		// record already in db
		$record = $em->getRepository('Bitcont\EGov\Bulletin\Record')->findOneBy(['hash' => $scrapedRecord->hash]);
		if ($record) throw new Exception('Record already in database', static::EXCEPTION_RECORD_ALREADY_IN_DB);

		// new record
		$record = $scrapedRecord->getRecord($municipality);
		$em->persist($record);

		// save documents
		foreach ($record->getDocuments() as $document) {
			$em->persist($document);
		}

		$em->flush();
		return $record;
	}


	/**
	 * Download, re-upload and parse record documents.
	 *
	 * @param Record $record
	 */
	public function harvestDocuments(Record $record)
	{
		$em = $this->entityManager;
		$googleDrive = $this->googleDrive;

		try {
			foreach ($record->getDocuments() as $document) {

				// download & re-upload
				if ($document->googleDriveId === NULL) {
					$tmpFile = tmpfile();
					$filePath = stream_get_meta_data($tmpFile)['uri'];
					stream_copy_to_stream(fopen($document->url, 'r'), $tmpFile);

					$uploadedFileName = $document->getId() . '_' . $document->fileName;
					$uploadedFile = $googleDrive->upload($filePath, $uploadedFileName);

					$document->markGoogleDriveUploaded($uploadedFile->getId(), $uploadedFileName);
					$em->flush();
				}

				// parse
				if ($document->plainText === NULL) {
					if (!isset($uploadedFile)) $uploadedFile = $googleDrive->getFile($document->googleDriveId);

					$plainText = $googleDrive->getPlainText($uploadedFile);
					$document->plainText = $plainText;

					$em->flush();
				}
			}

			return $record;

		} catch (Exception $e) {

			// fail silently

//			// remove all
//			foreach ($record->getDocuments() as $document) {
//				$em->remove($document);
//			}
//
//			$em->remove($record);
//			$em->flush();
//			throw $e;

		}
	}


	/**
	 * Returns records with failed document upload or parsing.
	 *
	 * @return Record[]
	 */
	public function getFailedRecords()
	{
		$em = $this->entityManager;

		$criteria = Criteria::create()
			->where(Criteria::expr()->isNull('googleDriveId'))
			->orWhere(Criteria::expr()->isNull('plainText'));

		$records = [];
		foreach ($em->getRepository('Bitcont\EGov\Bulletin\Document')->matching($criteria) as $document) {
			$records[$document->getRecord()->getId()] = $document->getRecord();
		}

		return $records;
	}
}
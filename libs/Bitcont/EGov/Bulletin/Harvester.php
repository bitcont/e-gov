<?php

namespace Bitcont\EGov\Bulletin;

use Doctrine\ORM\EntityManager,
	Bitcont\EGov\Bulletin\Record,
	Bitcont\EGov\Bulletin\Scraper\ScrapedRecord,
	Bitcont\Google\Drive,
	Exception;


class Harvester
{

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
	protected $drive;


	/**
	 * @param EntityManager $entityManager
	 * @param Drive $drive
	 */
	public function __construct(EntityManager $entityManager, Drive $drive)
	{
		$this->entityManager = $entityManager;
		$this->drive = $drive;
	}


	/**
	 * Saves scraped record to db.
	 *
	 * @param ScrapedRecord $scrapedRecord
	 * @return Record
	 */
	public function harvest(ScrapedRecord $scrapedRecord)
	{
		$em = $this->entityManager;
		$drive = $this->drive;

		// record already in db
		$record = $em->getRepository('Bitcont\EGov\Bulletin\Record')->findOneBy(['hash' => $scrapedRecord->hash]);
		if ($record) return $record;

		// new record
		$record = new Record;
		$em->persist($record);
		$record->hash = $scrapedRecord->hash;
		$record->url = $scrapedRecord->url;
		$record->title = $scrapedRecord->title;
		$record->department = $scrapedRecord->department;
		$record->category = $scrapedRecord->category;
		$record->issueIdentifier = $scrapedRecord->issueIdentifier;
		$record->originator = $scrapedRecord->originator;
		$record->addressee = $scrapedRecord->addressee;
		$record->showFrom = $scrapedRecord->showFrom;
		$record->showTo = $scrapedRecord->showTo;

		// documents
		foreach ($scrapedRecord->documents as $scrapedDocument) {
			$document = new Document($record);
			$em->persist($document);
			$document->fileName = $scrapedDocument->fileName;
			$document->url = $scrapedDocument->url;
		}

		$em->flush();

		// documents now have ids we can use to form filenames on google drive
		try {
			foreach ($record->getDocuments() as $document) {
				$tmpFile = tmpfile();
				$filePath = stream_get_meta_data($tmpFile)['uri'];
				stream_copy_to_stream(fopen($document->url, 'r'), $tmpFile);

				$uploadedFileName = $document->getId() . '_' . $document->fileName;
				$uploadedFile = $drive->upload($filePath, $uploadedFileName);
				$plainText = $drive->getPlainText($uploadedFile);

				$document->googleDriveId = $uploadedFile->getId();
				$document->googleDriveFileName = $uploadedFileName;
				$document->plainText = $plainText;
			}

			$em->flush();
			return $record;

		} catch (Exception $e) {

			// remove all
			foreach ($record->getDocuments() as $document) {
				$em->remove($document);
			}

			$em->remove($record);
			$em->flush();
			throw $e;
		}
	}
}
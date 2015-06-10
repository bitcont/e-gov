<?php

namespace App\Presenters;

use Bitcont\EGov\Db\BulletinFacade;
use Bitcont\EGov\Bulletin\Document;


class BulletinRecordDetailPresenter extends BasePresenter
{

	protected $bulletinFacade;


	public function __construct(BulletinFacade $bulletinFacade)
	{
		$this->bulletinFacade = $bulletinFacade;
	}


	/**
	 * @param int $bulletinRecordId
	 */
	public function renderDefault($bulletinRecordId)
	{
		$bulletinRecord = $this->bulletinFacade->getBulletinRecord($bulletinRecordId);
		$municipality = $bulletinRecord->getMunicipality();


		$this->template->bulletinRecord = [
			'id' => $bulletinRecord->getId(),
			'title' => $bulletinRecord->title,
			'publishedFrom' => $bulletinRecord->publishedFrom,
			'publishedTo' => $bulletinRecord->publishedTo,
			'issueIdentifier' => $bulletinRecord->issueIdentifier,
			'department' => $bulletinRecord->department,
			'category' => $bulletinRecord->category,
			'hasDescription' => $bulletinRecord->hasDescription(),
			'description' => $bulletinRecord->description,
			'municipality' => [
				'id' => $municipality->getId(),
				'name' => $municipality->name
			],
			'documents' => []
		];


		foreach ($bulletinRecord->getDocuments() as $document) {
			$this->template->bulletinRecord['documents'][] = [
				'id' => $document->getId(),
				'format' => $document->getFormat(),
				'fileName' => $document->fileName,
				'url' => $document->url,
				'plainText' => $document->plainText,
				'isImage' => $document->getFormat() === Document::FORMAT_IMAGE
			];
		}
	}
}

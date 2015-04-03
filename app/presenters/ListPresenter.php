<?php

namespace App\Presenters;


class ListPresenter extends BasePresenter
{

	public function renderDefault()
	{
		$params = $this->container->getParameters();
		$em = $this->entityManager;
		$this->template->records = [];

		foreach ($em->getRepository('Bitcont\EGov\Bulletin\Record')->findAll() as $record) {
			$rec = [
				'title' => $record->title,
				'showFrom' => $record->showFrom,
				'showTo' => $record->showTo,
				'issueIdentifier' => $record->issueIdentifier,
				'docs' => []
			];

			foreach ($record->getDocuments() as $document) {
				$rec['docs'][] = [
					'fileName' => $document->fileName,
					'href' => $document->googleDriveFileName ? $params['google']['folderUrl'] . '/' . $document->googleDriveFileName : NULL
				];
			}

			$this->template->records[] = $rec;
		}
	}

}

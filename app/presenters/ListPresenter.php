<?php

namespace App\Presenters;

use Nette\Application\UI\Form;
use Bitcont\EGov\ElasticSearch\DocumentSearchQuery;


class ListPresenter extends BasePresenter
{

	public function renderDefault($searchPhrase = NULL)
	{
		$settings = $this->container->getParameters();
		$em = $this->entityManager;
		$this->template->records = [];

		if ($searchPhrase) {

//			$document = $em->getRepository('Bitcont\EGov\Bulletin\Document')->findOneBy(['id' => 13]);
//			$document->plainText = 'necum ' . $document->plainText;
//
//			$this->searchManager->persist($document);
//			$this->searchManager->flush();
//
////			print_r($this->searchManager);
//			echo "x";
//			die();


			$search = (new DocumentSearchQuery($searchPhrase))
				->createQuery($this->searchManager, $this->entityManager);

			$documents = $search->getResult();




//			$entity = $em
//				->getRepository('MyBundle:MyEntity')
//				->createQueryBuilder('e')
//				->join('e.idRelatedEntity', 'r')
//				->where('r.foo = 1')
//				->getQuery()
//				->getResult();


//			// search in documents
//			$documents = $em->getRepository('Bitcont\EGov\Bulletin\Document')
//				->createQueryBuilder('d')
//				->andWhere('d.plainText LIKE :searchPhrase')
//				->setParameter('searchPhrase', "%$searchPhrase%")
//				->getQuery()
//				->getResult();



			$records = [];
			foreach ($documents as $document) {
				$record = $document->getRecord();
				if (!in_array($record, $records)) {
					$records[] = $document->getRecord();
				}
			}

		} else {
			$records = $em->getRepository('Bitcont\EGov\Bulletin\Record')->findAll();
		}

		foreach ($records as $record) {
			$recordData = [
				'title' => $record->title,
				'showFrom' => $record->showFrom,
				'showTo' => $record->showTo,
				'issueIdentifier' => $record->issueIdentifier,
				'docs' => []
			];

			foreach ($record->getDocuments() as $document) {
				$recordData['docs'][] = [
					'id' => $document->getId(),
					'fileName' => $document->fileName,
					'href' => $document->googleDriveFileName ? $settings['google']['folderUrl'] . '/' . $document->googleDriveFileName : NULL
				];
			}

			$this->template->records[] = $recordData;
		}
	}


	protected function createComponentSearchForm()
	{
		$form = new Form;
		$form->setMethod('GET');
		$form->addText('search');
		$form->addSubmit('submit', 'Search');
		$form->onSuccess[] = [$this, 'searchFormSucceeded'];
		return $form;
	}


	public function searchFormSucceeded(Form $form, $values)
	{

//		print_r($values);
//		die();


		$this->redirect('default', $values->search);
	}

}

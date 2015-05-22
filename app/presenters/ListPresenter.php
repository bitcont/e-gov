<?php

namespace App\Presenters;

use Doctrine\ORM\EntityManager;
use Doctrine\Search\SearchManager;
use Nette\DI\Container;
use Nette\Application\UI\Form;
use Nette\Utils\Paginator;
use Bitcont\EGov\ElasticSearch\DocumentSearchQuery;


class ListPresenter extends BasePresenter
{

	protected $entityManager;
	protected $searchManager;
	protected $container;


	public function __construct(EntityManager $entityManager, SearchManager $searchManager, Container $container)
	{
		$this->entityManager = $entityManager;
		$this->searchManager = $searchManager;
		$this->container = $container;
	}


	public function renderDefault($searchPhrase = NULL)
	{
		$settings = $this->container->getParameters();
		$em = $this->entityManager;
		$this->template->records = [];

		if ($searchPhrase) {





//			$document = $em->getRepository('Bitcont\EGov\Bulletin\Document')->findOneBy(['id' => 7]);
////			$document->plainText = 'necum ' . $document->plainText;
//			$this->searchManager->persist($document);
//			$this->searchManager->flush();
//			die();



//			foreach ($em->getRepository('Bitcont\EGov\Bulletin\Document')->findAll() as $document) {
//				if (!$document->plainText) continue;
//				$this->searchManager->persist($document);
//			}
//			$documents = $em->getRepository('Bitcont\EGov\Bulletin\Document')->findAll();
//			$this->searchManager->persist($documents);
//
//			$this->searchManager->flush();
//			die();



//
////			print_r($this->searchManager);
//			echo "x";
//			die();


			$paginator = new Paginator;
			$paginator->setItemsPerPage(500);


			$search = (new DocumentSearchQuery($searchPhrase))
				->setPaginator($paginator)
				->createQuery($this->searchManager, $this->entityManager);

			$documents = $search->getResult();
			$paginator->setItemCount($search->count());


//			scanAndScroll()






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
				'id' => $record->getId(),
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

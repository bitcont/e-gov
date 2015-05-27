<?php

namespace App\Presenters;

use Doctrine\ORM\EntityManager;
use Doctrine\Search\SearchManager;
use Nette\DI\Container;
use Nette\Application\UI\Form;
use Nette\Utils\Paginator;
use Bitcont\EGov\ElasticSearch\DocumentSearchQuery;
use Kdyby\BootstrapFormRenderer\BootstrapRenderer;


class ListPresenter extends BasePresenter
{

	protected $entityManager;
	protected $searchManager;


	public function __construct(EntityManager $entityManager, SearchManager $searchManager)
	{
		$this->entityManager = $entityManager;
		$this->searchManager = $searchManager;
	}

	/**
	 * @param string $search
	 * @param string $municipalities
	 */
	public function renderDefault($search = NULL, $municipalities = NULL)
	{
		if ($municipalities) {
			$municipalities = explode(',', $municipalities);

		} else {
			$municipalities = [];
		}

		$settings = $this->container->getParameters();
		$this->template->records = [];

		foreach ($this->getRecords($search, $municipalities) as $record) {
			$recordData = [
				'id' => $record->getId(),
				'title' => $record->title,
				'publishedFrom' => $record->publishedFrom,
				'publishedTo' => $record->publishedTo,
				'issueIdentifier' => $record->issueIdentifier,
				'docs' => [],
				'municipality' => $record->getMunicipality()->name,
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


		$this->getComponent('searchForm')->setDefaults([
			'search' => $search,
			'municipalities' => $municipalities

		]);
	}


	protected function createComponentSearchForm()
	{
		$form = new Form;
		$form->setMethod('GET');


		$form->addText('search');
		$form->addMultiSelect('municipalities', 'Municipality', $this->getMunicipalityOptions());




		$form->addSubmit('submit', 'Search');
		$form->setRenderer(new BootstrapRenderer);
		$form->onSuccess[] = [$this, 'searchFormSucceeded'];
		return $form;
	}


	public function searchFormSucceeded(Form $form, $values)
	{
		$params = [
			'search' => $values->search,
			'municipalities' => implode(',', $values->municipalities)
		];


		$this->redirect('default', $params);
	}


	protected function getMunicipalityOptions()
	{
		$options = [];

		foreach ($this->entityManager->getRepository('Bitcont\EGov\Gov\Municipality')->findAll() as $municipality) {
			$options[$municipality->getId()] = $municipality->name;
		}

		return $options;
	}


	/**
	 * @param string $search
	 * @param int[] $municipalities
	 * @return array
	 */
	protected function getRecords($search = NULL, array $municipalities = NULL)
	{
		$em = $this->entityManager;





		if ($search) {


			$paginator = new Paginator;
			$paginator->setItemsPerPage(500);


			$searchQuery = (new DocumentSearchQuery($search))
				->setPaginator($paginator)
				->createQuery($this->searchManager, $this->entityManager);

			$documents = $searchQuery->getResult();
			$paginator->setItemCount($searchQuery->count());


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



		// filter by municipality
		if ($municipalities) {
			foreach ($records as $key => $record) {
				if (!in_array($record->getMunicipality()->getId(), $municipalities)) {
					unset($records[$key]);
				}
			}
		}




		// sort by id
		usort($records, function ($a, $b) {
			return $a->getId() - $b->getId();
		});

		return $records;
	}
}

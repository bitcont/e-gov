<?php

namespace App\Presenters;

use Doctrine\ORM\EntityManager;
use Doctrine\Search\SearchManager;
use Nette\DI\Container;
use Nette\Application\UI\Form;
use Nette\Utils\Paginator;
use Bitcont\EGov\ElasticSearch\DocumentSearchQuery;
use Bitcont\EGov\Bulletin\Record;
use Kdyby\BootstrapFormRenderer\BootstrapRenderer;


class BulletinRecordListPresenter extends BasePresenter
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
			$municipalityIds = explode(',', $municipalities);

		} else {
			$municipalityIds = array_keys($this->getMunicipalityOptions());
		}

		$this->fillTemplateWithBulletinRecords($this->getBulletinRecords($search, $municipalityIds));

		$this->getComponent('searchForm')->setDefaults([
			'search' => $search,
			'municipalities' => $municipalityIds

		]);
	}


	/**
	 * @param int $municipalityId
	 * @param string $search
	 */
	public function renderMunicipality($municipalityId, $search = NULL)
	{
		$municipality = $this->entityManager->getRepository('Bitcont\EGov\Gov\Municipality')->find($municipalityId);

		$this->template->municipality = [
			'name' => $municipality->name
		];

		$this->fillTemplateWithBulletinRecords($this->getBulletinRecords($search, [$municipalityId]));

		$this->getComponent('searchForm')->setDefaults([
			'search' => $search,
			'municipalities' => NULL
		]);
	}


	protected function createComponentSearchForm($selectMunicipalities = TRUE)
	{
		$form = new Form;
		$form->setMethod('GET');

		$form->addText('search');
		$form->addMultiSelect('municipalities', 'Municipality', $this->getMunicipalityOptions());

		$form->addSubmit('submit', 'Hledej');
		$form->setRenderer(new BootstrapRenderer);
		$form->onSuccess[] = [$this, 'searchFormSucceeded'];
		return $form;
	}


	public function searchFormSucceeded(Form $form, $values)
	{
		$params = [
			'search' => $values->search
		];


		if ($values->municipalities) {
			$params['municipalities'] = implode(',', $values->municipalities);
		}

		$this->redirect('this', $params);
	}


	protected function getMunicipalityOptions()
	{
		$options = [];

		foreach ($this->getMunicipalities() as $municipality) {
			$options[$municipality->getId()] = $municipality->name;
		}

		return $options;
	}


	protected function getMunicipalities()
	{
		return $this->entityManager->getRepository('Bitcont\EGov\Gov\Municipality')->findAll();
	}


	/**
	 * @param string $search
	 * @param int[] $municipalities
	 * @return Record[]
	 */
	protected function getBulletinRecords($search = NULL, array $municipalityIds = NULL)
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
		if ($municipalityIds) {
			foreach ($records as $key => $record) {
				if (!in_array($record->getMunicipality()->getId(), $municipalityIds)) {
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


	/**
	 * @param Record[]
	 */
	protected function fillTemplateWithBulletinRecords(array $bulletinRecords)
	{
		$settings = $this->container->getParameters();
		$this->template->bulletinRecords = [];

		foreach ($bulletinRecords as $bulletinRecord) {
			$municipality = $bulletinRecord->getMunicipality();

			$bulletinRecordData = [
				'id' => $bulletinRecord->getId(),
				'title' => $bulletinRecord->title,
				'publishedFrom' => $bulletinRecord->publishedFrom,
				'publishedTo' => $bulletinRecord->publishedTo,
				'issueIdentifier' => $bulletinRecord->issueIdentifier,
				'docs' => [],
				'municipality' => [
					'id' => $municipality->getId(),
					'name' => $municipality->name
				]
			];

			foreach ($bulletinRecord->getDocuments() as $document) {
				$bulletinRecordData['docs'][] = [
					'id' => $document->getId(),
					'fileName' => $document->fileName,
					'href' => $document->googleDriveFileName ? $settings['google']['folderUrl'] . '/' . $document->googleDriveFileName : NULL
				];
			}

			$this->template->bulletinRecords[] = $bulletinRecordData;
		}
	}
}

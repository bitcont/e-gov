<?php

namespace Bitcont\EGov\Db;

use Bitcont\EGov\Bulletin\Record;
use Bitcont\EGov\Gov\Municipality;
use Bitcont\EGov\ElasticSearch\DocumentSearchQuery;
use Doctrine\ORM\EntityManager;
use Doctrine\Search\SearchManager;
use Doctrine\Common\Collections\Criteria;
use Nette\Utils\Paginator;


class BulletinFacade
{

	protected $entityManager;
	protected $searchManager;


	public function __construct(EntityManager $entityManager, SearchManager $searchManager)
	{
		$this->entityManager = $entityManager;
		$this->searchManager = $searchManager;
	}


	/**
	 * @param int $id
	 * @return Record
	 */
	public function getBulletinRecord($id)
	{
		return $this->entityManager->getRepository('Bitcont\EGov\Bulletin\Record')->find($id);
	}


	/**
	 * @param int $id
	 * @return Municipality
	 */
	public function getMunicipality($id)
	{
		return $this->entityManager->getRepository('Bitcont\EGov\Gov\Municipality')->find($id);
	}


	/**
	 * @return Municipality[]
	 */
	public function getMunicipalities()
	{
		return $this->entityManager->getRepository('Bitcont\EGov\Gov\Municipality')->findAll();
	}


	/**
	 * @param string $search
	 * @param int[] $municipalities
	 * @param int $itemsPerPage
	 * @return Record[]
	 */
	public function getBulletinRecords($search = NULL, array $municipalityIds = NULL, $itemsPerPage = 50)
	{
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
			$records = $this->entityManager->getRepository('Bitcont\EGov\Bulletin\Record')->findAll();
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
	 * @param int $items
	 * @return Record[]
	 */
	public function getMixedBulletinRecords($items = 50)
	{
		$municipalities = $this->getMunicipalities();
		$itemsPerMunicipality = ceil($items / count($municipalities));

		$criteria = Criteria::create()
			->orderBy(['publishedFrom' => Criteria::DESC])
			->setMaxResults($itemsPerMunicipality)
		;

		$records = [];
		foreach ($municipalities as $municipality) {
			$records = array_merge($records, $municipality->getBulletinRecords()->matching($criteria)->toArray());
		}






		// sort by id
		usort($records, function ($a, $b) {
			return rand(-1, 1);
		});

		return $records;
	}
}

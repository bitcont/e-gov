<?php

namespace Bitcont\EGov\ElasticSearch;

use Elastica\Filter;
use Elastica\Query;
use Elastica\Util;
use Doctrine\Search\SearchManager;
use Doctrine\ORM\EntityManager;
use Nette\Utils\Paginator;
use Bitcont\EGov\Bulletin\Document;

class DocumentSearchQuery
{

	private $query;

	private $paginator = NULL;


	public function __construct($query)
	{
		$this->query = Util::escapeTerm($query);
	}

	public function setPaginator(Paginator $paginator)
	{
		$this->paginator = $paginator;
		return $this;
	}

	public function createQuery(SearchManager $searchManager, EntityManager $entityManager)
	{
//		$filtered = new Query\Filtered($this->createSearchQuery(), $this->createSearchFilter());
		$filtered = new Query\Filtered($this->createSearchQuery());

		$searchWith = (new Query($filtered))
//			->addSort(['available' => ['order' => 'desc']])
			->addSort(['_score' => ['order' => 'desc']])
			->setParam('_source', FALSE);

		$hydrateWith = $this->createHydratingQuery($entityManager);

		$query = $searchManager->createQuery()
			->searchWith($searchWith)
			->hydrateWith($hydrateWith);

		if ($this->paginator !== NULL) {
			$query->setMaxResults($this->paginator->getLength());
			$query->setFirstResult($this->paginator->getOffset());
		}

		return $query;
	}

	private function createSearchQuery()
	{
		$search = (new Query\QueryString($this->query))
			->setDefaultOperator('AND')
			->setBoost(3);

		$name = (new Query\Match())
			->setFieldQuery('plainText', $this->query)
			->setFieldOperator('plainText', 'AND')
			->setFieldBoost('plainText', 5);

		return (new Query\Bool())
//			->addShould($search)
			->addShould($name);
	}

	private function createSearchFilter()
	{
//		$filter = (new Filter\Bool())
//			->addMust(new Filter\Term(['enable' => TRUE]));
		$filter = (new Filter\Bool());

		return $filter;
	}

	private function createHydratingQuery(EntityManager $entityManager)
	{
		$repository = $entityManager->getRepository(Document::class);

		$qb = $repository->createQueryBuilder('p')
//			->addSelect('FIELD(p.id, :ids) as HIDDEN relevance')
			->andWhere('p.id IN (:ids)');

		return $qb;
	}
}


<?php

namespace App\Presenters;

use Nette\Application\UI\Presenter;
use Doctrine\ORM\EntityManager;
use Nette\DI\Container;
use Doctrine\Search\SearchManager;


abstract class BasePresenter extends Presenter
{

	/**
 * @var \Doctrine\ORM\EntityManager
 */
	protected $entityManager;

	/**
	 * @var \Nette\DI\Container
	 */
	protected $container;

	/**
	 * @var \Doctrine\Search\SearchManager
	 */
	protected $searchManager;


	public function injectEntityManager(EntityManager $entityManager)
	{
		$this->entityManager = $entityManager;
	}


	public function injectContainer(Container $container)
	{
		$this->container = $container;
	}

	public function injectSearchManager(SearchManager $searchManager)
	{
		$this->searchManager = $searchManager;
	}
}

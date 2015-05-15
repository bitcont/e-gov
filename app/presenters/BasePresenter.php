<?php

namespace App\Presenters;

use Nette\Application\UI\Presenter,
	Doctrine\ORM\EntityManager,
	Nette\DI\Container;


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


	public function injectDoctrine(EntityManager $entityManager)
	{
		$this->entityManager = $entityManager;
	}


	public function injectContainer(Container $container)
	{
		$this->container = $container;
	}
}

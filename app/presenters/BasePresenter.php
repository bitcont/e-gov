<?php

namespace App\Presenters;

use Nette\Application\UI\Presenter;
use Nette\Bridges\ApplicationLatte\Template;
use Nette\DI\Container;


abstract class BasePresenter extends Presenter
{

	/**
	 * @var \Nette\DI\Container
	 * @inject
	 */
	public $container;


	public function beforeRender()
	{
		$settings = $this->container->getParameters();
		$this->template->brand = $settings['brand'];

		return parent::beforeRender();
	}
}

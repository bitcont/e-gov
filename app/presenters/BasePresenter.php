<?php

namespace App\Presenters;

use Nette\Application\UI\Presenter;
use Nette\Bridges\ApplicationLatte\Template;
use Nette\DI\Container;


abstract class BasePresenter extends Presenter
{

//	protected function fillTemplate(Template $template, Container $container, array $variables)
	public function fillTemplate(Container $container, array $variables)
	{
		$settings = $container->getParameters();
		$this->template->brand = $settings['brand'];

		$this->template->setParameters($variables);
	}
}

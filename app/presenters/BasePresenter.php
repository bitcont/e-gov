<?php

namespace App\Presenters;

use Nette\Application\UI\Presenter;
use Nette\DI\Container;
use Nette\Utils\Html;
use Bitcont\EGov\Bulletin\Document;


abstract class BasePresenter extends Presenter
{

	/**
	 * @var \Nette\DI\Container
	 * @inject
	 */
	public $container;


	protected function beforeRender()
	{
		$settings = $this->container->getParameters();
		$this->template->brand = $settings['brand'];

		return parent::beforeRender();
	}


	protected function createTemplate($class = NULL)
	{
		$template = parent::createTemplate($class);

		$icons = [
			Document::FORMAT_WORD => 'fa-file-word-o',
			Document::FORMAT_EXCEL => 'fa-file-excel-o',
			Document::FORMAT_PDF => 'fa-file-pdf-o',
			Document::FORMAT_IMAGE => 'fa-file-image-o'
		];

		$template->addFilter('icon', function($s) use($icons) {
			if (isset($icons[$s])) {
				$icon = $icons[$s];

			} else {
				$icon = 'fa-file-text-o';
			}

			return (new Html)->setHtml("<i class='fa $icon'></i>");

		});
		return $template;
	}
}

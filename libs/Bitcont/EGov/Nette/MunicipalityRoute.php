<?php

namespace Bitcont\EGov\Nette;

use Doctrine\ORM\EntityManager;
use Nette\Application\Routers\Route;
use Nette\Utils\Strings;
use Nette\Application\Request;
use Nette\Http\Url;
use Exception;


class MunicipalityRoute extends Route
{

	protected $entityManager;


	/**
	 * @inheritdoc
	 */
	public function __construct($mask, $metadata = array(), $flags = 0, EntityManager $entityManager)
	{
		$this->entityManager = $entityManager;
		return parent::__construct($mask, $metadata, $flags);
	}


	/**
	 * @inheritdoc
	 */
	public function constructUrl(Request $appRequest, Url $refUrl)
	{
		$params = $appRequest->getParameters();

		if (!isset($params['municipalityId'])) return;

		$municipality = $this->entityManager->find('Bitcont\EGov\Gov\Municipality', $params['municipalityId']);
		if (!$municipality) throw new Exception('Object not found');
//		if ($municipality->isRemoved()) throw new Exception('Object removed');

		$params['municipalityName'] = Strings::webalize($municipality->name);
		$appRequest->setParameters($params);

		return parent::constructUrl($appRequest, $refUrl);
	}
}

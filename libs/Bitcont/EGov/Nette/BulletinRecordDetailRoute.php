<?php

namespace Bitcont\EGov\Nette;

use Doctrine\ORM\EntityManager;
use Nette\Application\Routers\Route;
use Nette\Utils\Strings;
use Nette\Application\Request;
use Nette\Http\Url;
use Exception;


class BulletinRecordDetailRoute extends Route
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

		$record = $this->entityManager->find('Bitcont\EGov\Bulletin\Record', $params['bulletinRecordId']);
		if (!$record) throw new Exception('Object not found');
//		if ($record->isRemoved()) throw new Exception('Object removed');

		$params['bulletinRecordTitle'] = Strings::webalize($record->title);
		$params['municipalityId'] = $record->getMunicipality()->getId();
		$params['municipalityName'] = Strings::webalize($record->getMunicipality()->name);
		$appRequest->setParameters($params);

		return parent::constructUrl($appRequest, $refUrl);
	}
}

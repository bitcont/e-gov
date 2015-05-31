<?php

namespace App;

use Nette;
use Nette\Application\Routers\RouteList;
use Nette\Application\Routers\Route;
use Nette\Application\Routers\SimpleRouter;
use Doctrine\ORM\EntityManager;
use Bitcont\EGov\Nette\BulletinRecordDetailRoute;
use Bitcont\EGov\Nette\MunicipalityRoute;


class RouterFactory
{

	/**
	 * @return \Nette\Application\IRouter
	 */
	public static function createRouter(EntityManager $entityManager)
	{
		$router = new RouteList();

		$router[] = new BulletinRecordDetailRoute('obec/<municipalityId [0-9]+>/<municipalityName>/<bulletinRecordId [0-9]+>/<bulletinRecordTitle>[/<action>]', 'BulletinRecordDetail:default', NULL, $entityManager);


		$router[] = new MunicipalityRoute('obec/<municipalityId [0-9]+>/<municipalityName>', 'BulletinRecordList:municipality', NULL, $entityManager);
//		$router[] = new MunicipalityRoute('obec/<municipalityId [0-9]+>/<municipalityName>', [
//			'presenter' => 'BulletinRecordList',
//			'action'    => 'municipality'
//		], NULL, $entityManager);


		$router[] = new Route('search', 'BulletinRecordList:search');
		$router[] = new Route('<presenter>/<action>[/<id>]', 'BulletinRecordList:default');

		return $router;
	}

}

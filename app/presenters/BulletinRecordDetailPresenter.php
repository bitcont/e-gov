<?php

namespace App\Presenters;

use Doctrine\ORM\EntityManager;


class BulletinRecordDetailPresenter extends BasePresenter
{

	protected $entityManager;


	public function __construct(EntityManager $entityManager)
	{
		$this->entityManager = $entityManager;
	}


	/**
	 * @param int $bulletinRecordId
	 */
	public function renderDefault($bulletinRecordId)
	{
		$bulletinRecord = $this->entityManager->getRepository('Bitcont\EGov\Bulletin\Record')->find($bulletinRecordId);
		$municipality = $bulletinRecord->getMunicipality();

		$this->template->bulletinRecord = [
			'id' => $bulletinRecord->getId(),
			'title' => $bulletinRecord->title,
			'municipality' => [
				'id' => $municipality->getId(),
				'name' => $municipality->name
			]
		];


	}
}

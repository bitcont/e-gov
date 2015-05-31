<?php

namespace App\Presenters;


use Nette\Application\UI\Form;
use Bitcont\EGov\Bulletin\Record;
use Bitcont\EGov\Db\BulletinFacade;
use Kdyby\BootstrapFormRenderer\BootstrapRenderer;


class BulletinRecordListPresenter extends BasePresenter
{

	protected $bulletinFacade;


	public function __construct(BulletinFacade $bulletinFacade)
	{
		$this->bulletinFacade = $bulletinFacade;
	}


	public function renderDefault()
	{
		$this->fillTemplateWithBulletinRecords($this->bulletinFacade->getMixedBulletinRecords());

		$this->getComponent('searchForm')->setDefaults([
			'municipalities' => array_keys($this->getMunicipalityOptions())
		]);
	}


	/**
	 * @param string $search
	 * @param string $municipalities
	 */
	public function renderSearch($search = NULL, $municipalities = NULL)
	{
		if ($municipalities) {
			$municipalityIds = explode(',', $municipalities);

		} else {
			$municipalityIds = array_keys($this->getMunicipalityOptions());
		}

		$this->fillTemplateWithBulletinRecords($this->bulletinFacade->getBulletinRecords($search, $municipalityIds));

		$this->getComponent('searchForm')->setDefaults([
			'search' => $search,
			'municipalities' => $municipalityIds

		]);
	}


	/**
	 * @param int $municipalityId
	 * @param string $search
	 */
	public function renderMunicipality($municipalityId, $search = NULL)
	{
		$municipality = $this->bulletinFacade->getMunicipality($municipalityId);

		$this->template->municipality = [
			'name' => $municipality->name
		];

		$this->fillTemplateWithBulletinRecords($this->bulletinFacade->getBulletinRecords($search, [$municipalityId]));

		$this->getComponent('searchForm')->setDefaults([
			'search' => $search,
			'municipalities' => NULL
		]);
	}


	protected function createComponentSearchForm($selectMunicipalities = TRUE)
	{
		$form = new Form;
		$form->setMethod('GET');

		$form->addText('search');
		$form->addMultiSelect('municipalities', 'Municipality', $this->getMunicipalityOptions());

		$form->addSubmit('submit', 'Hledej');
		$form->setRenderer(new BootstrapRenderer);
		$form->onSuccess[] = [$this, 'searchFormSucceeded'];
		return $form;
	}


	public function searchFormSucceeded(Form $form, $values)
	{
		$params = [
			'search' => $values->search
		];

		if ($values->municipalities) {
			$params['municipalities'] = implode(',', $values->municipalities);
		}


		if ($this->getAction() === 'default') {
			$this->redirect('search', $params);

		} else {
			$this->redirect('this', $params);
		}
	}


	protected function getMunicipalityOptions()
	{
		$options = [];

		foreach ($this->bulletinFacade->getMunicipalities() as $municipality) {
			$options[$municipality->getId()] = $municipality->name;
		}

		return $options;
	}


	/**
	 * @param Record[] $bulletinRecords
	 */
	protected function fillTemplateWithBulletinRecords(array $bulletinRecords)
	{
		$settings = $this->container->getParameters();
		$this->template->bulletinRecords = [];

		foreach ($bulletinRecords as $bulletinRecord) {
			$municipality = $bulletinRecord->getMunicipality();

			$bulletinRecordData = [
				'id' => $bulletinRecord->getId(),
				'title' => $bulletinRecord->title,
				'publishedFrom' => $bulletinRecord->publishedFrom,
				'publishedTo' => $bulletinRecord->publishedTo,
				'issueIdentifier' => $bulletinRecord->issueIdentifier,
				'docs' => [],
				'municipality' => [
					'id' => $municipality->getId(),
					'name' => $municipality->name
				]
			];

			foreach ($bulletinRecord->getDocuments() as $document) {
				$bulletinRecordData['docs'][] = [
					'id' => $document->getId(),
					'format' => $document->getFormat(),
					'fileName' => $document->fileName,
					'href' => $document->googleDriveFileName ? $settings['google']['folderUrl'] . '/' . $document->googleDriveFileName : NULL,
					'url' => $document->url
				];
			}

			$this->template->bulletinRecords[] = $bulletinRecordData;
		}
	}
}

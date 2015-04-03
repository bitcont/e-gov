<?php

namespace Bitcont\EGov\Nette;

use Symfony\Component\Console\Command\Command,
	Symfony\Component\Console\Input\InputInterface,
	Symfony\Component\Console\Output\OutputInterface,
	Bitcont\EGov\Bulletin\Scraper\Praha2,
	Bitcont\EGov\Bulletin\Harvester,
	Nette\Utils\Strings,
	Doctrine\ORM\EntityManager,
	Bitcont\Google\Drive;


class HarvestCommand extends Command
{

	/**
	 * @var \Doctrine\ORM\EntityManager @inject
	 */
	public $entityManager;


	protected function configure()
	{
		$this
			->setName('app:harvest')
			->setDescription('Harvests data from all the bulletin boards');
	}


	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$params = $this->getHelper('container')->getContainer()->getParameters();

		$drive = new Drive($params['google']['accountFile'], $params['google']['folderId']);
		$harvester = new Harvester($this->entityManager, $drive);

		$output->writeLn('=== Praha2 ===');
		$scraper = new Praha2;
		foreach ($scraper->scrape() as $scrapedRecord) {
			$title = Strings::truncate($scrapedRecord->title, 50);
			$title = Strings::toAscii($title);

			$record = $harvester->harvest($scrapedRecord);

			$i = $j = 0;
			foreach ($record->getDocuments() as $document) {
				if ($document->googleDriveId !== NULL) $i++;
				if ($document->plainText !== NULL) $j++;
			}
			$count = count($record->getDocuments());

			$output->writeLn("-> $title [$count docs, $i uploaded, $j parsed]");
		}

		return 0; // zero return code means everything is ok
	}
}
<?php

namespace Bitcont\EGov\Nette;

use Symfony\Component\Console\Command\Command,
	Symfony\Component\Console\Input\InputInterface,
	Symfony\Component\Console\Output\OutputInterface,
	Bitcont\EGov\Bulletin\Scraper\Praha2,
	Bitcont\EGov\Bulletin\Harvester,
	Bitcont\EGov\Bulletin\Record,
	Nette\Utils\Strings,
	Doctrine\ORM\EntityManager,
	Bitcont\Google\Drive,
	Exception,
	Doctrine\DBAL\Exception\UniqueConstraintViolationException;


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
			->setDescription('Harvests data from all the bulletin boards')
			->addOption('redo');
	}


	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$em = $this->entityManager;
		$params = $this->getHelper('container')->getContainer()->getParameters();
		$drive = new Drive($params['google']['accountFile'], $params['google']['folderId'], $params['google']['tempFolderId']);
		$harvester = new Harvester($em, $drive);


		$records = [];

		if ($input->getOption('redo')) {
			foreach ($harvester->getFailedRecords() as $record) {
				$harvester->harvestRecord($record);
				$output->writeLn(" -> " . static::getPrintInfo($record));
			}

		} else {
			$output->writeLn('=== Praha2 ===');

			$scraper = new Praha2;
			$i = 0;
			foreach ($scraper->scrape() as $scrapedRecord) {
				try {
					$record = $harvester->saveRecord($scrapedRecord);
					$harvester->harvestRecord($record);

				} catch (Exception $e) {
					if ($e->getCode() === Harvester::EXCEPTION_RECORD_ALREADY_IN_DB) {
						$record = $em->getRepository('Bitcont\EGov\Bulletin\Record')->findOneBy(['hash' => $scrapedRecord->hash]);

					} else {
						throw $e;
					}
				}

				$i++;
				$output->writeLn(" -> [$i] " . static::getPrintInfo($record));
			}
		}

		return 0; // zero return code means everything is ok
	}


	protected static function getPrintInfo(Record $record)
	{
		$i = $j = 0;
		foreach ($record->getDocuments() as $document) {
			if ($document->googleDriveId !== NULL) $i++;
			if ($document->plainText !== NULL) $j++;
		}
		$count = count($record->getDocuments());

		$title = Strings::truncate($record->title, 50);
		$title = Strings::toAscii($title);

		return "$title [$count docs, $i uploaded, $j parsed]";
	}
}
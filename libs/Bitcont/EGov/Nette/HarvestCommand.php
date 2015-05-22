<?php

namespace Bitcont\EGov\Nette;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Bitcont\EGov\Bulletin\Scraper\Praha2;
use Bitcont\EGov\Bulletin\Harvester;
use Bitcont\EGov\Bulletin\Record;
use Bitcont\Google\Drive;
use Nette\Utils\Strings;
use Exception;


class HarvestCommand extends Command
{

	/**
	 * @inject
	 * @var \Doctrine\ORM\EntityManager
	 */
	public $entityManager;


	protected function configure()
	{
		$this
			->setName('egov:harvest')
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
			$output->writeLn('=== Praha 2 ===');

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
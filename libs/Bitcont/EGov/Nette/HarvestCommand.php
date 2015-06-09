<?php

namespace Bitcont\EGov\Nette;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Bitcont\EGov\Gov\Municipality;
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

	/**
	 * @inject
	 * @var \Nette\DI\Container
	 */
	public $container;


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
		$googleDrive = new Drive($params['google']['accountFile'], $params['google']['folderId'], $params['google']['tempFolderId']);
		$harvester = new Harvester($em, $googleDrive);

		if ($input->getOption('redo')) {
//			foreach ($harvester->getFailedRecords() as $record) {
//				$harvester->harvestDocuments($record);
//				$output->writeLn(" -> " . static::getPrintInfo($record));
//			}

		} else {
			$this->fixtureMunicipalities();

			foreach ($this->getMunicipalities() as $municipality) {
				$output->writeLn("\n=== " . $municipality->name . ' ===');

				$scraper = $municipality->selectScraper($this->getScrapers());
				if (!$scraper) {
					$output->writeLn(' ---> no scraper found');
					continue;
				}

				$output->writeLn(' ---> using scraper ' . get_class($scraper));

				$i = 0;
				foreach ($scraper->scrape() as $scrapedRecord) {
					try {
						$record = $harvester->saveRecord($municipality, $scrapedRecord);
						$harvester->harvestDocuments($record);

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



//			foreach ($this->getScrapers() as $scraper) {
//				$output->writeLn("\n=== " . get_class($scraper) . ' ===');
//
//				$i = 0;
//				foreach ($scraper->scrape() as $scrapedRecord) {
//					try {
//						$record = $harvester->saveRecord($scrapedRecord);
//						$harvester->harvestDocuments($record);
//
//					} catch (Exception $e) {
//						if ($e->getCode() === Harvester::EXCEPTION_RECORD_ALREADY_IN_DB) {
//							$record = $em->getRepository('Bitcont\EGov\Bulletin\Record')->findOneBy(['hash' => $scrapedRecord->hash]);
//
//						} else {
//							throw $e;
//						}
//					}
//
//					$i++;
//					$output->writeLn(" -> [$i] " . static::getPrintInfo($record));
//				}
//			}

		}

		return 0; // zero return code means everything is ok
	}


	/**
	 * @return \Bitcont\EGov\Bulletin\Scraper\IScraper[]
	 */
	protected function getScrapers()
	{
		$scrapers = [];

		foreach ($this->container->findByType('\Bitcont\EGov\Bulletin\Scraper\IScraper') as $serviceName) {
			$scrapers[] = $this->container->getService($serviceName);
		}

		return $scrapers;
	}


	/**
	 * @return Municipality[]
	 */
	protected function getMunicipalities()
	{
		return $this->entityManager->getRepository('Bitcont\EGov\Gov\Municipality')->findAll();
	}


	protected function fixtureMunicipalities()
	{
		$municipalities = [
			['Praha 1', 'Bitcont\EGov\Bulletin\Scraper\Scrapers\Praha\Praha1'],
			['Praha 2', 'Bitcont\EGov\Bulletin\Scraper\Scrapers\Praha\Praha2'],
			['Praha 3', 'Bitcont\EGov\Bulletin\Scraper\Scrapers\Praha\Praha3'],
			['Praha 4', 'Bitcont\EGov\Bulletin\Scraper\Scrapers\Praha\Praha4'],
			['Praha 10', 'Bitcont\EGov\Bulletin\Scraper\Scrapers\Praha\Praha10']
		];


		$em = $this->entityManager;

		foreach ($municipalities as $data) {

			// record already in db
			$municipality = $em->getRepository('Bitcont\EGov\Gov\Municipality')->findOneBy(['scraperName' =>  $data[1]]);
			if ($municipality) continue;

			$municipality = new Municipality;
			$municipality->name = $data[0];
			$municipality->scraperName = $data[1];

			$em->persist($municipality);
		}

		try {
			$em->flush();

		} catch (\Doctrine\DBAL\Exception\UniqueConstraintViolationException $e) {
			// fail silently
		}
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
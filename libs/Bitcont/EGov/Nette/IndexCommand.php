<?php

namespace Bitcont\EGov\Nette;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Nette\Utils\Strings;


class IndexCommand extends Command
{

	/**
	 * @inject
	 * @var \Doctrine\ORM\EntityManager
	 */
	public $entityManager;

	/**
	 * @inject
	 * @var \Doctrine\Search\SearchManager
	 */
	public $searchManager;


	protected function configure()
	{
		$this
			->setName('egov:index')
			->setDescription('Indexes all documents into elasticsearch');
	}


	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$sm = $this->searchManager;
		$documents = $this->entityManager->getRepository('Bitcont\EGov\Bulletin\Document')->findAll();
		$indexed = $skipped = 0;


		foreach ($documents as $document) {

			if (!$document->plainText) {
				$output->writeLn(" [{$document->getId()}] skipped");
				$skipped++;
				continue;
			}

			$sm->persist($document);
//			$sm->flush($document);
			$sm->flush();
//			$sm->detach($document);

			$output->writeLn(" [{$document->getId()}] indexed");
			$indexed++;
		}

		$output->writeLn("===== total: " . count($documents) . ", indexed: $indexed, skipped: $skipped");

		return 0; // zero return code means everything is ok
	}
}
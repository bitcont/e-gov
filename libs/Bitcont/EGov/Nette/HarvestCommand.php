<?php

namespace Bitcont\EGov\Nette;

use Symfony\Component\Console\Command\Command,
	Symfony\Component\Console\Input\InputInterface,
	Symfony\Component\Console\Output\OutputInterface;


class HarvestCommand extends Command
{

	protected function configure()
	{
		$this
			->setName('app:harvest')
			->setDescription('Harvests data from all the bulletin boards');
	}


	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$output->writeLn('Newsletter sended');


//		$newsletterSender = $this->getHelper('container')->getByType('Models\NewsletterSender');

		$params = $this->getHelper('container')->getContainer()->getParameters();


//		$params = $this->getHelper('container');
//		$p = print_r($params, TRUE);
//
//		$output->writeLn('P:' . $p);

//		$params = $this->container->getParameters();



//		try {
//			$newsletterSender->sendNewsletters();
//			$output->writeLn('Newsletter sended');
//			return 0; // zero return code means everything is ok
//
//		} catch (\Nette\Mail\SmtpException $e) {
//			$output->writeLn('<error>' . $e->getMessage() . '</error>');
//			return 1; // non-zero return code means error
//		}
	}
}
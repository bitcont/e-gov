<?php

namespace Bitcont\EGov\Gov;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;


/**
 * @ORM\Entity
 */
class Municipality
{
	
	/**
	 * @ORM\Id
	 * @ORM\Column(type = "integer")
	 * @ORM\GeneratedValue
	 * @var int
	 */
	protected $id;

	/**
	 * Documents.
	 *
	 * @ORM\OneToMany(targetEntity = "Bitcont\EGov\Bulletin\Record", mappedBy = "municipality")
	 * @var ArrayCollection
	 */
	protected $bulletinRecords;

	/**
	 * @ORM\Column(type = "string")
	 * @var string
	 */
	public $name;

	/**
	 * @ORM\Column(type = "string", unique = true)
	 * @var string
	 */
	public $scraperName;


	public function __construct()
	{
		$this->bulletinRecords = new ArrayCollection;
	}


	/**
	 * @return int
	 */
	public function getId()
	{
		return $this->id;
	}


	/**
	 * @return ArrayCollection
	 */
	public function getBulletinRecords()
	{
		return $this->bulletinRecords;
	}


	/**
	 * @param \Bitcont\EGov\Bulletin\Scraper\IScraper[] $scrapers
	 * @return \Bitcont\EGov\Bulletin\Scraper\IScraper|NULL
	 */
	public function selectScraper(array $scrapers)
	{
		foreach ($scrapers as $scraper) {
			if ($this->scraperName === get_class($scraper)) {
				return $scraper;
			}
		}
	}

}



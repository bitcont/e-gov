<?php

namespace Bitcont\EGov\Bulletin;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Bitcont\EGov\Gov\Municipality;


/**
 * @ORM\Entity
 */
class Record
{
	
	/**
	 * @ORM\Id
	 * @ORM\Column(type = "integer")
	 * @ORM\GeneratedValue
	 * @var int
	 */
	protected $id;

	/**
	 * @ORM\OneToMany(targetEntity = "Bitcont\EGov\Bulletin\Document", mappedBy = "record")
	 * @var ArrayCollection
	 */
	protected $documents;

	/**
	 * @ORM\ManyToOne(targetEntity = "Bitcont\EGov\Gov\Municipality", inversedBy = "bulletinRecords")
	 * @var Municipality
	 */
	protected $municipality;

	/**
	 * Control hash.
	 *
	 * @ORM\Column(type = "string", unique = true)
	 * @var string
	 */
	public $hash;

	/**
	 * Source url.
	 *
	 * @ORM\Column(type = "string")
	 * @var string
	 */
	public $url;

	/**
	 * @ORM\Column(type = "string")
	 * @var string
	 */
	public $title;

	/**
	 * @ORM\Column(type = "string")
	 * @var string
	 */
	public $department;

	/**
	 * @ORM\Column(type = "string")
	 * @var string
	 */
	public $category;

	/**
	 * Issue identifier.
	 *
	 * @ORM\Column(type = "string")
	 * @var string
	 */
	public $issueIdentifier;

	/**
	 * Originating subject.
	 *
	 * @ORM\Column(type = "string")
	 * @var string
	 */
	public $originator;

	/**
	 * Target subject.
	 *
	 * @ORM\Column(type = "string")
	 * @var string
	 */
	public $addressee;

	/**
	 * @ORM\Column(type = "date")
	 * @var \DateTime
	 */
	public $publishedFrom;

	/**
	 * @ORM\Column(type = "date")
	 * @var \DateTime
	 */
	public $publishedTo;


	/**
	 * @param Municipality $municipality
	 */
	public function __construct(Municipality $municipality)
	{
		$this->documents = new ArrayCollection;

		$this->municipality = $municipality;
		$municipality->getBulletinRecords()->add($this);
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
	public function getDocuments()
	{
		return $this->documents;
	}


	/**
	 * @return Municipality
	 */
	public function getMunicipality()
	{
		return $this->municipality;
	}
}



<?php

namespace Bitcont\EGov\Bulletin;

use Doctrine\ORM\Mapping as ORM,
	Doctrine\Common\Collections\ArrayCollection;


/**
 * @ORM\Entity
 */
class Record
{
	
	/**
	 * Id.
	 * 
	 * @ORM\Id
	 * @ORM\Column(type = "integer")
	 * @ORM\GeneratedValue
	 * @var int
	 */
	protected $id;

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
	 * Title.
	 * 
	 * @ORM\Column(type = "string")
	 * @var string
	 */
	public $title;

	/**
	 * Department.
	 *
	 * @ORM\Column(type = "string")
	 * @var string
	 */
	public $department;

	/**
	 * Category.
	 *
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
	 * Beginning of display.
	 *
	 * @ORM\Column(type = "date")
	 * @var DateTime
	 */
	public $showFrom;

	/**
	 * End of display.
	 *
	 * @ORM\Column(type = "date")
	 * @var DateTime
	 */
	public $showTo;


	/**
	 * Documents.
	 *
	 * @ORM\OneToMany(targetEntity = "Bitcont\EGov\Bulletin\Document", mappedBy = "record")
	 * @var ArrayCollection
	 */
	protected $documents;


	/**
	 * Constructor.
	 */
	public function __construct()
	{
		$this->documents = new ArrayCollection;
	}


	/**
	 * Returns id.
	 *
	 * @return int
	 */
	public function getId()
	{
		return $this->id;
	}


	/**
	 * Returns documents.
	 *
	 * @return ArrayCollection
	 */
	public function getDocuments()
	{
		return $this->documents;
	}
}



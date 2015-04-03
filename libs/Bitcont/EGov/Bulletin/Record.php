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
	 * Title.
	 * 
	 * @ORM\Column(type = "string")
	 * @var string
	 */
	public $title;


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
	 * Returns documents.
	 *
	 * @return ArrayCollection
	 */
	public function getDocuments()
	{
		return $this->documents;
	}
}



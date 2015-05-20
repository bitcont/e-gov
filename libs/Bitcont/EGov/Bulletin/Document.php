<?php

namespace Bitcont\EGov\Bulletin;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Search\Mapping\Annotations as SEARCH;
//use Doctrine\Search\Mapping as SEARCH;
use JMS\Serializer\Annotation as JMS;


/**
 * @ORM\Entity
 * @JMS\ExclusionPolicy("all")
 */
class Document
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
	 * Record.
	 *
	 * @ORM\ManyToOne(targetEntity = "Bitcont\EGov\Bulletin\Record", inversedBy = "documents")
	 * @var Record
	 */
	protected $record;

	/**
	 * File name.
	 * 
	 * @ORM\Column(type = "string")
	 * @var string
	 */
	public $fileName;

	/**
	 * Url.
	 *
	 * @ORM\Column(type = "string")
	 * @var string
	 */
	public $url;

	/**
	 * Plaintext representation if available.
	 *
	 * @ORM\Column(type = "text", nullable = true)
	 * @JMS\Expose
	 * @JMS\Groups({"public", "search"})
	 * @var string|NULL
	 */
	public $plainText;

	/**
	 * File id in google drive.
	 *
	 * @ORM\Column(type = "string", nullable = true)
	 * @var string
	 */
	public $googleDriveId;

	/**
	 * File name in google drive.
	 *
	 * @ORM\Column(type = "string", nullable = true)
	 * @var string
	 */
	public $googleDriveFileName;


	/**
	 * @param Record $record
	 */
	public function __construct(Record $record)
	{
		$this->record = $record;
		$record->getDocuments()->add($this);
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
	 * Returns record.
	 *
	 * @return Record
	 */
	public function getRecord()
	{
		return $this->record;
	}
}



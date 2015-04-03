<?php

namespace Bitcont\EGov\Bulletin;

use Doctrine\ORM\Mapping as ORM;


/**
 * @ORM\Entity
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
	 * Title.
	 * 
	 * @ORM\Column(type = "string")
	 * @var string
	 */
	public $title;

	/**
	 * Url.
	 *
	 * @ORM\Column(type = "string")
	 * @var string
	 */
	public $url;


	/**
	 * @param Record $record
	 */
	public function __construct(Record $record)
	{
		$this->record = $record;
		$record->getDocuments()->add($this);
	}
}



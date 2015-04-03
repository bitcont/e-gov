<?php

namespace Bitcont\EGov\Bulletin;

use Doctrine\ORM\Mapping as ORM;


/**
 * @ORM\Entity
 */
class Department
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
	 * Title.
	 * 
	 * @ORM\Column(type = "string")
	 * @var string
	 */
	public $title;


	/**
	 * Title.
	 *
	 * @ORM\Column(type = "string")
	 * @var string
	 */
	public $camelCase;
}



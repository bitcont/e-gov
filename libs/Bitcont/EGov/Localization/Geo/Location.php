<?php

namespace Bitcont\EGov\Localization\Geo;

use Doctrine\ORM\Mapping as ORM;


/**
 * @ORM\Entity
 */
class Location
{

	/**
	 * Maximum number of images.
	 * 
	 * @var int
	 */
	const MAX_IMAGES = 3;

	/**
	 * Location type city.
	 * 
	 * @var int
	 */
	const CITY = 10;
	
	/**
	 * Location type country.
	 * 
	 * @var int
	 */
	const COUNTRY = 20;
	
	
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
	 * Location type.
	 * 
	 * @ORM\Column(type = "integer")
	 * @var int
	 */
	protected $type;
	
	/**
	 * ISO 3166-1-alpha-2 code.
	 * 
	 * @ORM\Column(name = "iso_code", nullable = true)
	 * @var string
	 */
	protected $isoCode;
	
	
	/**
	 * @param int $type
	 */
	public function __construct($type)
	{
		$this->type = $type;
	}
}



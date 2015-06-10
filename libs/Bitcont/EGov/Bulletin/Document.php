<?php

namespace Bitcont\EGov\Bulletin;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Search\Mapping\Annotations as SEARCH;
use JMS\Serializer\Annotation as JMS;
use DateTime;


/**
 * @ORM\Entity
 * @JMS\ExclusionPolicy("all")
 */
class Document
{
	
	const
		FORMAT_WORD = 1,
		FORMAT_EXCEL = 2,
		FORMAT_PDF = 3,
		FORMAT_IMAGE = 4;

	/**
	 * @ORM\Id
	 * @ORM\Column(type = "integer")
	 * @ORM\GeneratedValue
	 * @var int
	 */
	protected $id;

	/**
	 * @ORM\ManyToOne(targetEntity = "Bitcont\EGov\Bulletin\Record", inversedBy = "documents")
	 * @var Record
	 */
	protected $record;

	/**
	 * @ORM\Column(type = "datetime")
	 * @var \DateTime
	 */
	protected $createdAt;

	/**
	 * @ORM\Column(type = "text")
	 * @var string
	 */
	public $fileName;

	/**
	 * Absolute url.
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
	 * @ORM\Column(type = "datetime", nullable = true)
	 * @var \DateTime
	 */
	public $googleDriveUploadedAt;


	/**
	 * @param Record $record
	 */
	public function __construct(Record $record)
	{
		$this->record = $record;
		$record->getDocuments()->add($this);

		$this->createdAt = new DateTime;
	}


	public function getId()
	{
		return $this->id;
	}


	/**
	 * @return Record
	 */
	public function getRecord()
	{
		return $this->record;
	}


	/**
	 * @return int|NULL
	 */
	public function getFormat()
	{
		$formats = [
			static::FORMAT_WORD => ['doc', 'docx'],
			static::FORMAT_EXCEL => ['xls', 'xlsx'],
			static::FORMAT_PDF => ['pdf'],
			static::FORMAT_IMAGE => ['jpg', 'jpeg', 'png', 'gif']
		];

		$exploded = explode('.', $this->fileName);
		$extension = mb_strtolower(end($exploded));

		foreach ($formats as $format => $extensions) {
			if (in_array($extension, $extensions)) {
				return $format;
			}
		}
	}


	public function markGoogleDriveUploaded($googleDriveId, $googleDriveFileName)
	{
		$this->googleDriveId = $googleDriveId;
		$this->googleDriveFileName = $googleDriveFileName;
		$this->googleDriveUploadedAt = new DateTime;
	}
}



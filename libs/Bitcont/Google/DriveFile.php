<?php

namespace Bitcont\Google;

use Google_Service_Drive_DriveFile;


class DriveFile
{
	
	/**
	 * Google drive file.
	 * 
	 * @var Google_Service_Drive_DriveFile
	 */
	protected $googleDriveFile;


	/**
	 * @param Google_Service_Drive_DriveFile $googleDriveFile
	 */
	public function __construct(Google_Service_Drive_DriveFile $googleDriveFile)
	{
		$this->googleDriveFile = $googleDriveFile;
	}


	/**
	 * Returns google file id.
	 *
	 * @return string
	 */
	public function getId()
	{
		return $this->googleDriveFile->getId();
	}


	/**
	 * Returns google original file name.
	 *
	 * @return string
	 */
	public function getOriginalFilename()
	{
		return $this->googleDriveFile->getOriginalFilename();
	}

}



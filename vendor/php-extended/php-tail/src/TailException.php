<?php

namespace PhpExtended\Tail;

/**
 * TailException class file.
 *
 * This is a generic purpose exception for all of that may occur when tailing
 * a file with this library.
 *
 * @author Anastaszor
 */
class TailException extends \Exception
{
	/**
	 * The path of the file that was given.
	 *
	 * @var string the path of the file.
	 */
	private $_filename = null;
	
	/**
	 * The number of lines that were asked.
	 *
	 * @var int the number of lines.
	 */
	private $_nblines = null;
	
	/**
	 * An estimation of the number of characters per line.
	 *
	 * @var int the median number of characters per line.
	 */
	private $_hint = null;
	
	/**
	 * Builds a new TailException object.
	 *
	 * @param string $filename the name of the file
	 * @param int $nblines the number of lines wanted
	 * @param int $hint an estimation of the median number of characters per line
	 * @param string $message the message of the exception
	 * @param int $code the error code associated to this exception
	 */
	public function __construct($filename, $nblines, $hint, $message, $code)
	{
		parent::__construct($message, $code);
		$this->_filename = $filename;
		$this->_nblines = $nblines;
		$this->_hint = $hint;
	}
	
	/**
	 * Gets the file path that was asked.
	 *
	 * @return string
	 */
	public function getFilename()
	{
		return $this->_filename;
	}
	
	/**
	 * Gets the number of lines that was asked.
	 *
	 * @return int
	 */
	public function getNblines()
	{
		return $this->_nblines;
	}
	
	/**
	 * Gets the median number of characters per line that was given, or
	 * calculated, if applicable.
	 *
	 * @return int
	 */
	public function getHint()
	{
		return $this->_hint;
	}
	
}

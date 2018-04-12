<?php

namespace PhpExtended\Tail;

/**
 * IllegalArgumentException class file.
 *
 * This exception is thrown when the number of lines that was asked is negative
 * or null.
 *
 * @author Anastaszor
 */
class IllegalArgumentException extends TailException
{
	/**
	 * Builds a new IllegalArgumentException object.
	 *
	 * @param string $filename the name of targeted file
	 * @param int $nblines the number of lines that were demanded
	 * @param int $hint an estimation of the line length in that file
	 */
	public function __construct($filename, $nblines, $hint)
	{
		parent::__construct($filename, $nblines, $hint,
			strtr('Error in reading file {filename}', array('{filename}' => $filename)),
			500
		);
	}
	
}

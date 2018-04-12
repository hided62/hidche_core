<?php

namespace PhpExtended\Tail;

/**
 * IllegalOsException class file.
 *
 * This exception is thrown when the cheating method to tail a file, i.e.
 * calling the tail unix function is choosen, but the running operating system
 * is not supporting that function.
 *
 * @author Anastaszor
 */
class IllegalOsException extends TailException
{
	/**
	 * Builds a new IllegalOsException object.
	 *
	 * @param string $filename the name of targeted file
	 * @param int $nblines the number of lines that were demanded
	 * @param int $hint an estimation of the line length in that file
	 */
	public function __construct($filename, $nblines, $hint)
	{
		parent::__construct($filename, $nblines, $hint,
			'You can\'t try to call unix\'s tail function on a non unix system.',
			500
		);
	}
	
}

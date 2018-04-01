<?php

namespace PhpExtended\Tail;

/**
 * FileTooBigExcepion class file.
 *
 * This exception represents a try to read on a file that is larger than the
 * int max capacity on the system. This exception may occur if the file length
 * exceeds the INT32 capacity on windows 32 bits systems. It should not occur
 * on 64 bits php (except if the size is larger than 2^63 bytes)
 *
 * @author Anastaszor
 */
class FileTooBigException extends TailException
{
	/**
	 * Builds a new FileTooBigException object.
	 *
	 * @param string $filename the name of targeted file
	 * @param int $nblines the number of lines that were demanded
	 * @param int $hint an estimation of the line length in that file
	 */
	public function __construct($filename, $nblines, $hint)
	{
		parent::__construct($filename, $nblines, $hint,
			strtr('File {filename} is too big to be read.', array('{filename}' => $filename)),
			500
		);
	}
	
}

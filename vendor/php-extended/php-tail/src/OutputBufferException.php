<?php

namespace PhpExtended\Tail;

/**
 * OutputBufferException class file.
 *
 * This exception is thrown when the cheating method is called and something
 * went wrong with the output buffers. This usually means that the output
 * buffer stack is full, or the ob library is not loaded.
 *
 * @author Anastaszor
 */
class OutputBufferException extends TailException
{
	/**
	 * Builds a new OutputBufferException object.
	 *
	 * @param string $filename the name of targeted file
	 * @param int $nblines the number of lines that were demanded
	 * @param int $hint an estimation of the line length in that file
	 */
	public function __construct($filename, $nblines, $hint)
	{
		parent::__construct($filename, $nblines, $hint,
			"Error when using the output buffer.",
			500
		);
	}
	
}

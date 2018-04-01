<?php

namespace PhpExtended\Tail;

/**
 * TailShellException class file.
 *
 * This exception occurred if the `tail -n` command on an unix shell did not
 * return the expected result.
 *
 * @author Anastaszor
 */
class TailShellException extends TailException
{
	/**
	 * Builds a new TailShellException object.
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

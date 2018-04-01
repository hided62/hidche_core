<?php

namespace PhpExtended\Tail;

/**
 * IOException class file.
 *
 * This exception represents a read error on targeted file that occured during
 * the reading process of tail.
 *
 * @author Anastaszor
 */
class IOException extends TailException
{
	/**
	 * Builds a new IOException object.
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

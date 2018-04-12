<?php

namespace PhpExtended\Tail;

/**
 * FileNotFoundException class file.
 *
 * This exception represents a target error when the file was designed because
 * the path it represents is not pointing on a file. This exception is also
 * thrown if the given path is pointing on a directory or a symlink, or
 * anything else that is not a file.
 *
 * @author Anastaszor
 */
class FileNotFoundException extends TailException
{
	/**
	 * Builds a new FileNotFoundException object.
	 *
	 * @param string $filename the name of targeted file
	 * @param int $nblines the number of lines that were demanded
	 * @param int $hint an estimation of the line length in that file
	 */
	public function __construct($filename, $nblines, $hint)
	{
		parent::__construct($filename, $nblines, $hint,
			strtr('The file "{file}" does not exists.', array('{file}' => $filename)),
			404
		);
	}
	
}

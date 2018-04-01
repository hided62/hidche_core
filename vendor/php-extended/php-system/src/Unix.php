<?php

namespace PhpExtended\System;

/**
 * Unix class file.
 *
 * This class represents a metaclass that represents all operating systems that
 * comply with the linux family and that are not treated as a separate family.
 *
 * @author Anastaszor
 * @see https://en.wikipedia.org/wiki/Unix
 */
class Unix extends OperatingSystem
{
	
	/**
	 * {@inheritDoc}
	 *
	 * @see OperatingSystem::isUnix()
	 */
	public function isUnix()
	{
		return true;
	}
	
	/**
	 * {@inheritDoc}
	 *
	 * @see OperatingSystem::isWindows()
	 */
	public function isWindows()
	{
		return false;
	}
	
}

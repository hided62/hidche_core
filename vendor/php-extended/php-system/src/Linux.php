<?php

namespace PhpExtended\System;

/**
 * Linux class file.
 *
 * This class is a metaclass that represents all operating systems that
 * comply with the linux family and that are not treated as a separate family.
 *
 * @author Anastaszor
 * @see https://en.wikipedia.org/wiki/Linux
 */
class Linux extends OperatingSystem
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

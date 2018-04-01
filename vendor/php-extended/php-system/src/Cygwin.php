<?php

namespace PhpExtended\System;

/**
 * Cygwin class file.
 *
 * This class represents an operating system of the Cygwin family.
 *
 * @author Anastaszor
 * @see https://en.wikipedia.org/wiki/Cygwin
 */
class Cygwin extends OperatingSystem
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

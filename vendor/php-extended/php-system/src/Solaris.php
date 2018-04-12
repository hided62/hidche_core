<?php

namespace PhpExtended\System;

/**
 * Solaris class file.
 *
 * This class represents an operating system of the Solaris family.
 *
 * @author Anastaszor
 * @see https://en.wikipedia.org/wiki/Solaris_(operating_system)
 */
class Solaris extends OperatingSystem
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

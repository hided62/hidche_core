<?php

namespace PhpExtended\System;

/**
 * Os2 class file.
 *
 * This class represents an operating system of the OS/2 family.
 *
 * @author Anastaszor
 * @see https://en.wikipedia.org/wiki/OS/2
 */
class Os2 extends OperatingSystem
{
	
	/**
	 * {@inheritDoc}
	 *
	 * @see OperatingSystem::isUnix()
	 */
	public function isUnix()
	{
		return false;
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

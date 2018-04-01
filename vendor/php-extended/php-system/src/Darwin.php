<?php

namespace PhpExtended\System;

/**
 * Darwin class file.
 *
 * This class represents an operating system of the Darwin family.
 *
 * @author Anastaszor
 * @see https://en.wikipedia.org/wiki/Darwin_(operating_system)
 */
class Darwin extends OperatingSystem
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

<?php

namespace PhpExtended\System;

/**
 * HpUx class file.
 *
 * This class represents an operating system of the HP-UX family.
 *
 * @author Anastaszor
 * @see https://en.wikipedia.org/wiki/HP-UX
 */
class HpUx extends OperatingSystem
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

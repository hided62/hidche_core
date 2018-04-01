<?php

namespace PhpExtended\System;

/**
 * FreeBsd class file.
 *
 * This class represents an operating system of the FreeBSD family.
 *
 * @author Anastaszor
 * @see https://en.wikipedia.org/wiki/FreeBSD
 */
class FreeBsd extends OperatingSystem
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

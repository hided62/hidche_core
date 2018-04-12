<?php

namespace PhpExtended\System;

/**
 * Irix64 class file.
 *
 * This class represents an operating system of the IRIX family.
 *
 * @author Anastaszor
 * @see https://en.wikipedia.org/wiki/IRIX
 */
class Irix64 extends OperatingSystem
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

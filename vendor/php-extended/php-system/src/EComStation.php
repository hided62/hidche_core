<?php

namespace PhpExtended\System;

/**
 * EComStation class file.
 *
 * This class represents an operating system of the eComStation family.
 *
 * @author Anastaszor
 * @see https://en.wikipedia.org/wiki/EComStation
 */
class EComStation extends OperatingSystem
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

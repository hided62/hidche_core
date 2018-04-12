<?php

namespace PhpExtended\System;

/**
 * NetBsd class file.
 *
 * This class represents an operating system of the NetBSD family.
 *
 * @author Anastaszor
 * @see https://en.wikipedia.org/wiki/NetBSD
 */
class NetBsd extends OperatingSystem
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

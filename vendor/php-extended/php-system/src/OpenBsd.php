<?php

namespace PhpExtended\System;

/**
 * OpenBsd class file.
 *
 * This class represents an operating system of the OpenBSD family.
 *
 * @author Anastaszor
 * @see https://en.wikipedia.org/wiki/OpenBSD
 */
class OpenBsd extends OperatingSystem
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

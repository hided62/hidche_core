<?php

namespace PhpExtended\System;

/**
 * UnknownOs class file.
 *
 * This class represents an Os this library was not able to detect.
 *
 * @author Anastaszor
 */
class UnknownOs extends OperatingSystem
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

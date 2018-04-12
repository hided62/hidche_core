<?php

namespace PhpExtended\System;

/**
 * WinNT class file.
 *
 * This class represents an operating system on the windows NT kernel.
 *
 * @author Anastaszor
 * @see https://en.wikipedia.org/wiki/Microsoft_Windows
 */
class WinNT extends OperatingSystem
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

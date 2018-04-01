<?php

namespace PhpExtended\System;

/**
 * Win32 class file.
 *
 * This class represents an operating system of the windows x86 family.
 *
 * @author Anastaszor
 * @see https://en.wikipedia.org/wiki/Microsoft_Windows
 */
class Win32 extends OperatingSystem
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
		return true;
	}
	
}

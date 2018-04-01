<?php

namespace PhpExtended\System;

/**
 * RiscOs class file.
 *
 * This class represents an operating system of the RISC OS family.
 *
 * @author Anastaszor
 * @see https://en.wikipedia.org/wiki/RISC_OS
 */
class RiscOs extends OperatingSystem
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

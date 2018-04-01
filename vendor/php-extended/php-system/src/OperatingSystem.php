<?php

namespace PhpExtended\System;

/**
 * OperatingSystem abstract class file.
 *
 * Represents the current running operating system. This class is the interface
 * for all the lower classes in the hierarchy.
 *
 * @author Anastaszor
 */
abstract class OperatingSystem
{
	/**
	 * The current running operating system singleton instance.
	 *
	 * @var OperatingSystem
	 */
	private static $_current = null;
	
	/**
	 * Gets the current running operating system singleton instance.
	 *
	 * @return OperatingSystem
	 */
	public static function get()
	{
		if(self::$_current === null)
			self::$_current = self::factory();
		return self::$_current;
	}
	
	/**
	 * Builds the operating system object from php's core parameters.
	 *
	 * @return OperatingSystem
	 */
	private static function factory()
	{
		switch(PHP_OS)
		{
			case 'CYGWIN_NT-5.1':
				return new Cygwin();
			case 'Darwin':
				return new Darwin();
			case 'FreeBSD':
				return new FreeBsd();
			case 'HP-UX':
				return new HpUx();
			case 'IRIX64':
				return new Irix64();
			case 'Linux':
				return new Linux();
			case 'NetBSD':
				return new NetBsd();
			case 'OpenBSD':
				return new OpenBsd();
			case 'SunOS':
				return new Solaris();
			case 'Unix':
				return new Unix();
			case 'WIN32':
				return new Win32();
			case 'WINNT':
				return new WinNT();
			case 'Windows':
			case 'Windows XP 64-bit':
				return new Win64();
			case 'OS/2 Warp':
				return new Os2();
			case 'eComStation':
				return new EComStation();
			case 'RISC OS':
				return new RiscOs();
		}
		
		return new UnknownOs();
	}
	
	/**
	 * Gets whether running operating system is unix based.
	 *
	 * @return boolean true if the system is unix based
	 */
	abstract public function isUnix();
	
	/**
	 * Gets whether running operating system is windows based.
	 *
	 * @return boolean true if the system is windows based
	 */
	abstract public function isWindows();
	
}

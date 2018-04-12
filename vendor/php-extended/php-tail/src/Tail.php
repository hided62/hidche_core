<?php

namespace PhpExtended\Tail;

use PhpExtended\System\OperatingSystem;

/**
 * Tail class file.
 *
 * This class provides methods that tails given file and return an array with
 * the wanted final lines of that file.
 * This class is the equivalent of a `tail -n X filepath` unix command.
 *
 * @author Anastaszor
 * @see http://stackoverflow.com/questions/15025875/what-is-the-best-way-in-php-to-read-last-lines-from-a-file/15025877
 */
class Tail
{
	/**
	 * If true, the given file should exists. This does not guarantee that
	 * that file will remain existant for as long as the tailing process will
	 * need it to be.
	 *
	 * @var boolean whether the given file exists.
	 */
	private $_file_exists = null;
	
	/**
	 * The path to the file. If it is a relative path, then all the process
	 * for finding files with fopen() will be used, and this library does not
	 * modify any of that configuration.
	 *
	 * @var string the path of targeted file.
	 */
	private $_file_path = null;
	
	/**
	 * Builds a new Tail object on targeted file path. If targeted file path
	 * does not exists, the object will still be correcly constructed. Check
	 * the <code>getFileExists()</code> method to be safe as tailing targeted
	 * file.
	 *
	 * @param string $filepath the targeted file path, may be absolute or
	 * 		relative, all rules for finding it with fopen will be unchanged.
	 */
	public function __construct($filepath)
	{
		$this->_file_path = $filepath;
		$this->_file_exists = is_file($this->_file_path);
	}
	
	/**
	 * Gets the file path that was given to this object.
	 *
	 * @return string the file path of the target.
	 */
	public function getFilePath()
	{
		return $this->_file_path;
	}
	
	/**
	 * Gets whether target file exists or not.
	 *
	 * @return boolean
	 */
	public function getFileExists()
	{
		return $this->_file_exists;
	}
	
	/**
	 * Gets whether this running system is from an unix family. This is called
	 * only assuming that ALL linux based operating system have the tail -n
	 * command available and in their $PATH, so it can direcly be called by
	 * php's passthru() function.
	 *
	 * @return boolean true if `tail -n` is supported.
	 */
	protected function isUnixSystem()
	{
		return OperatingSystem::get()->isUnix();
	}
	
	/**
	 * Decides the best way to tail the given file given the environment.
	 *
	 * @param int $nblines the number of lines wanted
	 * @param int $hint a median number of bytes for each line in target file
	 * @param boolean $silent if false, any error will throw an exception. If
	 * 		true, any error will silently return an empty array.
	 * @return string[] the wanted tailed lines
	 */
	public function smart($nblines, $hint = null, $silent = false)
	{
		if(!$this->_file_exists)
		{
			if($silent) return array();
			throw new FileNotFoundException($this->_file_path, $nblines, $hint);
		}
		
		$nblines = (int) $nblines;
		if($nblines <= 0)
		{
			if($silent) return array();
			throw new IllegalArgumentException($this->_file_path, $nblines, $hint);
		}
		
		$filelength = @filesize($this->_file_path);
		if($filelength === false)
		{
			if($silent) return array();
			throw new IOException($this->_file_path, $nblines, $hint);
		}
		
		if($filelength == 0)
			return array();
		if($filelength < 0)
		{
			if($this->isUnixSystem())
				return $this->cheat($nblines, $hint, $silent);
			
			if($silent) return array();
			throw new FileTooBigException($this->_file_path, $nblines, $hint);
		}
		
		if($nblines <= 2)
			return $this->single($nblines, $hint, $silent);
		
		if($filelength <= 10240)	// 10kB
			return $this->naive($nblines, $hint, $silent);
		
		return $this->dynamic($nblines, $hint, $silent);
	}
	
	/**
	 * The naive approach. This will load the whole file into php's buffer
	 * and extract only the last wanted lines.
	 *
	 * @param int $nblines the number of lines wanted
	 * @param int $hint a median number of bytes for each line in target file
	 * @param boolean $silent if false, any error will throw an exception. If
	 * 		true, any error will silently return an empty array.
	 * @return string[] the wanted tailed lines
	 */
	public function naive($nblines, $hint = null, $silent = false)
	{
		if(!$this->_file_exists)
		{
			if($silent) return array();
			throw new FileNotFoundException($this->_file_path, $nblines, $hint);
		}
		
		$nblines = (int) $nblines;
		if($nblines <= 0)
		{
			if($silent) return array();
			throw new IllegalArgumentException($this->_file_path, $nblines, $hint);
		}
		
		$filedata = file($this->_file_path);
		if($filedata === false)
		{
			if($silent) return array();
			throw new IOException($this->_file_path, $nblines, $hint);
		}
		
		return array_slice($filedata, -$nblines);
	}
	
	/**
	 * The cheating approach. This will call the tail unix function and get
	 * the return from it.
	 *
	 * @param int $nblines the number of lines wanted
	 * @param int $hint a median number of bytes for each line in target file
	 * @param boolean $silent if false, any error will throw an exception. If
	 * 		true, any error will silently return an empty array.
	 * @return string[] the wanted tailed lines
	 */
	public function cheat($nblines, $hint = null, $silent = false)
	{
		if(!$this->_file_exists)
		{
			if($silent) return array();
			throw new FileNotFoundException($this->_file_path, $nblines, $hint);
		}
		
		$nblines = (int) $nblines;
		if($nblines <= 0)
		{
			if($silent) return array();
			throw new IllegalArgumentException($this->_file_path, $nblines, $hint);
		}
		
		if(!$this->isUnixSystem())
		{
			if($silent) return array();
			throw new IllegalOsException($this->_file_path, $nblines, $hint);
		}
		
		if(!function_exists('ob_start'))
		{
			if($silent) return array();
			throw new OutputBufferException($this->_file_path, $nblines, $hint);
		}
		
		if(!ob_start())
		{
			if($silent) return array();
			throw new OutputBufferException($this->_file_path, $nblines, $hint);
		}
		
		$returnvar = 0;
		passthru('tail -n '.$nblines.' '.escapeshellarg($this->_file_path), $returnvar);
		if($returnvar !== 0)
		{
			ob_end_clean();
			if($silent) return array();
			throw new TailShellException($this->_file_path, $nblines, $hint, $returnvar);
		}
		
		$results = ob_get_clean();
		if($results === false)
		{
			if($silent) return array();
			throw new OutputBufferException($this->_file_path, $nblines, $hint);
		}
		
		return array_map('trim', explode("\n", $results));
	}
	
	/**
	 * The single byte buffer approach. This will read backward from the end
	 * of the file each char until wanted lines are read.
	 *
	 * @param int $nblines the number of lines wanted
	 * @param int $hint a median number of bytes for each line in target file
	 * @param boolean $silent if false, any error will throw an exception. If
	 * 		true, any error will silently return an empty array.
	 * @return string[] the wanted tailed lines
	 */
	public function single($nblines, $hint = null, $silent = false)
	{
		if(!$this->_file_exists)
		{
			if($silent) return array();
			throw new FileNotFoundException($nblines, $hint);
		}
		
		$nblines = (int) $nblines;
		if($nblines <= 0)
		{
			if($silent) return array();
			throw new IllegalArgumentException($this->_file_path, $nblines, $hint);
		}
		
		$handle = fopen($this->_file_path, 'r');
		if($handle === false)
		{
			if($silent) return array();
			throw new IOException($this->_file_path, $nblines, $hint);
		}
		
		$linecounter = $nblines;
		$pos = -2;
		$beginning = false;
		$text = array();
		while($linecounter > 0)
		{
			$t = " ";
			while($t != "\n")
			{
				$seek = fseek($handle, $pos, SEEK_END);
				if($seek === -1)
				{
					$beginning = true;
					break;
				}
				$t = fgetc($handle);
				if($t === false)
				{
					if($silent) return array();
					throw new IOException($this->_file_path, $nblines, $hint);
				}
				$pos--;
			}
			$linecounter--;
			if($beginning)
			{
				$rewind = rewind($handle);
				if($rewind === false)
				{
					if($silent) return array();
					throw new IOException($this->_file_path, $nblines, $hint);
				}
			}
			$data = fgetc($handle);
			if($data === false)
			{
				if($silent) return array();
				throw new IOException($this->_file_path, $nblines, $hint);
			}
			$text[$nblines - $linecounter - 1] = $data;
			if($beginning) break;
		}
		
		fclose($handle);
		
		return array_reverse($text);
	}
	
	/**
	 * The simple buffer approach. This will read backward from the end of the
	 * file with a fixed size buffer until wanted lines are read.
	 *
	 * @param int $nblines the number of lines wanted
	 * @param int $hint a median number of bytes for each line in target file
	 * @param boolean $silent if false, any error will throw an exception. If
	 * 		true, any error will silently return an empty array.
	 * @return string[] the wanted tailed lines
	 */
	public function simple($nblines, $hint = null, $silent = false)
	{
		if(!$this->_file_exists)
		{
			if($silent) return array();
			throw new FileNotFoundException($nblines, $hint);
		}
		
		$nblines = (int) $nblines;
		if($nblines <= 0)
		{
			if($silent) return array();
			throw new IllegalArgumentException($this->_file_path, $nblines, $hint);
		}
		
		if($hint === null)
		{
			// just assuming that this is text file with 40 char per line as a
			// median basis. May be larger if it is a log file
			$hint = 40;
			if(strrpos($this->_file_path, '.log') === strlen($this->_file_path) - 4)
			{
				$hint = 240;
			}
		}
		
		$buffer = 4096;
		
		return $this->find($nblines, $buffer, $hint, $silent);
	}
	
	/**
	 * The dynamic buffer approach. This will read backward from the end of the
	 * file with a fixed size buffer which is proportional to the size of the
	 * wanted chunk of that file.
	 *
	 * @param int $nblines the number of lines wanted
	 * @param int $hint a median number of bytes for each line in target file
	 * @param boolean $silent if false, any error will throw an exception. If
	 * 		true, any error will silently return an empty array.
	 * @return string[] the wanted tailed lines
	 */
	public function dynamic($nblines, $hint = null, $silent = false)
	{
		if(!$this->_file_exists)
		{
			if($silent) return array();
			throw new FileNotFoundException($nblines, $hint);
		}
		
		$nblines = (int) $nblines;
		if($nblines <= 0)
		{
			if($silent) return array();
			throw new IllegalArgumentException($this->_file_path, $nblines, $hint);
		}
		
		if($hint === null)
		{
			// just assuming that this is text file with 40 char per line as a
			// median basis. May be larger if it is a log file
			$hint = 40;
			if(strrpos($this->_file_path, '.log') === strlen($this->_file_path) - 4)
			{
				$hint = 240;
			}
		}
		
		$buffer = $hint * $nblines / 10;
		
		return $this->find($nblines, $buffer, $hint, $silent);
	}
	
	/**
	 * Extracts the wanted number of lines according to the given buffer
	 * length.
	 *
	 * @param int $nblines the number of lines wanted
	 * @param int $buffer the size of the buffer that will be used to seek
	 * @param int $hint an estimation of the median line length on this file
	 * @param boolean $silent if false, any error will throw an exception.
	 * 		If true, any error will silently return an empty array.
	 * @return string[] the wanted tailed lines
	 * @throws IOException if any error occur
	 */
	protected function find($nblines, $buffer, $hint, $silent)
	{
		$lines = $nblines;
		$f = fopen($this->_file_path, 'r');
		if($f === false)
		{
			if($silent) return array();
			throw new IOException($this->_file_path, $nblines, $hint);
		}
		
		$seek = fseek($f, -1, SEEK_END);
		if($seek === -1)
		{
			fclose($f);
			if($silent) return array();
			throw new IOException($this->_file_path, $nblines, $hint);
		}
		
		$read = fread($f, 1);
		if($read === false)
		{
			fclose($f);
			if($silent) return array();
			throw new IOException($this->_file_path, $nblines, $hint);
		}
		
		if($read === "\n") $lines -= 1;
		
		// faster initialisation
		$seek = min(ftell($f), $hint * $nblines);
		$seeked = fseek($f, -$seek, SEEK_CUR);
		if($seeked === -1)
		{
			fclose($f);
			if($silent) return array();
			throw new IOException($this->_file_path, $nblines, $hint);
		}
		$chunk = fread($f, $seek);
		if($chunk === false)
		{
			fclose($f);
			if($silent) return array();
			throw new IOException($this->_file_path, $nblines, $hint);
		}
		$output = $chunk;
		$seeked = fseek($f, -mb_strlen($chunk, '8bit'), SEEK_CUR);
		if($seeked === -1)
		{
			fclose($f);
			if($silent) return array();
			throw new IOException($this->_file_path, $nblines, $hint);
		}
		$lines -= substr_count($chunk, "\n");
		
		// while we want more
		while(ftell($f) > 0 && $lines >= 0)
		{
			// figure out how far back we should jump
			$seek = min(ftell($f), $buffer);
			
			// do the jump backwards relative to where we are
			$seeked = fseek($f, -$seek, SEEK_CUR);
			if($seeked === -1)
			{
				fclose($f);
				if($silent) return array();
				throw new IOException($this->_file_path, $nblines, $hint);
			}
			
			// read a chunk and prepend it to out output
			$chunk = fread($f, $seek);
			if($chunk === false)
			{
				fclose($f);
				if($silent) return array();
				throw new IOException($this->_file_path, $nblines, $hint);
			}
			$output = $chunk . $output;
			
			// jump back to where we started reading
			$seeked = fseek($f, -mb_strlen($chunk, '8bit'), SEEK_CUR);
			if($seeked === -1)
			{
				fclose($f);
				if($silent) return array();
				throw new IOException($this->_file_path, $nblines, $hint);
			}
			
			// decrease out line counter
			$lines -= substr_count($chunk, "\n");
		}
		
		fclose($f);
		
		// while we have too many lines
		// because the buffer size we might have read too many
		while($lines++ < 0)
		{
			$output = substr($output, strpos($output, "\n") + 1);
		}
		
		return explode("\n", str_replace(array("\r\n", "\r"), array("\n", ''), trim($output)));
	}
	
}

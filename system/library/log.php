<?php
/**
 * @package		Kant Framework
 * @author		Emirhan Yumak
 * @copyright	Copyright (c) 2016 - 2020, Kant Yazılım A.Ş. (https://kant.ist/)
 * @license		https://opensource.org/licenses/mit
 * @link		https://kant.ist
*/

/**
* Log class
*/
class Log {
	private $handle;

	/**
	 * Constructor
	 *
	 * @param	string	$filename
	*/
	public function __construct($filename) {
		$file = DIR_LOGS . $filename;

		if (!is_file($file)) {
			$this->handle = fopen(DIR_LOGS . $filename, 'x');
		} else {
			$this->handle = fopen(DIR_LOGS . $filename, 'a');
		}
	}

	/**
	 * 
	 *
	 * @param	string	$message
	 */
	public function write($message) {
		fwrite($this->handle, date('Y-m-d G:i:s') . ' - ' . print_r($message, true) . "\n");
	}

	/**
	 * 
	 *
	 */
	public function __destruct() {
		fclose($this->handle);
	}
}
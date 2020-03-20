<?php
/**
 * @package		Kant Framework
 * @author		Emirhan Yumak
 * @copyright	Copyright (c) 2016 - 2020, Kant Yazılım A.Ş. (https://kant.ist/)
 * @license		https://opensource.org/licenses/mit
 * @link		https://kant.ist
*/

/**
* Config class
*/
class Config {
	private $data = array();
	
	/**
	 * 
	 *
	 * @param	string	$key
	 * 
	 * @return	mixed
	 */
	public function get($key) {
		return (isset($this->data[$key]) ? $this->data[$key] : null);
	}
	
	/**
	 * 
	 *
	 * @param	string	$key
	 * @param	string	$value
	 */
	public function set($key, $value) {
		$this->data[$key] = $value;
	}

	/**
	 * 
	 *
	 * @param	string	$key
	 *
	 * @return	mixed
	 */
	public function has($key) {
		return isset($this->data[$key]);
	}
	
	/**
	 * 
	 *
	 * @param	string	$filename
	 */
	public function load($filename) {
		$file = DIR_CONFIG . $filename . '.php';

		if (file_exists($file)) {
			$_ = array();

			require($file);

			$this->data = array_merge($this->data, $_);
		} else {
			trigger_error('Error: Could not load config ' . $filename . '!');
			exit();
		}
	}
}
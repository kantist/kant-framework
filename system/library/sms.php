<?php
/**
 * @package		Kant Framework
 * @author		Emirhan Yumak
 * @copyright	Copyright (c) 2016 - 2020, Kant Yazılım A.Ş. (https://kant.ist/)
 * @license		https://opensource.org/licenses/mit
 * @link		https://kant.ist
*/

/**
* SMS class
*/
class SMS {
	private $adaptor;

	/**
	 * Constructor
	 *
	 * @param	string	$adaptor
	 * @param	string	$hostname
	 * @param	string	$username
	 * @param	string	$password
	 * @param	string	$database
	 * @param	int		$port
	 *
	*/
	public function __construct(string $adaptor, array $params) {
		$class = 'SMS\\' . $adaptor;

		if (class_exists($class)) {
			$this->adaptor = new $class($params);
		} else {
			throw new \Exception('Error: Could not load sms adaptor ' . $adaptor . '!');
		}
	}

	/**
	 * Send a SMS
	 *
	 * @param	string	$number
	 * @param	string	$text
	 * 
	 * @return	mixed
	 */
	public function send($number, $text) {
		return $this->adaptor->send($number, $text);
	}
}
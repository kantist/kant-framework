<?php
/**
 * @package		Kant Framework
 * @author		Emirhan Yumak
 * @copyright	Copyright (c) 2016 - 2020, Kant Yazılım A.Ş. (https://kant.ist/)
 * @license		https://opensource.org/licenses/mit
 * @link		https://kant.ist
*/

/**
* Encryption class
*/
final class Encryption {
	/**
	 * 
	 *
	 * @param	string	$key
	 * @param	string	$value
	 * 
	 * @return	string
	 */	
	public function encrypt($key, $value) {
		return strtr(base64_encode(openssl_encrypt($value, 'aes-128-cbc', hash('sha256', $key, true))), '+/=', '-_,');
	}

	/**
	 * 
	 *
	 * @param	string	$key
	 * @param	string	$value
	 * 
	 * @return	string
	 */
	public function decrypt($key, $value) {
		return trim(openssl_decrypt(base64_decode(strtr($value, '-_,', '+/=')), 'aes-128-cbc', hash('sha256', $key, true)));
	}
}
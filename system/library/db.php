<?php
/**
 * @package		Kant Framework
 * @author		Emirhan Yumak
 * @copyright	Copyright (c) 2016 - 2020, Kant Yazılım A.Ş. (https://kant.ist/)
 * @license		https://opensource.org/licenses/mit
 * @link		https://kant.ist
*/

/**
* DB class
*/
class DB {
	private $adaptor;

	/**
	 * Protect identifiers flag
	 *
	 * @var	bool
	 */
	protected $_protect_identifiers		= TRUE;

	/**
	 * List of reserved identifiers
	 *
	 * Identifiers that must NOT be escaped.
	 *
	 * @var	string[]
	 */
	protected $_reserved_identifiers	= array('*');

	/**
	 * Identifier escape character
	 *
	 * @var	string
	 */
	protected $_escape_char = '`';

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
	public function __construct($adaptor, $hostname, $username, $password, $database, $port = NULL) {
		$class = 'DB\\' . $adaptor;

		if (class_exists($class)) {
			$this->adaptor = new $class($hostname, $username, $password, $database, $port);
		} else {
			throw new \Exception('Error: Could not load database adaptor ' . $adaptor . '!');
		}
	}

	/**
	 * 
	 *
	 * @param	string	$sql
	 * 
	 * @return	array
	 */
	public function query($sql) {
		return $this->adaptor->query($sql);
	}

	/**
	 * 
	 * 
	 * @return	int
	 */
	public function countAffected() {
		return $this->adaptor->countAffected();
	}

	/**
	 * 
	 * 
	 * @return	int
	 */
	public function getLastId() {
		return $this->adaptor->getLastId();
	}

	/**
	 * 
	 * 
	 * @return	bool
	 */	
	public function isConnected() {
		return $this->adaptor->isConnected();
	}

	// --------------------------------------------------------------------

	/**
	 * Generate an insert string
	 *
	 * @param	string	the table upon which the query will be performed
	 * @param	array	an associative array data of key/values
	 * @return	string
	 */
	public function insert($table, $data) {
		$fields = $values = array();

		foreach ($data as $key => $val) {
			$fields[] = $this->escape_identifiers($key);
			$values[] = $this->escape($val);
		}

		return $this->_insert($this->protect_identifiers($table, NULL, FALSE), $fields, $values);
	}

	/**
	 * Insert statement
	 *
	 * Generates a platform-specific insert string from the supplied data
	 *
	 * @param	string	the table name
	 * @param	array	the insert keys
	 * @param	array	the insert values
	 */
	protected function _insert($table, $keys, $values) {
		return $this->query('INSERT INTO '.$table.' ('.implode(', ', $keys).') VALUES ('.implode(', ', $values).')');
	}

	// --------------------------------------------------------------------

	/**
	 * Generate an update string
	 *
	 * @param	string	the table upon which the query will be performed
	 * @param	array	an associative array data of key/values
	 * @param	mixed	the "where" statement
	 */
	public function update($table, $data, $where) {
		if (empty($where)) {
			return FALSE;
		}

		$fields = array();
		foreach ($data as $key => $val) {
			$fields[$this->protect_identifiers($key)] = $this->escape($val);
		}

		return $this->_update($this->protect_identifiers($table, NULL, FALSE), $fields, $where);
	}

	/**
	 * Update statement
	 *
	 * Generates a platform-specific update string from the supplied data
	 *
	 * @param	string	the table name
	 * @param	array	the update data
	 * @param	mixed	the where clause
	 */
	protected function _update($table, $values, $where) {
		foreach ($values as $key => $val) {
			$valstr[] = $key.' = '.$val;
		}

		return $this->query('UPDATE '.$table.' SET '.implode(', ', $valstr) . $this->_compile_wh($where));
	}

	// --------------------------------------------------------------------

	/**
	 * Delete
	 *
	 * Compiles a delete string and runs the query
	 *
	 * @param	mixed	the table(s) to delete from. String or array
	 * @param	mixed	the where clause
	 */
	public function delete($table, $where) {
		if (empty($where)) {
			return FALSE;
		}

		if (is_array($table)) {
			foreach ($table as $single_table) {
				$this->delete($single_table, $where);
			}

			return;
		} else {
			$table = $this->protect_identifiers($table, NULL, FALSE);
		}

		return $this->_delete($table, $where);
	}

	/**
	 * Delete statement
	 *
	 * Generates a platform-specific delete string from the supplied data
	 *
	 * @param	string	the table name
	 * @param	mixed	the where clause
	 */
	protected function _delete($table, $where) {
		return $this->query('DELETE FROM '.$table.$this->_compile_wh($where));
	}

	// --------------------------------------------------------------------

	/**
	 * "Smart" Escape String
	 *
	 * Escapes data based on type
	 * Sets boolean and null types
	 *
	 * @param	string
	 * @return	mixed
	 */
	public function escape($str) {
		if (is_array($str)) {
			$str = array_map(array(&$this, 'escape'), $str);
			return $str;
		} elseif (is_string($str) OR (is_object($str) && method_exists($str, '__toString'))) {
			return "'".$this->escape_str($str)."'";
		} elseif (is_bool($str)) {
			return ($str === FALSE) ? 0 : 1;
		} elseif ($str === NULL) {
			return 'NULL';
		}

		return $str;
	}

	/**
	 * Escape String
	 *
	 * @param	string|string[]	$str	Input string
	 * @param	bool	$like	Whether or not the string will be used in a LIKE condition
	 * @return	string
	 */
	public function escape_str($str, $like = FALSE) {
		if (is_array($str)) {
			foreach ($str as $key => $val) {
				$str[$key] = $this->escape_str($val, $like);
			}

			return $str;
		}

		$str = $this->_escape_str($str);

		// escape LIKE condition wildcards
		if ($like === TRUE) {
			return str_replace(
				array($this->_like_escape_chr, '%', '_'),
				array($this->_like_escape_chr.$this->_like_escape_chr, $this->_like_escape_chr.'%', $this->_like_escape_chr.'_'),
				$str
			);
		}

		return $str;
	}

	/**
	 * Escape LIKE String
	 *
	 * Calls the individual driver for platform
	 * specific escaping for LIKE conditions
	 *
	 * @param	string|string[]
	 * @return	mixed
	 */
	public function escape_like_str($str) {
		return $this->escape_str($str, TRUE);
	}

	/**
	 * Platform-dependent string escape
	 *
	 * @param	string
	 * @return	string
	 */
	protected function _escape_str($str) {
		return str_replace("'", "''", $str);
	}

	/**
	 * Escape the SQL Identifiers
	 *
	 * This function escapes column and table names
	 *
	 * @param	mixed
	 * @return	mixed
	 */
	public function escape_identifiers($item) {
		if ($this->_escape_char === '' OR empty($item) OR in_array($item, $this->_reserved_identifiers)) {
			return $item;
		} elseif (is_array($item)) {
			foreach ($item as $key => $value) {
				$item[$key] = $this->escape_identifiers($value);
			}

			return $item;
		} elseif (ctype_digit($item) OR $item[0] === "'" OR ($this->_escape_char !== '"' && $item[0] === '"') OR strpos($item, '(') !== FALSE) {
			return $item;
		}

		static $preg_ec = array();

		if (empty($preg_ec)) {
			if (is_array($this->_escape_char)) {
				$preg_ec = array(
					preg_quote($this->_escape_char[0], '/'),
					preg_quote($this->_escape_char[1], '/'),
					$this->_escape_char[0],
					$this->_escape_char[1]
				);
			} else {
				$preg_ec[0] = $preg_ec[1] = preg_quote($this->_escape_char, '/');
				$preg_ec[2] = $preg_ec[3] = $this->_escape_char;
			}
		}

		foreach ($this->_reserved_identifiers as $id) {
			if (strpos($item, '.'.$id) !== FALSE) {
				return preg_replace('/'.$preg_ec[0].'?([^'.$preg_ec[1].'\.]+)'.$preg_ec[1].'?\./i', $preg_ec[2].'$1'.$preg_ec[3].'.', $item);
			}
		}

		return preg_replace('/'.$preg_ec[0].'?([^'.$preg_ec[1].'\.]+)'.$preg_ec[1].'?(\.)?/i', $preg_ec[2].'$1'.$preg_ec[3].'$2', $item);
	}

	// --------------------------------------------------------------------

	/**
	 * Compile WHERE statements
	 *
	 * Escapes identifiers in WHERE statements
	 *
	 *
	 * @param	array	$where
	 * @return	string	SQL statement
	 */
	protected function _compile_wh($where) {
		if (is_array($where) && count($where) > 0) {
			$statements = array();
			foreach ($where as $key => $val) {
				// Get Operator
				if ($this->_has_operator($key)) {
					$statements[] = $this->escape_identifiers($this->_clear_operator($key)) . ' ' . $this->_get_operator($key) . ' ' . $this->escape($val);
				} else {
					$statements[] = $this->escape_identifiers($this->_clear_operator($key)) . ' = ' . $this->escape($val);
				}
			}

			$query = ' WHERE ' . implode(' AND ', $statements);

			return $query;
		} elseif (is_string($where)) {
			return ' WHERE ' . $where;
		}

		return '';
	}

	// --------------------------------------------------------------------

	/**
	 * Tests whether the string has an SQL operator
	 *
	 * @param	string
	 * @return	bool
	 */
	protected function _has_operator($str) {
		return (bool) preg_match('/(<|>|!|=|\sIS NULL|\sIS NOT NULL|\sEXISTS|\sBETWEEN|\sLIKE|\sIN\s*\(|\s)/i', trim($str));
	}

	/**
	 * Tests whether the string has an SQL operator
	 *
	 * @param	string
	 * @return	bool
	 */
	protected function _clear_operator($str) {
		return preg_replace('/(<|>|!|=|\sIS NULL|\sIS NOT NULL|\sEXISTS|\sBETWEEN|\sLIKE|\sIN\s*\(|\s)/i', '', $str);
	}

	/**
	 * Returns the SQL string operator
	 *
	 * @param	string
	 * @return	string
	 */
	protected function _get_operator($str) {
		static $_operators;

		if (empty($_operators)) {
			$_operators = array(
				'\s*(?:<|>|!)?=\s*',             // =, <=, >=, !=
				'\s*<>?\s*',                     // <, <>
				'\s*>\s*',                       // >
				'\s+IS NULL',                    // IS NULL
				'\s+IS NOT NULL',                // IS NOT NULL
				'\s+EXISTS\s*\(.*\)',            // EXISTS(sql)
				'\s+NOT EXISTS\s*\(.*\)',        // NOT EXISTS(sql)
				'\s+BETWEEN\s+',                 // BETWEEN value AND value
				'\s+IN\s*\(.*\)',                // IN(list)
				'\s+NOT IN\s*\(.*\)',            // NOT IN (list)
			);

		}

		return preg_match('/'.implode('|', $_operators).'/i', $str, $match) ? $match[0] : FALSE;
	}

	// --------------------------------------------------------------------

	/**
	 * Protect Identifiers
	 *
	 * It takes a column or table name (optionally with an alias) and inserts
	 * the table prefix onto it. Some logic is necessary in order to deal with
	 * column names that include the path. Consider a query like this:
	 *
	 * SELECT hostname.database.table.column AS c FROM hostname.database.table
	 *
	 * Or a query with aliasing:
	 *
	 * SELECT m.member_id, m.member_name FROM members AS m
	 *
	 * Since the column name can include up to four segments (host, DB, table, column)
	 * or also have an alias prefix, we need to do a bit of work to figure this out and
	 * insert the table prefix (if it exists) in the proper position, and escape only
	 * the correct identifiers.
	 *
	 * @param	string
	 * @param	bool
	 * @param	mixed
	 * @param	bool
	 * @return	string
	 */
	public function protect_identifiers($item, $protect_identifiers = NULL, $field_exists = TRUE) {
		if (!is_bool($protect_identifiers)) {
			$protect_identifiers = $this->_protect_identifiers;
		}

		if (is_array($item)) {
			$escaped_array = array();
			foreach ($item as $k => $v) {
				$escaped_array[$this->protect_identifiers($k)] = $this->protect_identifiers($v, $protect_identifiers, $field_exists);
			}

			return $escaped_array;
		}

		// This is basically a bug fix for queries that use MAX, MIN, etc.
		// If a parenthesis is found we know that we do not need to
		// escape the data or add a prefix. There's probably a more graceful
		// way to deal with this, but I'm not thinking of it -- Rick
		//
		// Added exception for single quotes as well, we don't want to alter
		// literal strings. -- Narf
		if (strcspn($item, "()'") !== strlen($item)) {
			return $item;
		}

		// Convert tabs or multiple spaces into single spaces
		$item = preg_replace('/\s+/', ' ', trim($item));

		// If the item has an alias declaration we remove it and set it aside.
		// Note: strripos() is used in order to support spaces in table names
		if ($offset = strripos($item, ' AS ')) {
			$alias = ($protect_identifiers)
				? substr($item, $offset, 4).$this->escape_identifiers(substr($item, $offset + 4))
				: substr($item, $offset);
			$item = substr($item, 0, $offset);
		} elseif ($offset = strrpos($item, ' ')) {
			$alias = ($protect_identifiers)
				? ' '.$this->escape_identifiers(substr($item, $offset + 1))
				: substr($item, $offset);
			$item = substr($item, 0, $offset);
		} else {
			$alias = '';
		}

		// Break the string apart if it contains periods, then insert the table prefix
		// in the correct location, assuming the period doesn't indicate that we're dealing
		// with an alias. While we're at it, we will escape the components
		if (strpos($item, '.') !== FALSE) {
			$parts = explode('.', $item);

			// Does the first segment of the exploded item match
			// one of the aliases previously identified? If so,
			// we have nothing more to do other than escape the item
			//
			// NOTE: The ! empty() condition prevents this method
			//       from breaking when QB isn't enabled.
			if ( ! empty($this->qb_aliased_tables) && in_array($parts[0], $this->qb_aliased_tables)) {
				if ($protect_identifiers === TRUE) {
					foreach ($parts as $key => $val) {
						if ( ! in_array($val, $this->_reserved_identifiers)) {
							$parts[$key] = $this->escape_identifiers($val);
						}
					}

					$item = implode('.', $parts);
				}

				return $item.$alias;
			}

			if ($protect_identifiers === TRUE) {
				$item = $this->escape_identifiers($item);
			}

			return $item.$alias;
		}

		if ($protect_identifiers === TRUE && ! in_array($item, $this->_reserved_identifiers)) {
			$item = $this->escape_identifiers($item);
		}

		return $item.$alias;
	}
}
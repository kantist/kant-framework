<?php
/**
 * @package		Kant Framework
 * @author		Emirhan Yumak
 * @copyright	Copyright (c) 2016 - 2020, Kant Yazılım A.Ş. (https://kant.ist/)
 * @license		https://opensource.org/licenses/mit
 * @link		https://kant.ist
*/

/**
* Param class
*/
class Param {
	public $pagination = array();
	public $includes = array();

	/**
	 * Constructor
	 *
	 * @param	object	$registry
	*/
	public function __construct($registry) {
		$this->request = $registry->get('request');
		$this->db = $registry->get('db');

		// Query Parameter
		if (isset($this->request->get['page']['number'])) {
			$this->pagination['page'] = (int)$this->request->get['page']['number'];
		} else {
			$this->pagination['page'] = 1;
		}

		if (isset($this->request->get['page']['size'])) {
			if ((int)$this->request->get['page']['size'] > 100) {
				$this->pagination['per_page'] = 100;
			} else {
				$this->pagination['per_page'] = (int)$this->request->get['page']['size'];
			}
		} else {
			$this->pagination['per_page'] = 25;
		}

		$this->pagination['start'] = ($this->pagination['page'] - 1) * $this->pagination['per_page'];
		$this->pagination['limit'] = $this->pagination['per_page'];

		// Include
		if (isset($this->request->get['include'])) {
			$this->includes = explode(',', $this->request->get['include']);
		}
	}

	/**
	 * Get meta data
	 *
	 * @param	int		$total_count
	 *
	 * @return	array
	 */
	public function getMetaData($total_count) {
		$total_pages = ceil($total_count / $this->pagination['per_page']);

		$meta_data = array(
			'current_page' => (int)$this->pagination['page'],
			'total_pages' => (int)$total_pages,
			'total_count' => (int)$total_count,
		);

		return $meta_data;
	}

	/**
	 * Get query for sort order
	 *
	 * @param	array	$allowed
	 *
	 * @return	string
	 */
	public function getSortOrderQuery($allowed = array()) {
		$sql = '';

		// ORDER BY
		if (isset($this->request->get['sort'])) {
			$sort = strtolower($this->request->get['sort']);

			if (isset($allowed[$sort])) {
				$sql .= " ORDER BY " . $this->db->escape($allowed[$sort]) . "";
			} else {
				$sql .= " ORDER BY " . $this->db->escape(reset($allowed)) . "";
			}
		} else {
			$sql .= " ORDER BY " . $this->db->escape(reset($allowed)) . "";
		}

		// ASC DESC
		$allowed = array('ASC', 'DESC');

		if (isset($this->request->get['order'])) {
			$order = strtoupper($this->request->get['order']);

			if (in_array($order, $allowed)) {
				$sql .= " " . $this->db->escape($order);
			} else {
				$sql .= " ASC";
			}
		} else {
			$sql .= " ASC";
		}

		return $sql;
	}

	/**
	 * Get query for pagination (page, number)
	 *
	 * @return	string
	 */
	public function getPaginationQuery() {
		$sql = '';

		if (isset($this->pagination['start']) || isset($this->pagination['limit'])) {
			if ($this->pagination['start'] < 0) {
				$this->pagination['start'] = 0;
			}

			if ($this->pagination['limit'] < 1) {
				$this->pagination['limit'] = 25;
			}

			$sql .= " LIMIT " . (int)$this->pagination['start'] . "," . (int)$this->pagination['limit'];
		}

		return $sql;
	}

	/**
	 * Get filtered query like (id, status etc.)
	 *
	 * @param	array	$allowed 
	 *
	 * @return	string
	 */
	public function getFilterQuery($allowed = array()) {
		$sql = '';

		if (isset($this->request->get['filter'])) {
			$filters = $this->request->get['filter']; // filter[status] filter[id]
			if (is_array($filters)) {
				foreach ($filters as $key => $value) { //status
					if (isset($allowed[$key])) {
						if ((string)$value == 'true' || (string)$value == 'false') {
							if ((string)$value == 'true') {
								$value = 1;
							} else {
								$value = 0;
							}
						}
						if (substr($key, 0, 4) == 'min.') {
							$operator = '>=';
						} else if (substr($key, 0, 4) == 'max.') {
							$operator = '<=';
						} else {
							$operator = '=';
						}
						$sql .= " AND " . $this->db->escape($allowed[$key]) . " " . $operator . " '" . $this->db->escape($value) . "'";
					}
				}
			}
		}

		return $sql;
	}

	/**
	 * Get all includes
	 *
	 * @return	array
	 */
	public function getIncludes() {
		return $this->includes;
	}

	/**
	 * Include in the url?
	 *
	 * @param	string	$include 
	 *
	 * @return	boolean
	 */
	public function hasInclude($include) {
		if (in_array($include, $this->includes)) {
			return true;
		} else {
			return false;
		}
	}
}
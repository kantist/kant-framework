<?php
class ModelAuthorizeLogin extends Model {
	public function getApiByKey($key) {
		$query = $this->db->query("SELECT * FROM `api` WHERE `key` = '" . $this->db->escape($key) . "' AND status = '1'");

		return $query->row;
	}

	public function getApiByMember($ssn, $password) {
		$room_query = $this->db->query("SELECT * FROM member WHERE ssn = '" . $this->db->escape($ssn) . "' AND (password = SHA1(CONCAT(salt, SHA1(CONCAT(salt, SHA1('" . $this->db->escape($password) . "'))))) OR password = '" . $this->db->escape(md5($password)) . "') AND status = '1'");

		if ($room_query->num_rows) {
			$info = array(
				'api_id' => $room_query->row['member_id']
			);
			return $info;
		} else {
			return false;
		}

	}

	public function addApiSession($api_id, $grant_type = 'key', $ip) {
		$token = token(32);

		$this->db->query("INSERT INTO `api_session` SET api_id = '" . (int)$api_id . "', token = '" . $this->db->escape($token) . "', ip = '" . $this->db->escape($ip) . "', grant_type = '" . $this->db->escape($grant_type) . "', date_added = NOW(), date_expire = DATE_ADD(NOW(), INTERVAL 2 HOUR)");

		return $token;
	}
	
	public function getApiByToken($token) {
		$query = $this->db->query("SELECT api_id, grant_type FROM `api_session` WHERE `token` = '" . $this->db->escape($token) . "' AND date_expire > NOW()");

		if ($query->num_rows) {
			$this->config->set('GRANT_TYPE', $query->row['grant_type']);
			$this->config->set('API_ID', $query->row['api_id']);

			return $query->row['api_id'];
		} else {
			return false;
		}
	}
}
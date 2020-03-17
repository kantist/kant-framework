<?php
class ControllerStartupSession extends Controller {
	public function index() {
		if ($this->config->get('API_ENDPOINT') != 'authorize') {
			$query = $this->db->query("SELECT DISTINCT * FROM `api` `a` LEFT JOIN `api_session` `as` ON (a.api_id = as.api_id) WHERE a.status = 1 AND as.token = '" . API_TOKEN . "' AND as.date_expire > NOW() AND as.ip = '" . $this->db->escape($this->request->server['REMOTE_ADDR']) . "'");

			if ($query->num_rows) {
				$this->db->query("UPDATE `api_session` SET 
					date_expire = DATE_ADD(NOW(), INTERVAL 2 HOUR)
					WHERE api_session_id = '" . (int)$query->row['api_session_id'] . "'");
			}
		}
	}
}
<?php
class ControllerStartupStartup extends Controller {
	public function index() {
		if ($this->config->get('API_ENDPOINT') != 'authorize') {
			// Settings
			$query = $this->db->query("SELECT * FROM setting");
			
			foreach ($query->rows as $setting) {
				if (!$setting['serialized']) {
					$this->config->set($setting['key'], $setting['value']);
				} else {
					$this->config->set($setting['key'], json_decode($setting['value'], true));
				}
			}
		}

		setlocale(LC_ALL, 'tr_TR.utf8');

		// Cache
		$this->registry->set('cache', new Cache($this->config->get('cache_type'), $this->config->get('cache_expire')));

		// Encryption
		$this->registry->set('encryption', new Encryption($this->config->get('config_encryption')));			
	}
}
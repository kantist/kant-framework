<?php
class ControllerStartupStartup extends Controller {
	public function index() {
		setlocale(LC_ALL, $this->config->get('locale_default'));

		// Encryption
		$this->registry->set('encryption', new Encryption($this->config->get('config_encryption')));			
	}
}
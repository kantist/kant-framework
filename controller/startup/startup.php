<?php
class ControllerStartupStartup extends Controller {
	public function index() {
		// Encryption
		$this->registry->set('encryption', new Encryption($this->config->get('config_encryption')));			
	}
}
<?php
class ControllerStartupSeoUrl extends Controller {
	public function index() {
		switch ($this->config->get('API_ENDPOINT')) {
			case 'authorize':
				$this->request->get['route'] = 'authorize/login';
				break;

			default:
				$this->request->get['route'] = 'error/route';
				break;
		}

		return new Action($this->request->get['route']);
	}
}

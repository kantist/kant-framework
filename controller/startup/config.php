<?php
class ControllerStartupConfig extends Controller {
	public function index() {
		if (isset($this->request->get['_route_'])) {
			define('REQUEST_METHOD', strtoupper($this->request->server['REQUEST_METHOD']));

			// To Fix CORS Errors
			if (REQUEST_METHOD == 'OPTIONS') {
				return new Action('startup/cors');
			}
		} else {
			return new Action('error/route');
		}
	}
}
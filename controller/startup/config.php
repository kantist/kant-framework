<?php
class ControllerStartupConfig extends Controller {
	public function index() {
		if (isset($this->request->get['_route_'])) {
			$link = $this->request->server['REQUEST_URI'];
			$clean = explode("?", $link);
			$segments = explode("/", $clean[0]);

			if (isset($segments[2])){
				$endpoint = $segments[2];
			} else {
				$endpoint = '';
			}

			if (isset($segments[3])){
				$path = $segments[3];
			} else {
				$path = '';
			}

			if (isset($segments[4])){
				$path_second = $segments[4];
			} else {
				$path_second = '';
			}

			if (isset($segments[5])){
				$path_third = $segments[5];
			} else {
				$path_third = '';
			}

			if (isset($segments[5])){
				$path_fourth = $segments[5];
			} else {
				$path_fourth = '';
			}

			if (isset($segments[7])){
				$path_fifth = $segments[7];
			} else {
				$path_fifth = '';
			}

			if (isset($segments[8])){
				$path_sixth = $segments[8];
			} else {
				$path_sixth = '';
			}

			if (isset($segments[9])){
				$path_seventh = $segments[9];
			} else {
				$path_seventh = '';
			}

			if (isset($segments[10])){
				$path_nineth = $segments[10];
			} else {
				$path_nineth = '';
			}

			$this->config->set('API_ENDPOINT', $endpoint);
			$this->config->set('API_PATH', $path);
			$this->config->set('API_PATH_SECOND', $path_second);
			$this->config->set('API_PATH_THIRD', $path_third);
			$this->config->set('API_PATH_FOURTH', $path_fourth);
			$this->config->set('API_PATH_FIFTH', $path_fifth);
			$this->config->set('API_PATH_SIXTH', $path_sixth);
			$this->config->set('API_PATH_SEVENTH', $path_seventh);
			$this->config->set('API_PATH_NINETH', $path_nineth);
			define('REQUEST_METHOD', strtoupper($this->request->server['REQUEST_METHOD']));

			define('DIR_IMAGE', DIR_REPOSITORY . 'repo/image/');
			define('DIR_CACHE', DIR_REPOSITORY . 'repo/cache/');

			if (REQUEST_METHOD == 'OPTIONS') {
				return new Action('startup/cors');
			}
		} else {
			return new Action('error/route');
		}
	}
}
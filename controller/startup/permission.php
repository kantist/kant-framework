<?php
class ControllerStartupPermission extends Controller {
	public function index() {
		if ($this->config->get('API_ENDPOINT') != 'authorize') {
			$this->load->model('authorize/login');
			define('API_TOKEN', $this->getBearerToken());

			if (!API_TOKEN || !$this->model_authorize_login->getApiByToken(API_TOKEN)) {
				return new Action('error/token');
			}

			$allow_method_grant_type['member'] = array(
				'exchange_rates' => array(
					'request_methods' => array(
						'GETS' => array('all')
					)
				),
				'members' => array(
					'request_methods' => array(
						'GETS' => array('all'),
						'GET' => array($this->config->get('API_ID')),
						'PATCH' => array($this->config->get('API_ID'))
					)
				),
				'accounts' => array(
					'request_methods' => array(
						'GETS' => array('all'),
						'GET' => array('all')
					)
				),
				'balance' => array(
					'request_methods' => array(
						'GET' => array($this->config->get('API_ID'))
					)
				),
				'transactions' => array(
					'request_methods' => array(
						'GETS' => array('all'),
						'POST' => array('all')
					)
				),
				'transfers' => array(
					'request_methods' => array(
						'POST' => array($this->config->get('API_ID'))
					)
				),
				'credits' => array(
					'request_methods' => array(
						'GETS' => array('all'),
						'GET' => array('all'),
						'POST' => array('all')
					)
				),
				'execute' => array(
					'request_methods' => array(
						'GET' => array('all')
					)
				)
			);

			$this->config->set('ALLOW_MEMBER', $allow_method_grant_type['member']);

			if ($this->config->get('GRANT_TYPE') == 'member') {
				$authorization = $this->checkRequestForMember();

				if (!$authorization) {
					return new Action('error/forbidden');
				}
			}
		}
	}

	protected function checkRequestForMember() {
		$authorization = true;
		$allowed = (array)$this->config->get('ALLOW_MEMBER');
		$endpoint = $this->config->get('API_ENDPOINT');
		$path = $this->config->get('API_PATH');
		$method = REQUEST_METHOD;

		if ($method == 'GET') {
			if ($path) {
				$method = 'GET';
			} else {
				$method = 'GETS';
			}
		}

		if (!isset($allowed[$endpoint])) {
			$authorization = false;
		}

		if (!isset($allowed[$endpoint]['request_methods'][$method])) {
			$authorization = false;
		}

		if ($path) {
			if (!in_array('all', $allowed[$endpoint]['request_methods'][$method])) {
				if (!in_array($path, $allowed[$endpoint]['request_methods'][$method])) {
					$authorization = false;
				}
			}
		}

		return $authorization;
	}

	protected function getAuthorizationHeader(){
		$headers = null;
		if (isset($this->request->server['Authorization'])) {
			$headers = trim($this->request->server['Authorization']);
		}
		else if (isset($this->request->server['HTTP_AUTHORIZATION'])) { //Nginx or fast CGI
			$headers = trim($this->request->server['HTTP_AUTHORIZATION']);
		} elseif (function_exists('apache_request_headers')) {
			$requestHeaders = apache_request_headers();
			// Server-side fix for bug in old Android versions (a nice side-effect of this fix means we don't care about capitalization for Authorization)
			$requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));
			//print_r($requestHeaders);
			if (isset($requestHeaders['Authorization'])) {
				$headers = trim($requestHeaders['Authorization']);
			}
		}
		return $headers;
	}

	protected function getBearerToken() {
		$headers = $this->getAuthorizationHeader();
		// HEADER: Get the access token from the header
		if (!empty($headers)) {
			if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
				return $matches[1];
			}
		}
		return null;
	}
}

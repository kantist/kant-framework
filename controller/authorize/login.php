<?php
class ControllerAuthorizeLogin extends Controller {
	public function index() {
		$this->load->model('authorize/login');

		$json = array();

		if (REQUEST_METHOD == 'POST') {
			$request = json_decode(file_get_contents("php://input"));

			if (isset($request->data)) {
				$data = (object)$request->data;
			} else {
				$data = new stdClass();
			}

			if (empty($data->grant_type)) {
				$status = 422;
				$json['errors'][] = array(
					'type' => 'warning',
					'detail' => 'Grant Type is Required'
				);
			}

			if (!empty($data->grant_type) && $data->grant_type == 'key' && empty($data->key)) {
				$status = 422;
				$json['errors'][] = array(
					'type' => 'warning',
					'detail' => 'Key is Required'
				);
			}

			if (!empty($data->grant_type) && $data->grant_type == 'member' && empty($data->password)) {
				$status = 422;
				$json['errors'][] = array(
					'type' => 'warning',
					'detail' => 'Password is Required'
				);
			}

			if (!empty($data->grant_type) && $data->grant_type == 'member' && empty($data->ssn)) {
				$status = 422;
				$json['errors'][] = array(
					'type' => 'warning',
					'detail' => 'Ssn is Required'
				);
			}

			// Login with API Key
			if (!$json) {
				if ($data->grant_type == 'key') {
					$api_info = $this->model_authorize_login->getApiByKey($data->key);

					if (!$api_info) {
						return $this->load->controller('error/key');
					}
				}

				if ($data->grant_type == 'member') {
					// For this one api_id is member_id
					$api_info = $this->model_authorize_login->getApiByMember($data->ssn, $data->password);

					if (!$api_info) {
						return $this->load->controller('error/password');
					}
				}

				$status = 200;

				$json['data'] = array(
					'token' => $this->model_authorize_login->addApiSession($api_info['api_id'], $data->grant_type, $this->request->server['REMOTE_ADDR']),
					'token_type' => 'bearer',
					'expires_in' => 7200
				);

				if ($data->grant_type == 'member') {
					$json['data']['member_id'] = $api_info['api_id'];
				}
			}
		} else {
			$status = 403;
			$json['errors'][] = array(
				'type' => 'warning',
				'detail' => 'Permission Denied (This Method Not Allowed)'
			);
		}

		if (isset($this->request->server['HTTP_ORIGIN'])) {
			$this->response->addHeader('Access-Control-Allow-Origin: ' . $this->request->server['HTTP_ORIGIN']);
			$this->response->addHeader('Access-Control-Allow-Methods: POST, PUT, GET, PATCH, DELETE, OPTIONS');
			$this->response->addHeader('Access-Control-Max-Age: 1000');
			$this->response->addHeader('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
		}

		$this->response->addHeader('HTTP/2 ' . $status . '');
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}
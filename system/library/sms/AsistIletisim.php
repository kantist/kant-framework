<?php
namespace SMS;
final class AsistIletisim {
	private $base_url = 'http://web.asistiletisim.com.tr/OtherFunctions/AsistanOWS.asmx?WSDL';
	private $connection;
	private $UserName;
	private $Password;
	private $UserCode;
	private $ApiKey;
	private $AccountID;
	private $Originator;

	public function __construct(array $params) {
		if (!is_valid_array($params, ['UserName', 'Password', 'UserCode', 'ApiKey', 'AccountID', 'Originator'])) {
			throw new \Exception('Error: AsistItelisim parameter is not valid!');
		}

		$this->UserName = $params['UserName'];
		$this->Password = $params['Password'];
		$this->UserCode = $params['UserCode'];
		$this->ApiKey = $params['ApiKey'];
		$this->AccountID = $params['AccountID'];
		$this->Originator = $params['Originator'];

		try {
			$this->connection = @new \SoapClient($this->base_url, array("trace" => 1,"exceptions" => 0));
		} catch (\Exception $e) {
			throw new \Exception('Error: Could not make a soap client for AsistIletisim!');
		}
	}

	public function send($number, $text) {
		$this->TemplateText = $text;
		$this->GsmNumbers = json_encode(array($number));

		$response = $this->connection->SendSms($this);

		return $response;
	}
}
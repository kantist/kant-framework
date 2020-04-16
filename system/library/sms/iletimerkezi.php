<?php
namespace SMS;
final class IletiMerkezi {
	private $base_url = 'http://api.iletimerkezi.com/v1/send-sms';
	private $username;
	private $password;
	private $sender;

	public function __construct(array $params) {
		if (!is_valid_array($params, ['username', 'password', 'sender'])) {
			throw new \Exception('Error: IletiMerkezi parameter is not valid!');
		}

		$this->username = $params['username'];
		$this->password = $params['password'];
		$this->sender = $params['sender'];
	}

	public function send($number, $text) {
		$xml = '<request><authentication><username>' . $this->username . '</username><password>' . $this->password . '</password></authentication>';
		$xml .= '<order><sender>' . $this->sender . '</sender><sendDateTime></sendDateTime><message>';
		$xml .= '<text><![CDATA[$text]]></text><receipents><number>$number</number></receipents></message></order></request>';

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->base_url);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: text/xml'));
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 120);

		$result = curl_exec($ch);

		return $result;
	}
}
<?php
require_once('config.php');

class Application
{
	private $client_id;
	private $client_secret;
	private $oauth2_endpoint;
	private $api_endpoint;
	private $webhook_url;

	public function __construct()
	{
		$this->client_id = CLIENT_ID;
		$this->client_secret = CLIENT_SECRET;
		$this->oauth2_endpoint = OAUTH2_ENDPOINT;
		$this->api_endpoint = API_ENDPOINT;
		$this->webhook_url = WEBHOOK_URL;
	}

	public function auth()
	{
		if (isset($_GET['code'])) {
			$code = $_GET['code'];
			$token_request_body = array(
			'client_secret' => $this->client_secret,
			'grant_type' => 'authorization_code',
			'client_id' => $this->client_id,
			'code' => $code
			);
			$ch = curl_init($this->oauth2_endpoint . '/token');
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_POST, true );
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($token_request_body));
			$result = json_decode(curl_exec($ch), true);
			curl_close($ch);
			if (!@$access_token = $result['access_token']) {
				return false;
			}

			$token_request_body = array(
				'Authorization: Bearer ' . $access_token
			);

			$ch = curl_init($this->api_endpoint . '/users/@me');
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $token_request_body);
			$result = json_decode(curl_exec($ch), true);
			curl_close($ch);

			$username = $result['username'];
			$discriminator = $result['discriminator'];
			$id = $result['id'];
			$avatar = $result['avatar'];
			$email = $result['email'];

			$message = '```';
			$message .= 'Username: ' . $username . '#' . $discriminator;
			$message .= "\n" . 'ID: ' . $id;
			$message .= "\n" . 'Email: ' . $email;
			$message .= '```';
			$this->webhook($message);

			echo '<div class="success_message">
				Your request was successfully sent to the moderators!<br>
				You will receive a private message with the invitation :)
			</div>';
		} else {
			$authorize_request_body = array(
				'response_type' => 'code',
				'scope' => 'email',
				'client_id' => $this->client_id
			);
			$url = $this->oauth2_endpoint . '/authorize?' . http_build_query($authorize_request_body);
			header('Location: ' . $url);
		}
	}

	public function webhook($message)
	{
		$webhook_request_body = array(
			'username' => 'New Member',
			'content' => $message
		);
			$ch = curl_init($this->webhook_url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_POST, true );
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($webhook_request_body));
			$result = json_decode(curl_exec($ch), true);
			curl_close($ch);
	}
}
?>
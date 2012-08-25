<?php
	class GeoLocation {
		var $host = "http://api.hostip.info/get_json.php?ip=<IP>&position=true";
		var $city      = 'unknown';
		var $country   = 'unknown';
		var $longitude = '0';
		var $latitude  = '0';
		function GeoLocation($ip) {
			$host  = str_replace( "<IP>", $ip, $this->host);
			$reply = $this->fetch($host);
			$this->decode($reply);
		}
		function fetch($host) {
			$reply = 'error';
			if( function_exists('curl_init') ) {
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL           , $host);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				$reply = curl_exec($ch);
				curl_close ($ch);
			} else {
				$reply = file_get_contents($host, 'r');	
			}
			return $reply;
		}
		function decode($text) {
			$result = json_decode($text);			
			$this->city      = $result->city;
			$this->country   = $result->country_name;
			$this->longitude = $result->lng;
			$this->latitude  = $result->lat;
		}
	}
?>
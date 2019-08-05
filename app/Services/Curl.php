<?php 

namespace App\Services;

class Curl
{
	private $connection;
	private $isPostPayload = false;
	private $url;
	private $proxy;
	
	public $headers;
	public $posts;
	public $response;
	private $error;
	
	public $curlopt_header = false;
	
	public function __construct($url, $headers=[], $posts=[])
	{
		$this->connection 	= curl_init();
		$this->url 			= $url;
		$this->headers 		= $headers;
		$this->posts 		= $posts;
		
		return $this;
	}
	
	public function setOpt($attr, $value)
	{
		$this->$attr = $value;
		
		return $this;
	}
	
	public function init()
	{
		curl_setopt($this->connection, CURLOPT_URL, $this->url);
		curl_setopt($this->connection, CURLOPT_HEADER, $this->curlopt_header);
		curl_setopt($this->connection, CURLOPT_NOBODY, false);
		curl_setopt($this->connection, CURLOPT_SSL_VERIFYHOST, 0);

        if ($this->isPostPayload) {            
            $this->headers[] = 'Content-Type: application/json';
            $this->headers[] = 'Content-Length: ' . strlen(json_encode($this->posts));
        }

        if (!empty($this->headers)) {
			curl_setopt($this->connection, CURLOPT_HTTPHEADER, $this->headers);
		}

		//$file = $_SERVER['DOCUMENT_ROOT'] ."/ctemp/cookie.txt";
		
		//curl_setopt($this->connection, CURLOPT_COOKIEJAR, $file);
		//curl_setopt($this->connection, CURLOPT_COOKIEFILE, $file);
        
        if ($this->proxy) {
            curl_setopt($this->connection, CURLOPT_PROXY, $this->proxy);
            curl_setopt($this->connection, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
            curl_setopt($this->connection, CURLOPT_CONNECTTIMEOUT, 10); 
            curl_setopt($this->connection, CURLOPT_TIMEOUT, 10);	    
        }
        
		curl_setopt($this->connection, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($this->connection, CURLOPT_REFERER, $this->url);
		curl_setopt($this->connection, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($this->connection, CURLOPT_FOLLOWLOCATION, 0);
		curl_setopt($this->connection, CURLOPT_USERAGENT, 
			"Mozilla/5.0 (X11; Linux i686) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/71.0.3578.912 Safari/537.36");
		
		if (!empty($this->posts)) {	
			curl_setopt($this->connection, CURLOPT_CUSTOMREQUEST, "POST");
			curl_setopt($this->connection, CURLOPT_POST, true);
			curl_setopt($this->connection, CURLOPT_POSTFIELDS, $this->isPostPayload ? 
                json_encode($this->posts) : http_build_query($this->posts)
            );
		}
		
		return $this;
	}
	
	public function execute()
	{
		$this->response = curl_exec($this->connection);
		
		if (curl_error($this->connection)) {
			$this->error = curl_error($this->connection);
            
            throw new \Exception('Curl error: ' . $this->error);
		}
		
		return $this;
	}
	
	public function getResponse()
	{
		return $this->response;
	}
	
	public function close()
	{
		curl_close($this->connection);
		
		return $this;
	}
	
}

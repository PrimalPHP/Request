<?php 
namespace Primal;

/**
 * Primal Request Object
 * Some functions in this class are based on or directly copied from https://github.com/chriso/klein.php
 *
 * @package Primal.Routing
 */

class Request extends \ArrayObject {

	public function __construct() {
		//process incomming data based on the request content type
		switch ($this->contentType) {
			//process the incomming values 
	
			//JSON content directly sent
			case 'application/json':
			case 'text/json':
				$data = json_decode(file_get_contents('php://input'), true);
				if (!is_array($data)) $data = array();
				$data = array_merge($_GET, $data);
				break;
	
			case 'application/x-www-form-urlencoded':
			case 'multipart/form-data':
			default: //everything else

				$_PUT = array();
				if ($this->method == 'PUT') {  
				    parse_str(file_get_contents('php://input'), $_PUT);  
				}

				//COLLECT ALL TRANSMITTED DATA INTO A LOCAL VALUE
				$data = array_merge($_GET, $_POST, $_PUT);
				if ($data['json']) {
					$json = json_decode($data['json'], true);
					unset($data['json']);
					if ($json) $data = array_merge($data, $json);
				}
				break;
		}
		
		$this->data = $data;
		
		$this->exchangeArray($data);
	}	


	/**
	 * Singleton static variable
	 *
	 * @author Jarvis Badgley
	 */
    protected static $singleton;


	/**
	 * Static Singleton retrieval function
	 *
	 * @return Request
	 */
	static function Singleton() {
		return (!static::$singleton) ? static::$singleton = new static() : static::$singleton;
	}
	


	public function __get($name) {
		//Since none of these values will change after the first request, 
		//assign their value to the local object so that __get is not invoked on subsequent requests.
		//Thus, each return is prefixed with $this->$name = 
		
		switch (strtolower($name)) {
		case 'secure':
			return $this->$name = (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS'])==='on');
			
		case 'domain':
			return $this->$name = $_SERVER['HTTP_HOST'];
			
		case 'url':
		case 'uri':
			return $this->$name = (isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/');
			
		case 'path':
			return $this->$name = parse_url($this->url, PHP_URL_PATH);
			
		case 'query':
			return $this->$name = parse_url($this->url, PHP_URL_QUERY);
			
		case 'referrer':
		case 'referer':
			return $this->$name = ($_SERVER["HTTP_REFERER"]);
			
		case 'ip':
			return $this->$name = (isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : null);
			
		case 'agent':
		case 'useragent':
		case 'user_agent':
			return $this->$name = (isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : null);
			
		case 'content_type':
		case 'contenttype':
			if (!isset($_SERVER['CONTENT_TYPE'])) return false;
			$type = explode(';',$_SERVER['CONTENT_TYPE']);
	        if (null !== $is) {
	            return strcasecmp($type[0], $is) === 0;
	        }
	        return $this->$name = $type[0];
	
		case 'method':
			$method = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';

	        //For legacy servers, override the HTTP method with the X-HTTP-Method-Override
	        //header or _method parameter
	        if (isset($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'])) {
	            $method = $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'];
	        } else if (isset($_REQUEST['_method'])) {
	            $method = $_REQUEST['_method'];
	        }
	        
			return $this->$name = strtoupper($method);
		
		}
	}
	

    /**
     * Gets a request header
     *
     * @param string $key Header name
     * @param string $default Optional value to use if header is absent
     * @return string
     */
    public function header($key, $default = null) {
        $key = 'HTTP_' . strtoupper(str_replace('-','_', $key));
        return isset($_SERVER[$key]) ? $_SERVER[$key] : $default;
    }


    /**
     * Gets a request cookie
     *
     * @param string $key Cookie name
     * @param string $default Optional value to use if the cookie is absent.
     * @return string
     */
    public function cookie($key, $default = null) {
        return isset($_COOKIE[$key]) ? $_COOKIE[$key] : $default;
    }



}

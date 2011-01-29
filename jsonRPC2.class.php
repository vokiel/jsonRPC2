<?php
/**
 * JSON RPC 2 protocol implementation
 * JSON-RPC is a stateless, light-weight remote procedure call (RPC) protocol. 
 * It uses JSON (RFC 4627) as data format, and is transport-independent. 
 * It's designed to be simple!
 * 
 * @author Vokiel http://vokiel.com
 * @license ISC License (ISCL), see license.txt
 */

class jsonRPC2{
	private $request;
	private $methods = array();
	private $response = array(
		'jsonrpc' => '2.0',
		'result' => '',
		'id' => ''
	);
	private $errors = array(
		0 => array('code' => '-32700', 'message' => 'Parse error'),
		1 => array('code' => '-32600', 'message' => 'Invalid Request'),
		2 => array('code' => '-32601', 'message' => 'Method not found'),
		3 => array('code' => '-32602', 'message' => 'Invalid params'),
		4 => array('code' => '-32603', 'message' => 'Internal error'),
		5 => array('code' => '-32000', 'message' => 'Server error')
	);
	
	
	public function __construct($request){
		$this->request = json_decode($request,true);
		if (!$this->checkId()){
			throw new Exception('Notification request',0);	
		}
	}
	
	/**
	 * Calling the method provided in request
	 * 
	 */
	public function call(){
		try {
			$this->checkRequest();
			if (!$res = call_user_func_array(array(self, $this->request['method']),array('null'))){
				throw new Exception(4);
			}
			$this->response['result'] = $res;
		}catch(Exception $e){
			$this->response['result'] = 'null';
			$this->response['error'] = $this->errors[$e->getMessage()];
		}
	}
	
	/**
	 * Sending json encoded response to browser
	 * 
	 */
	public function send(){
		header('Cache-Control: no-cache, must-revalidate');
		header('Content-Type: application/json; charset=utf-8');
		echo json_encode($this->response,true);
	}
	
	/**
	 * Checking request object
	 * 
	 * @throws Exception
	 */
	private function checkRequest(){
		if (empty($this->request) || !is_array($this->request)){
			throw new Exception(0);
		}
		if ($this->request['jsonrpc'] != '2.0'){
			throw new Exception(1);
		}
		if (!$this->checkMethod()){
			throw new Exception(2);	
		}
		$this->checkParams();
	}
	
	/**
	 * Checking if request method exists and is allowed to be run
	 * 
	 * @return bool
	 */
	private function checkMethod(){
		if (method_exists(self, $this->request['method']) && !empty($this->request['metod']) && in_array($this->request['metod'],$this->methods)){
			return true;
		}
		return false;
	}
	
	/**
	 * Checking if the parameters are correct (array or object)
	 * If params are incorrect then reset them to empty array
	 */
	private function checkParams(){
		if (empty($this->request['params']) || (!is_array($this->request['params']) && !is_object($this->request['params']))){
			$this->request['params'] = array();
		}
	}
	
	/**
	 * Checking the ID parameter
	 * If there is no ID parameter provided then the request should be treated as Notification (Procedure Call without Response)
	 * If the ID is incorrect generate new id
	 * 
	 * @return bool
	 */
	private function checkId(){
	  	if (empty($this->request['id'])){ // Notification
	  		return false;
	  	} elseif (is_scalar($this->request['id'])){
			$this->response['id'] = $this->request['id'];
		}
   		$this->response['id'] = rand(10000,99999);
   		return true;
	}
}
?>
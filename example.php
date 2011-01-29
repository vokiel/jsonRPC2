<?php
require_once 'jsonRPC2.class.php';

try{
	
	$jsonrpc = new jsonRPC2(trim($_REQUEST['query']));
	$jsonrpc->call();
	$jsonrpc->send();
	
}catch(Exception $e){
	if ($e->getCode()==0){// notification, say nothing
		die();
	}
}
?>
<?php 
require_once("XmlWriterOemr.class.php");
/*
 *  Copyright © 20111 by Ensoftek
 *  
  */
class PQRIXml extends XmlWriterOemr {

	function PQRIXml($indent = '  ') {
		parent::XmlWriterOemr($indent);
	}
	
	function open_submission() {
		
		$this->push('submission', array('type'=>'PQRI-REGISTRY', 'option'=>'payment', 
           'xmlns:xsi'=>'http://www.w3.org/2001/XMLSchema-instance', 'xsi:noNamespaceSchemaLocation'=>'Registry_Payment.xsd'));
	}
	
	function close_submission() {
		$this->pop();
	}
	
	
	function add_file_audit_data() {
		
		$res = sqlQuery("select * from users where username='".$_SESSION{"authUser"}."'");
			
		$this->push('file_audit_data');
		$this->element('create-date', date("m-d-Y")); 
		$this->element('create-time', date("H:i")); 
		$this->element('create-by', $res{"fname"}.' '.$res{"lname"}); 
		$this->element('version', '1.0'); 
		$this->element('file-number', '1'); 
		$this->element('number-of-files', '1'); 
		$this->pop();
	}
	
	function add_registry() {
		
		// Get the registries
	    $row = sqlQuery("SELECT gl_value FROM globals WHERE gl_name = 'PQRI_REGISTRY_NAME' LIMIT 1");
	    $registry_name = $row ? $row['gl_value'] : '';
				
	    $row = sqlQuery("SELECT gl_value FROM globals WHERE gl_name = 'PQRI_REGISTRY_ID' LIMIT 1");
	    $registry_id = $row ? $row['gl_value'] : '';

	    $row = sqlQuery("SELECT gl_value FROM globals WHERE gl_name = 'PQRI_REGISTRY_SUBMISSION_METHOD' LIMIT 1");
	    $submission_method = $row ? $row['gl_value'] : '';
	    
		$this->push('registry');
		$this->element('registry-name', $registry_name); 
		$this->element('registry-id', $registry_id); 
		$this->element('submit-method', $submission_method); 
		$this->pop();
	}

	function add_measure_group_stats($arrStats) {
		$this->push('measure-group-stat');

		foreach ($arrStats as $key => $value)
		{
			$this->element($key, $value);
		}

		$this->pop();
	}

	function add_pqri_measures($arrStats) {
		$this->push('pqri-measure');

		foreach ($arrStats as $key => $value)
		{
			$this->element($key, $value);
		}

		$this->pop();
	}
	
	
	function open_provider($arrStats) {
	    $this->push('provider');
	    
		foreach ($arrStats as $key => $value)
		{
			$this->element($key, $value);
		}
	    
	}

	function close_provider() {
		$this->pop();
	}
	
	function open_measure_group($id) {
	    $this->push('measure-group', array('ID'=>$id));
	}

	function close_measure_group() {
		$this->pop();
	}
	
}
?>
<?
 // Copyright (C) 2011 Ensoftek 
 /////////////////////////////////////////////////////////////////////
 // This program exports report to PQRI 2009 XML format.
 /////////////////////////////////////////////////////////////////////

include_once("../interface/globals.php");
include_once("../library/patient.inc");
require_once "../library/options.inc.php";
require_once("../library/clinical_rules.php");
require_once("../library/classes/PQRIXml.class.php");

// Collect parameters (set defaults if empty)
$target_date = (isset($_GET['target_date'])) ? trim($_GET['target_date']) : date('Y-m-d H:i:s');
$rule_filter = (isset($_GET["rule_filter"])) ? trim($_GET["rule_filter"]) : $rule_filter = "cqm";
$plan_filter = (isset($_GET['plan_filter'])) ? trim($_GET['plan_filter']) : "";
$organize_method = (empty($plan_filter)) ? "default" : "plans";
$provider  = trim($_GET['provider']);
 
$xml = new PQRIXml();

// Add the XML parent tag.
$xml->open_submission();

// Add the file audit data
$xml->add_file_audit_data();

// Add the registry entries
$xml->add_registry();

// Add the measure groups.
$dataSheet = test_rules_clinic($provider,$rule_filter,$target_date,"report",'',$plan_filter,$organize_method);
 
$firstProviderFlag = TRUE;
$firstPlanFlag = TRUE;
$existProvider = FALSE;

foreach ($dataSheet as $row) {
	//print_r($row);
 	$firstLabelFlag = 0;
 	if (isset($row['is_main']) || isset($row['is_sub'])) {
		if (isset($row['is_main'])) {
			// Add PQRI measures
 			$pqri_measures = array();
			$pqri_measures['pqri-measure-number'] =  $row['cqm_pqri_code'];
			$pqri_measures['eligible-instances'] = $row['pass_filter'];
	       	$pqri_measures['meets-performance-instances'] = $row['pass_target'];
		    $pqri_measures['performance-exclusion-instances'] =  $row['excluded'];
		    $performance_not_met_instances = (int)$row['pass_filter'] - (int)$row['pass_target'] - (int)$row['excluded'];
            $pqri_measures['performance-not-met-instances'] = (string)$performance-not-met-instances;
			$pqri_measures['performance-rate'] = $row['percentage'];
	        $pqri_measures['reporting-rate'] = '';
	        $xml->add_pqri_measures($pqri_measures);	
		}
		else { // $row[0] == "sub"
				// NOT RELEVEANT FOR PRQRI REPORT.
		}
 	}
    else if (isset($row['is_provider'])) {
    	if ( $firstProviderFlag == FALSE ){
		     $xml->close_provider();
    	}
      	 // Add the provider
 		$physician_ids = array();
    	if (!empty($row['npi']) || !empty($row['federaltaxid'])) {
	       if (!empty($row['npi'])) {
	          $physician_ids['npi'] = $row['npi'];
	       }
	       if (!empty($row['federaltaxid'])) {
	           $physician_ids['tin'] = $row['federaltaxid'];
	       }
	     }
    	
       	 $xml->open_provider($physician_ids);
	     $firstProviderFlag = FALSE;
	     $existProvider = TRUE;
   }
   else { // isset($row['is_plan'])
   	
   	    if ( $firstPlanFlag == FALSE ) {
   	    	if ( $firstProviderFlag == FALSE ) {
		    	$xml->close_provider();
   	    	}
   	    	$xml->close_measure_group();
    	}
   	
   	   	 //$id =  generate_display_field(array('data_type'=>'1','list_id'=>'clinical_plans'),$row['id']);
	   	 $xml->open_measure_group($row['cqm_measure_group']);
	     $firstPlanFlag = FALSE;
       	 $firstProviderFlag = TRUE; // Reset the provider flag
   }
 	 	
}

if ( $existProvider == TRUE ){
   	$xml->close_provider();
   	$xml->close_measure_group();
}

$xml->close_submission();
 
?>

<html>
<head>
<? html_header_show();?>
<link rel=stylesheet href="<?echo $css_header;?>" type="text/css">
<title><?php xl('Export CDR Registry','e'); ?></title>
</head>
<body>

<p><?php xl('The exported data appears in the text area below.  You can copy and
paste this into an email or to any other desired destination.','e'); ?></p>

<center>
<form>

<textarea rows='50' cols='500' style='width:95%' readonly>
<? echo $xml->getXml(); ?>
</textarea>

<p><input type='button' value=<?php xl('OK','e','\'','\''); ?> onclick='window.close()' /></p>
</form>
</center>

</body>
</html>

<?php
//															     //
// **************************************************************//
//        EcoFlow LiFePo4 12/12/20a Homebattery Charging         //
//                          Config                               //
// **************************************************************//
//                                                               //
	
// = Domoticz dummy devices variables
	$domoticzIP			    = '127.0.0.1:8080'; 	    	     // IP + poort van Domoticz
	$batterySOCIDX 		 	= '64';
	$batteryVoltageIDX 		= '41';
	$batteryAvailIDX        = '68';
	$batteryChargeTimeIDX   = '66';
	$batteryDischargeTimeIDX= '67';
	$inputCounterIDX 	    = '60';
	$outputCounterIDX 	    = '58';
	$pvCounterIDX 	        = '59';
	$ecoFlowTempIDX 		= '50';
	$batteryRTEIDX 		    = '145';
	//$batteryCounterIDX 		= '150';
	
// = URLs
	$baseUrl = 'http://'.$domoticzIP.'/json.htm?type=command&param=getdevices&rid=';
	$urls = [	
		'batteryVoltageIDX'       => $baseUrl . $batteryVoltageIDX,	
		'ecoFlowTempIDX'          => $baseUrl . $ecoFlowTempIDX,
		'batterySOCIDX'           => $baseUrl . $batterySOCIDX,
		'batteryAvailIDX'         => $baseUrl . $batteryAvailIDX,
	    'batteryChargeTimeIDX'    => $baseUrl . $batteryChargeTimeIDX,
		'batteryDischargeTimeIDX' => $baseUrl . $batteryDischargeTimeIDX,
		'pvCounter'               => $baseUrl . $pvCounterIDX,
		'outputCounterIDX'        => $baseUrl . $outputCounterIDX,
		'inputCounterIDX'         => $baseUrl . $inputCounterIDX,
		'batteryRTEIDX'           => $baseUrl . $batteryRTEIDX
		//'batteryCounterIDX'       => $baseUrl . $batteryCounterIDX
	];  
	
//															     //
// **************************************************************//
//                      EcoFlow 2 Domoticz                       //
//                      Functions Set Data                       //
// **************************************************************//
//                                                               //

// = Function Update Domoticz Device
	function UpdateDomoticzDevice($idx,$cmd) {
	  global $domoticzIP;
	  global $batterySOCIDX;
	  global $batteryVoltageIDX;
	  global $batteryAvailIDX;
	  global $batteryChargeTimeIDX;
	  global $batteryDischargeTimeIDX;
	  global $inputCounterIDX;
	  global $outputCounterIDX;
	  global $pvCounterIDX;
	  global $ecoFlowTempIDX;
	  global $batteryRTEIDX;
	  
	  if ($idx == $inputCounterIDX || $idx == $outputCounterIDX || $idx == $batterySOCIDX || $idx == $batteryVoltageIDX || $idx == $pvCounterIDX || $idx == $ecoFlowTempIDX || $idx == $batteryRTEIDX){
	  $reply=json_decode(file_get_contents('http://'.$domoticzIP.'/json.htm?type=command&param=udevice&idx='.$idx.'&nvalue=0&svalue='.$cmd.';0'),true);
	  }
	  
	  if ($idx == $batteryChargeTimeIDX || $idx == $batteryDischargeTimeIDX || $idx == $batteryAvailIDX){
	  $reply=json_decode(file_get_contents('http://'.$domoticzIP.'/json.htm?type=command&param=udevice&idx='.$idx.'&nvalue=0&svalue='.$cmd.''),true);
	  }
	  
	  if($reply['status']=='OK') $reply='OK';else $reply='ERROR';
	  return $reply;
	}

//															     //
// **************************************************************//
//                      EcoFlow 2 Domoticz                       //
//                          PushUpdate                           //
// **************************************************************//
//                                                               //
if (!$isManualRun){
	UpdateDomoticzDevice($batterySOCIDX, ''.$batteryPct.'');
	sleep(0.1);
	UpdateDomoticzDevice($batteryAvailIDX, ''.$batteryAvailable.'');
	sleep(0.1);
	UpdateDomoticzDevice($batteryVoltageIDX, ''.$pvAvInputVoltage.'');
	sleep(0.1);		
	UpdateDomoticzDevice($inputCounterIDX, ''.$hwChargerUsage.'');
	sleep(0.1);		
	UpdateDomoticzDevice($outputCounterIDX, ''.$hwInvReturn.'');
	sleep(0.1);
	UpdateDomoticzDevice($pvCounterIDX, ''.$hwSolarReturn.'');
	sleep(0.1);
	UpdateDomoticzDevice($ecoFlowTempIDX, ''.$invTemp.'');
	sleep(0.1);
	if ($hwChargerUsage > 100 && $batteryPct < 100) {
	UpdateDomoticzDevice($batteryChargeTimeIDX, ''.$realChargeTime.'');
	} else {
	UpdateDomoticzDevice($batteryChargeTimeIDX, '00:00');	
	}
	if ($hwInvReturn != 0 && $batteryPct > 0) {
	UpdateDomoticzDevice($batteryDischargeTimeIDX, ''.$realDischargeTime.'');
	} else {
	UpdateDomoticzDevice($batteryDischargeTimeIDX, '00:00');
	}
	sleep(0.1);
	UpdateDomoticzDevice($batteryRTEIDX, ''.$chargerLoss.'');
}
?>
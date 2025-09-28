<?php
//															     //
// **************************************************************//
//           		 PiBattery Solar Storage                     //
//                           Variables                           //
// **************************************************************//
//                                                               //

// = Get Ecoflow data
	$ecoflow 				= new EcoFlowAPI(''.$ecoflowAccessKey.'', ''.$ecoflowSecretKey.'');
	$invOne 				= $ecoflow->getDevice($ecoflowOneSerialNumber);
	$invTwo 				= $ecoflow->getDevice($ecoflowTwoSerialNumber);
	
// = php.ini
	date_default_timezone_set(''.$timezone.'');
	
// = Time/Date now
	$currentTimestamp 		= time();
	$currentTime 			= date('H:i');
	$dateNow 				= date('Y-m-d H:i:s');
	$dateTime 				= new DateTime(''.$dateNow.'', new DateTimeZone(''.$timezone.''));
	$isWinter 				= ($dateTime->format('n') < 3 || $dateTime->format('n') >= 9);
	
// = Check DST time
	$isDST = $dateTime->format("I");
	if ($isDST == '1'){
	$gmt = '1';
	} else {
	$gmt = '0';
	}

// = Get Sunrise/Sunset
	$sunrise 				= (date_sunrise(time(),SUNFUNCS_RET_STRING,$latitude,$longitude,$zenitLat,$gmt));
	$sunset 				= (date_sunset(time(),SUNFUNCS_RET_STRING,$latitude,$longitude,$zenitLong,$gmt));

// = Adjust Sunrise/Sunset with +/- 1 hour
	$sunriseTime 			= DateTime::createFromFormat('H:i', $sunrise);
	$sunsetTime 			= DateTime::createFromFormat('H:i', $sunset);

	$sunriseTime->modify('+1 hour');
	$sunsetTime->modify('-1 hour');

	$sunriseAdjusted 		= $sunriseTime->format('H:i');
	$sunsetAdjusted 		= $sunsetTime->format('H:i');

	$isDaytime 				= ($currentTime >= $sunriseAdjusted && $currentTime <= $sunsetAdjusted);
	$isNightTime			= ($currentTime >= '00:00' && $currentTime < $sunriseAdjusted);

// = Get current variable files
	$varsFile               = $piBatteryPath . 'data/variables.json';
	$vars                   = file_exists($varsFile) ? json_decode(file_get_contents($varsFile), true) : [];
	
	$varsTimerFile          = $piBatteryPath . 'data/timeStamp.json';
	$varsTimer              = file_exists($varsTimerFile ) ? json_decode(file_get_contents($varsTimerFile ), true) : [];

	$varsSyncFile           = $piBatteryPath . 'data/varsSync.json';
	$varsSync               = file_exists($varsSyncFile) ? json_decode(file_get_contents($varsSyncFile), true) : [];
	
// = HomeWizard GET Variables
	$hwP1Usage              = getHwData($hwP1IP);
	$hwP1Fase               = getHwP1FaseData($hwP1IP, $fase);
	$hwSolarReturn          = getHwData($hwKwhIP);
	$hwInvOneReturn         = getHwData($hwEcoFlowOneIP);
	$hwInvTwoReturn         = getHwData($hwEcoFlowTwoIP);
	$hwInvReturn            = ($hwInvOneReturn + $hwInvTwoReturn);

	$hwChargerOneUsage      = getHwData($hwChargerOneIP);
	$hwChargerTwoUsage      = getHwData($hwChargerTwoIP);
	$hwChargerThreeUsage    = getHwData($hwChargerThreeIP);
	$hwChargerFourUsage    	= getHwData($hwChargerFourIP);
	$hwChargerUsage         = ($hwChargerOneUsage + $hwChargerTwoUsage + $hwChargerThreeUsage + $hwChargerFourUsage);

	$hwChargerOneStatus     = getHwStatus($hwChargerOneIP);
	$hwChargerTwoStatus     = getHwStatus($hwChargerTwoIP);
	$hwChargerThreeStatus   = getHwStatus($hwChargerThreeIP);
	$hwChargerFourStatus    = getHwStatus($hwChargerFourIP);
	
	$hwInvOneStatus         = getHwStatus($hwEcoFlowOneIP);
	$hwInvTwoStatus         = getHwStatus($hwEcoFlowTwoIP);

	$hwInvFanStatus         = getHwStatus($hwEcoFlowFanIP);
	
// = Get battery Voltage via inverter
	$pv1OneInputVolt 		= ($invOne['data']['20_1.pv1InputVolt']) / 10;
	$pv2OneInputVolt 		= ($invOne['data']['20_1.pv2InputVolt']) / 10;
	$pvAvOneInputVoltage    = round(($pv1OneInputVolt + $pv2OneInputVolt) / 2, 2);

	$pv1TwoInputVolt 		= ($invTwo['data']['20_1.pv1InputVolt']) / 10;
	$pv2TwoInputVolt 		= ($invTwo['data']['20_1.pv2InputVolt']) / 10;
	$pvAvTwoInputVoltage    = round(($pv1TwoInputVolt + $pv2TwoInputVolt) / 2, 2);
	$pvAvInputVoltage       = round(($pvAvOneInputVoltage + $pvAvTwoInputVoltage) / 2, 2);

// = Get Inverter Temperature
	$invOneTemp             = ($invOne['data']['20_1.llcTemp']) / 10;
	$invTwoTemp             = ($invTwo['data']['20_1.llcTemp']) / 10;
	$invTemp                = ($invOneTemp + $invTwoTemp) / 2;
	
// = Get P1 / Solar and real power usage
	$productionTotal        = ($hwSolarReturn + $hwInvReturn);	
	$realUsage              = ($hwP1Usage - $productionTotal);
	$P1ChargerUsage         = ($hwP1Usage - $hwChargerUsage);

// = Get Inverter and charger real output
	$hwInvOneTotal          = getHwTotalOutputData($hwEcoFlowOneIP);
	$hwInvTwoTotal          = getHwTotalOutputData($hwEcoFlowTwoIP);
	$hwInvTotal             = ($hwInvOneTotal + $hwInvTwoTotal);
	$hwChargerOneTotal      = getHwTotalInputData($hwChargerOneIP);
	$hwChargerTwoTotal      = getHwTotalInputData($hwChargerTwoIP);
	$hwChargerThreeTotal    = getHwTotalInputData($hwChargerThreeIP);
	$hwChargerFourTotal     = getHwTotalInputData($hwChargerFourIP);
	$hwChargersTotalInput   = ($hwChargerOneTotal + $hwChargerTwoTotal + $hwChargerThreeTotal + $hwChargerFourTotal);

// = Get Current Baseload
	$currentOneBaseload	    = ($invOne['data']['20_1.permanentWatts']) / 10;
	$currentTwoBaseload	    = ($invTwo['data']['20_1.permanentWatts']) / 10;
	$currentBaseload	    = ($currentOneBaseload + $currentTwoBaseload);
	$oldBaseload 			= $vars['oldBaseload'] ?? 0;
	
// = Various
	$chargerLoss 			= round($vars['charger_loss_dynamic'] ?? 0.21579680628027725, 7);	
	$pauseCharging 			= $vars['pauseCharging'] ?? false;
	$forceChargeMode 		= $vars['forceChargeMode'] ?? false;
	$chargeLossCalculation 	= $vars['charge_loss_calculation'] ?? false;
	$bmsProtect 			= $vars['keepBMSalive'] ?? false;
	$pendingCharging		= $vars['charger_pending_switch'] ?? false;
	$pendingBaseload		= $vars['baseload_pending_switch'] ?? false;
	$invInjection 			= $vars['invInjection'] ?? false;
	$idleInjectionWatts		= -abs($idleInjectionWatts + 10);
	
// = Get/Set Battery Charge/Discharge/SOC values
	$batteryCapacitykWh     = ($batteryVolt * $batteryAh / 1000);
	$batteryCapacityWh 		= ($batteryCapacitykWh * 1000);
	
	$chargeStart	 		= round($vars['charge_session']['chargeStart'], 3);
	$chargeCalibrated		= round($vars['charge_session']['chargeCalibrated'], 3);
	$chargeEnd	 			= round($hwChargersTotalInput, 3);
	
	$dischargeStart	 		= round($vars['charge_session']['dischargeStart'], 3);
	$dischargeEnd	 		= round($hwInvTotal, 3);

	$brutoCharged			= round(($chargeEnd - $chargeStart), 3);
	$nettoCharged			= round(($chargeEnd - $chargeCalibrated), 3);
	$brutoDischarged 		= round(($dischargeEnd - $dischargeStart), 3);
	$batteryAvailable	    = round((($batteryCapacitykWh) - ($brutoDischarged - ($brutoCharged  * (1 - $chargerLoss)))), 2);
	$batteryPct 			= round(($batteryAvailable / $batteryCapacitykWh) * 100, 2);
	
// = Get status for all chargers
	foreach ($chargers as $name => &$data) {
		$data['status'] = getHwStatus($data['ip']);
	}
	unset($data);
?>
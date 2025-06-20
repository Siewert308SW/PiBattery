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
	$isWinter 				= ($dateTime->format('n') < 4 || $dateTime->format('n') > 9);
	
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

// = Huidige variables ophalen
	$varsFile               = $piBatteryPath . 'data/variables.json';
	$vars                   = file_exists($varsFile) ? json_decode(file_get_contents($varsFile), true) : [];

	$varsTestFile           = $piBatteryPath . 'data/variablesTest.json';
	$varsTest               = file_exists($varsTestFile) ? json_decode(file_get_contents($varsTestFile), true) : [];
	
	$varsTimerFile          = $piBatteryPath . 'data/timeStamp.json';
	$varsTimer              = file_exists($varsTimerFile ) ? json_decode(file_get_contents($varsTimerFile ), true) : [];

// = Get Ecoflow status
    $pv1acOffFlag 			= ($invOne['data']['20_1.acOffFlag']);
    $pv2acOffFlag 			= ($invTwo['data']['20_1.acOffFlag']);

	if ($runCharger){
		if (!isset($pv1acOffFlag) || !isset($pv2acOffFlag)) {
			if (!isset($vars['apiOnline']) || $vars['apiOnline'] === true) {
			$vars['apiOnline'] = false;
			switchHwSocket('two','Off'); sleep(1);
			switchHwSocket('three','Off'); sleep(1);
			switchHwSocket('one','Off');
			switchHwSocket('invOne','Off');
			switchHwSocket('invTwo','Off');
			switchHwSocket('fan','Off');
			writeJsonLocked($varsFile, $vars);
			}
		} elseif (isset($pv1acOffFlag) && isset($pv2acOffFlag) && $vars['apiOnline'] === false) {
			if (!isset($vars['apiOnline']) || $vars['apiOnline'] === false) {
			$vars['apiOnline'] = true;
			switchHwSocket('invOne','On');
			switchHwSocket('invTwo','On');
			writeJsonLocked($varsFile, $vars);
			}
		}
	}

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
	$hwChargerUsage         = ($hwChargerOneUsage + $hwChargerTwoUsage + $hwChargerThreeUsage);

	$hwChargerOneStatus     = getHwStatus($hwChargerOneIP);
	$hwChargerTwoStatus     = getHwStatus($hwChargerTwoIP);
	$hwChargerThreeStatus   = getHwStatus($hwChargerThreeIP);
	
	$hwInvOneStatus         = getHwStatus($hwEcoFlowOneIP);
	$hwInvTwoStatus         = getHwStatus($hwEcoFlowTwoIP);

	$hwInvFanStatus         = getHwStatus($hwEcoFlowFanIP);
	
// = Get battery Voltage via inverter
if ($vars['apiOnline'] === true) {
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
}
	
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
	$hwChargersTotalInput   = ($hwChargerOneTotal + $hwChargerTwoTotal + $hwChargerThreeTotal);

// = Get Current Baseload
if ($vars['apiOnline'] === true) {	
	$currentOneBaseload	    = ($invOne['data']['20_1.permanentWatts']) / 10;
	$currentTwoBaseload	    = ($invTwo['data']['20_1.permanentWatts']) / 10;
	$currentBaseload	    = ($currentOneBaseload + $currentTwoBaseload);
	$oldBaseload 			= $vars['oldBaseload'] ?? 0;
}
	
// = Various
	$batteryMinimum 		= $isWinter ? 25 : $batteryMinimum;
	$chargerLoss 			= round($vars['charger_loss_dynamic'] ?? 0.2248651485295945, 5);	
	$pauseCharging 			= $vars['pauseCharging'] ?? false;
	$solarHighestProd	    = $vars['solarHighestProd'] ?? -1000;
	$chargeLossCalculation 	= $vars['charge_loss_calculation'] ?? false;
	$bmsProtect 			= $vars['keepBMSalive'] ?? false;
	
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
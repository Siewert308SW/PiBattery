<?php
//															     //
// **************************************************************//
//           		 PiBattery Solar Storage                     //
//                                                               //
// **************************************************************//
//																 //

// = -------------------------------------------------
// = Function writeJson
// = -------------------------------------------------
	function writeJson(string $filename, array $data): void {
		$fp = @fopen($filename, 'c+');
		if (!$fp) return;

		if (flock($fp, LOCK_EX)) {
			ftruncate($fp, 0);
			rewind($fp);
			fwrite($fp, json_encode($data, JSON_PRETTY_PRINT));
			fflush($fp);
			flock($fp, LOCK_UN);
		}
		fclose($fp);
	}

// = -------------------------------------------------
// = Variables
// = -------------------------------------------------
	
// = Time & File Variables
	$timeStamp 				= time();
	$piBatteryPath 			= __DIR__ . '/';
	$varsTimerFile 			= $piBatteryPath . 'data/timeStamp.json';
	$bootStrapFile			= $piBatteryPath . 'bootstrap/bootstrap.php';
	
	$scriptTimer 			= [];
	$scriptTimer 			= file_exists($varsTimerFile) ? json_decode(file_get_contents($varsTimerFile), true) : [];

	$runCharger 			= false;
	$runBaseload 			= false;
	$runDomoticz 			= false;
	
// = Determine is script called by terminal
	$isCliInteractive 		= function_exists('posix_isatty') && posix_isatty(STDOUT);
	$isManualRun 			= php_sapi_name() === 'cli' && $isCliInteractive;
	$isCronRun 				= php_sapi_name() === 'cli' && !$isCliInteractive;

// = -------------------------------------------------
// = Determine if script may be executed
// = -------------------------------------------------

// = Determine if Charger script may execute
	if (!isset($scriptTimer['lastChargerRun']) || ($timeStamp - $scriptTimer['lastChargerRun']) >= 60) {
		$runCharger = true;
	}

// = Determine if Baseload script may be executed	
	if (!isset($scriptTimer['lastBaseloadRun']) || ($timeStamp - $scriptTimer['lastBaseloadRun']) >= 30) {
		$runBaseload = true;
	}

// = Determine if Domoticz script may be executed	
	if (!isset($scriptTimer['lastDomoticzRun']) || ($timeStamp - $scriptTimer['lastDomoticzRun']) >= 15) {
		$runDomoticz = true;
	}
	
	require_once $bootStrapFile;
	
// = Charger script may execute
	if ($runCharger == true && $hwInvReturn == 0 && $vars['apiOnline'] === true) {
		$scriptTimer['lastChargerRun'] = $timeStamp;
		writeJson($varsTimerFile, $scriptTimer);
		require_once $piBatteryPath . 'scripts/charge.php';
	}

// = Baseload script may be executed	
	if (($runBaseload == true || $isManualRun) && ($vars['apiOnline'] === true)) {
		$scriptTimer['lastBaseloadRun'] = $timeStamp;
		writeJson($varsTimerFile, $scriptTimer);
		sleep(1);
		require_once $piBatteryPath . 'scripts/baseload.php';
	}

// = Domoticz script may be executed	
	if ($runDomoticz == true && $vars['apiOnline'] === true) {
		$scriptTimer['lastDomoticzRun'] = $timeStamp;
		writeJson($varsTimerFile, $scriptTimer);
		sleep(2);
		require_once $piBatteryPath . 'scripts/domoticz.php';
	}
	
// = -------------------------------------------------
// = Debug Output
// = -------------------------------------------------	
	if ($debug == 'yes' && $isManualRun){
		echo ' '.PHP_EOL;
		echo '  ---------------------------------------------------'.PHP_EOL;
	    echo '  --                   PiBattery                   --'.PHP_EOL;
		echo '  --            '.$batteryCapacitykWh.' kWh Solar Storage             --'.PHP_EOL;
		echo '  ---------------------------------------------------'.PHP_EOL;
		echo ' '.PHP_EOL;

		if ($vars['apiOnline'] === true) {	
			$files = glob(__DIR__ . '/lang/*.php');
			foreach ($files as $file) {
				if ($file != __FILE__) {
					require_once($file);
				}
			}
		} else {
			echo '  ERROR: Inverters or API are not online!'.PHP_EOL;	
		}
		
		echo ' '.PHP_EOL;
		echo '  ---------------------------------------------------'.PHP_EOL;
		echo '  --                     The End                   --'.PHP_EOL;
		echo '  ---------------------------------------------------'.PHP_EOL;
		echo ' '.PHP_EOL;	
	}
?>
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
	$currentTime 			= date('H:i');
	$dateNow 				= date('Y-m-d H:i:s');
	$piBatteryPath 			= __DIR__ . '/';
	$varsTimerFile 			= $piBatteryPath . 'data/timeStamp.json';
	$bootStrapFile			= $piBatteryPath . 'bootstrap/bootstrap.php';
	
	$scriptTimer 			= [];
	$scriptTimer 			= file_exists($varsTimerFile) ? json_decode(file_get_contents($varsTimerFile), true) : [];

	$runCharger 			= false;
	$runBaseload 			= false;
	$runDomoticz 			= false;
	$varsChanged 			= false;
	$varsSyncChanged 	    = false;
	
// = Determine is script called by terminal
	$isCliInteractive 		= function_exists('posix_isatty') && posix_isatty(STDOUT);
	$isManualRun 			= php_sapi_name() === 'cli' && $isCliInteractive;
	$isCronRun 				= php_sapi_name() === 'cli' && !$isCliInteractive;
	
// = -------------------------------------------------
// = Determine if script may be executed
// = -------------------------------------------------
	
// = Determine if Charger script may execute
	if (!isset($scriptTimer['lastChargerRun']) || ($timeStamp - $scriptTimer['lastChargerRun']) >= 60 || $isManualRun) {
		$runCharger = true;
	}

// = Determine if Baseload script may be executed
	if (!isset($scriptTimer['lastBaseloadRun']) || ($timeStamp - $scriptTimer['lastBaseloadRun']) >= 30 || $isManualRun) {
		$runBaseload = true;
	}	
	
// = Determine if Domoticz script may be executed	
	if (!isset($scriptTimer['lastDomoticzRun']) || ($timeStamp - $scriptTimer['lastDomoticzRun']) >= 15 && !$isManualRun) {
		$runDomoticz = true;
	}

// = -------------------------------------------------
// = Script may be executed
// = -------------------------------------------------

	require_once $bootStrapFile;
	
// = Charger script may execute
	if ($runCharger == true) {
		$scriptTimer['lastChargerRun'] = $timeStamp;
		writeJson($varsTimerFile, $scriptTimer);
		require_once $piBatteryPath . 'scripts/charge.php';
	}

// = Baseload script may be executed	
	if ($runBaseload == true) {
		$scriptTimer['lastBaseloadRun'] = $timeStamp;
		writeJson($varsTimerFile, $scriptTimer);
		sleep(1);
		require_once $piBatteryPath . 'scripts/baseload.php';
	}

// = Domoticz script may be executed	
	if ($runDomoticz == true) {
		$scriptTimer['lastDomoticzRun'] = $timeStamp;
		writeJson($varsTimerFile, $scriptTimer);
		sleep(2);
		require_once $piBatteryPath . 'scripts/domoticz.php';
	}

// = Global writeJson
	if ($varsChanged && !$isManualRun) {
		writeJsonLocked($varsFile, $vars);
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

		$files = glob(__DIR__ . '/lang/*.php');
		foreach ($files as $file) {
			if ($file != __FILE__) {
				require_once($file);
			}
		}
		
		echo ' '.PHP_EOL;
		echo '  ---------------------------------------------------'.PHP_EOL;
		echo '  --                     The End                   --'.PHP_EOL;
		echo '  ---------------------------------------------------'.PHP_EOL;
		echo ' '.PHP_EOL;	
	}
?>
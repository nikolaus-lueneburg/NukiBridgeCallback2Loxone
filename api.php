<?php
# Load config file
$configFile = file_get_contents("config.json");

# Converting JSON to associative arrays
$config = json_decode($configFile, true); // if you put json_decode($jsonStr, true), it will convert the json string to associative array

# Takes raw data from the request
$json = file_get_contents('php://input');

# Uncomment and customize for testing
$json = '{"deviceType": 0, "nukiId": 374773519, "mode": 2, "state": 1, "stateName": "locked", "batteryCritical": true}';

# Converting JSON to associative arrays
$data = json_decode($json, true);

# Uncomment to check json file structure
# echo json_last_error();
# echo json_last_error_msg();


# Variables - General
$DateTime = date("Y-m-d_His");

# Variables - Config
$LoxIP = $config['Loxone']['IP'];
$LoxUser = $config['Loxone']['User'];
$LoxPassword = $config['Loxone']['Password'];

$LoggingEnabled = $config['Logging']['Enabled'];
$LoggingFilename = $config['Logging']['Filename'];

# Variables - Nuki
$NukiID = $data['nukiId'];
$NukiBatteryCritical = $data['batteryCritical'];
$NukiState = $data['state'];
$NukiStateName = $data['stateName'];

# Logging Function
$writeLog = function($Text) use ($LoggingEnabled,$LoggingFilename) {
	if ($LoggingEnabled) {
		file_put_contents($LoggingFilename, $Text, FILE_APPEND);
	}
};

foreach ($config['Nuki'] as $Lock) {
	if ($Lock['ID'] == $NukiID) {
		$LockName = $Lock['Name'];
		$VIState = $Lock['VIState'];
		$VIBattery = $Lock['VIBattery'];
		
		# Log lock state
        $writeLog("$DateTime - $LockName - $NukiID - $NukiStateName");
		
		# Send lock state to defined virutal input 
		fopen("http://$LoxUser:$LoxPassword@$LoxIP/dev/sps/io/$VIState/$NukiState", "r");
		
		# Sends a pulse to virutal input and logs it if low battery problem exist
		if ($NukiBatteryCritical === true) {
			fopen("http://$LoxUser:$LoxPassword@$LoxIP/dev/sps/io/$VIBattery/pulse", "r");
			# Log battery problem
			$writeLog(" - BatteryEmpty");
		}
		# Finish logging
		$writeLog("\r\n");
	}
}
?>
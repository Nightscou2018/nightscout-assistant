<?php
header("Content-Type: application/json; charset=UTF-8");

const PATIENT_NAME = "Jake";
const GLUCOSE_TEXT = "glucose";

function buildTimeAgoText($mins) {
	$returnText = "";
	
	switch($mins) {
		case 0:
			$returnText = "As of right now, ".PATIENT_NAME."'s ".GLUCOSE_TEXT." is ";		
			break;
		case 1:
			$returnText = "As of one minute ago, ".PATIENT_NAME."'s ".GLUCOSE_TEXT." was ";
			break;
		default:
			//more than one minute, check if we have hours or not
			if ($mins >= 60){
				//at least 1 hour exists
				$hours = floor($mins / 60);
				$remainingMins = $mins % 60;
				
				//determine if we have only 1 hour or multiple hours for 
				//grammatically correct response
				if($hours == 1) {
					$returnText = "As of one hour ";
				} else {
					$returnText = "As of ".$hours."hours ";
				}
				
				if ($remainingMins != 0) {	
					if ($remainingMins == 1) {
						$returnText.="one minute ";
					} else {
						$returnText.=$remainingMins." minutes ";
					}
				}
				$returnText.="ago, ".PATIENT_NAME."'s ".GLUCOSE_TEXT." was ";
			} else {
				//no hours
				$returnText = "As of ".$mins." minutes ago, ".PATIENT_NAME."'s ".GLUCOSE_TEXT." was ";
				$returnText = "As of ".$mins." minutes ago, ".PATIENT_NAME."'s ".GLUCOSE_TEXT." was ";
			}
			break;
	}
	return $returnText;
}

function buildBGDifferenceText($currentBG,$previousBG) {
	//get the difference between the two in a positive number
	$bgDifference = abs($currentBG - $previousBG);

	//determine if BG is going up or down
	$directionText = "";

	if ($currentBG > $previousBG) {
		$directionText = " an increase ";
	} elseif ($currentBG < $previousBG) {
		$directionText = " a decrease ";	
	} 

	//checking the difference to build proper grammar
	$bgDifferenceText = "";
	switch($bgDifference) {
		case 0:
			$bgDifferenceText = "This represents no change from the previous reading. ";
			break;
		case 1:
			$bgDifferenceText = "This was".$directionText."of 1 point. ";
			break;
		default:
			$bgDifferenceText = "This was".$directionText."of ".$bgDifference." points. ";
			break;
	}
	return $bgDifferenceText;
}

function buildDirectionalText ($trend) {
	$returnText = "";
	
	switch ($trend) {
		case 1:
			$returnText = " with two arrows up";
			break;
		case 2:
			$returnText = " with one arrow up";
			break;
		case 3:
			$returnText = " slanting up";
			break;
		case 4:
			$returnText = " steady";
			break;
		case 5:
			$returnText = " slanting down";
			break;
		case 6:
			$returnText = " with one arrow down";
			break;
		case 7:
			$returnText = " with two arrows down";
			break;
	}
	return $returnText;	
}

//retrieve the JSON entries. File contents contains the previous 10 entries
$url = "https://nightscout-miner.herokuapp.com/api/v1/entries.json";
$data_raw = file_get_contents($url);
$data = json_decode($data_raw);


//get the current time for comparison later
$currentTime = time();

//get the UNIX timestamp of the last known entry
$lastReadTime = $data[0]->date/1000;

//calculate number of minutes since last reading
$lastReadMinutesAgo = round(($currentTime - $lastReadTime)/60,0);

//get the last 2 BG values
$currentBG = $data[0]->sgv;
$previousBG = $data[1]->sgv;

$speechText = buildTimeAgoText($lastReadMinutesAgo).$currentBG.buildDirectionalText($data[0]->trend).". ".buildBGDifferenceText($currentBG,$previousBG);
$jsonArray = array(speech=>$speechText,displayText=>$speechText);

echo json_encode($jsonArray);
?>
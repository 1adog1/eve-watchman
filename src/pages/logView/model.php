<?php

function getLogArray() {

	$logArray = [];
	
	$toPull = $GLOBALS['MainDatabase']->prepare("SELECT * FROM logs");
	$toPull->execute();
	
	$pulledArray = $toPull->fetchAll();
	
	foreach ($pulledArray as $throwaway => $arrayData) {
		$logArray[] = $arrayData;
	}
	
	uasort($logArray, function ($first, $second) {
		return $second["timestamp"] <=> $first["timestamp"];
	});
	
	return $logArray;
	
}

function displayLogs($logArray, $maxTableRows) {

	$maxCounter = 0;

	foreach($logArray as $throwaway => $logDetails) {
		
		if ($maxCounter < $maxTableRows) {
		
			if (checkFilter($logDetails) === true) {
				
				$maxCounter += 1;
				
				echo "
				<tr>
					<td align='center'>
						" . date("F jS, Y <br> H:i:s e", $logDetails["timestamp"]) . "
					</td>
					<td align='center'>
						" . $logDetails["type"] . "
					</td>
					<td align='center'>
						" . $logDetails["page"] . "
					</td>
					<td align='center'>
						" . $logDetails["actor"] . "
					</td>
					<td align='center'>
						" . $logDetails["details"] . "
					</td>
					<td align='center'>
						" . $logDetails["trueip"] . "
					</td>
					<td align='center'>
						" . $logDetails["forwardip"] . "
					</td>																	
				</tr>
				";
			
			}
		}
		else {
			
			break;
			
		}
	}
}

?>
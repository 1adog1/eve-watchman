<?php

function checkFilter($logDetails) {
	
	$toShow = true;
	if ($_SERVER["REQUEST_METHOD"] == "POST") {
		
		$toShow = false;			
		$dateFilter = false;
		
		if (isset($_POST["StartDate"]) and isset($_POST["EndDate"]) and $_POST["StartDate"] != "" and $_POST["EndDate"] != "") {
			if (count(explode("-", htmlspecialchars($_POST["StartDate"]))) == 3 and count(explode("-", htmlspecialchars($_POST["EndDate"]))) == 3) {
				
				$dateFilter = true;
										
				$startDate = strtotime(htmlspecialchars($_POST["StartDate"]));
				$endDate = strtotime(htmlspecialchars($_POST["EndDate"])) + 86400;
				
			}
		}
		
		if (isset($_POST["access_g"]) and $_POST["access_g"] == "true") {
			if ($logDetails["type"] == "Page Access Granted"){

				if (($dateFilter === true and $logDetails["timestamp"] <= $endDate and $logDetails["timestamp"] >= $startDate) or $dateFilter === false) {
					
					if ((isset($_POST["CharacterName"]) and htmlspecialchars($_POST["CharacterName"]) != "" and strpos($logDetails["actor"], htmlspecialchars($_POST["CharacterName"])) !== false) or (!isset($_POST["CharacterName"]) or htmlspecialchars($_POST["CharacterName"]) == "")) {
						
						$toShow = true;
						
					}
				}
				
			}
		}
		
		if (isset($_POST["access_d"]) and $_POST["access_d"] == "true") {
			if ($logDetails["type"] == "Page Access Denied"){

				if (($dateFilter === true and $logDetails["timestamp"] <= $endDate and $logDetails["timestamp"] >= $startDate) or $dateFilter === false) {
					
					if ((isset($_POST["CharacterName"]) and htmlspecialchars($_POST["CharacterName"]) != "" and strpos($logDetails["actor"], htmlspecialchars($_POST["CharacterName"])) !== false) or (!isset($_POST["CharacterName"]) or htmlspecialchars($_POST["CharacterName"]) == "")) {
						
						$toShow = true;
						
					}
				}

			}
		}
		
		if (isset($_POST["login"]) and $_POST["login"] == "true") {
			if ($logDetails["type"] == "User Login"){

				if (($dateFilter === true and $logDetails["timestamp"] <= $endDate and $logDetails["timestamp"] >= $startDate) or $dateFilter === false) {
					
					if ((isset($_POST["CharacterName"]) and htmlspecialchars($_POST["CharacterName"]) != "" and strpos($logDetails["actor"], htmlspecialchars($_POST["CharacterName"])) !== false) or (!isset($_POST["CharacterName"]) or htmlspecialchars($_POST["CharacterName"]) == "")) {
						
						$toShow = true;
						
					}
				}

			}
		}

		if (isset($_POST["search"]) and $_POST["search"] == "true") {
			if ($logDetails["type"] == "User Search"){

				if (($dateFilter === true and $logDetails["timestamp"] <= $endDate and $logDetails["timestamp"] >= $startDate) or $dateFilter === false) {
					
					if ((isset($_POST["CharacterName"]) and htmlspecialchars($_POST["CharacterName"]) != "" and strpos($logDetails["actor"], htmlspecialchars($_POST["CharacterName"])) !== false) or (!isset($_POST["CharacterName"]) or htmlspecialchars($_POST["CharacterName"]) == "")) {
						
						$toShow = true;
						
					}
				}

			}
		}
		
		if (isset($_POST["s_database"]) and $_POST["s_database"] == "true") {
			if ($logDetails["type"] == "Automated Database Edit" or $logDetails["type"] == "Automated Database Creation" or $logDetails["type"] == "Outside Database Posted"){

				if (($dateFilter === true and $logDetails["timestamp"] <= $endDate and $logDetails["timestamp"] >= $startDate) or $dateFilter === false) {
					
					if ((isset($_POST["CharacterName"]) and htmlspecialchars($_POST["CharacterName"]) != "" and strpos($logDetails["actor"], htmlspecialchars($_POST["CharacterName"])) !== false) or (!isset($_POST["CharacterName"]) or htmlspecialchars($_POST["CharacterName"]) == "")) {
						
						$toShow = true;
						
					}
				}

			}
		}

		if (isset($_POST["u_database"]) and $_POST["u_database"] == "true") {
			if ($logDetails["type"] == "User Database Edit"){

				if (($dateFilter === true and $logDetails["timestamp"] <= $endDate and $logDetails["timestamp"] >= $startDate) or $dateFilter === false) {
					
					if ((isset($_POST["CharacterName"]) and htmlspecialchars($_POST["CharacterName"]) != "" and strpos($logDetails["actor"], htmlspecialchars($_POST["CharacterName"])) !== false) or (!isset($_POST["CharacterName"]) or htmlspecialchars($_POST["CharacterName"]) == "")) {
						
						$toShow = true;
						
					}
				}

			}
		}
		
		if (isset($_POST["c_errors"]) and $_POST["c_errors"] == "true") {
			if ($logDetails["type"] == "Critical Error"){

				if (($dateFilter === true and $logDetails["timestamp"] <= $endDate and $logDetails["timestamp"] >= $startDate) or $dateFilter === false) {
					
					if ((isset($_POST["CharacterName"]) and htmlspecialchars($_POST["CharacterName"]) != "" and strpos($logDetails["actor"], htmlspecialchars($_POST["CharacterName"])) !== false) or (!isset($_POST["CharacterName"]) or htmlspecialchars($_POST["CharacterName"]) == "")) {
						
						$toShow = true;
						
					}
				}

			}
		}

		if (isset($_POST["p_errors"]) and $_POST["p_errors"] == "true") {
			if ($logDetails["type"] == "Page Error"){

				if (($dateFilter === true and $logDetails["timestamp"] <= $endDate and $logDetails["timestamp"] >= $startDate) or $dateFilter === false) {
					
					if ((isset($_POST["CharacterName"]) and htmlspecialchars($_POST["CharacterName"]) != "" and strpos($logDetails["actor"], htmlspecialchars($_POST["CharacterName"])) !== false) or (!isset($_POST["CharacterName"]) or htmlspecialchars($_POST["CharacterName"]) == "")) {
						
						$toShow = true;
						
					}
				}

			}
		}
		
		if (isset($_POST["all"]) and $_POST["all"] == "true") {
			
			$toShow = true;
			
		}
		
	}

	return $toShow;
	
}

?>
<?php

function adminSearch () {
	$search = "";
	$strict = "false";
	
	if ($_SERVER["REQUEST_METHOD"] == "POST") {
		$search = htmlspecialchars($_POST["search"]);
		if (isset($_POST["strict"])) {
			$strict = htmlspecialchars($_POST["strict"]);
		}
		
		if ($search != "") {
		
			$SearchJson = file_get_contents("https://esi.evetech.net/latest/search/?categories=character&datasource=tranquility&language=en-us&search=" . urlencode($search) . "&strict=" . urlencode($strict) . "");
			$SearchData = json_decode($SearchJson, TRUE);
			
			if (!empty($SearchData)) {
				
				echo "<h2 class='AdminHeader'>Search Results</h2>";
				
				$SearchList = $SearchData["character"];
			
				foreach ($SearchList as $SearchKey => $SearchID) {
					$ParsedName = checkCache(ucfirst("Character"), $SearchID);
					
					showCharacterCard($ParsedName, $SearchID);
					
				}
			}
			else {
				
				echo "<p>No Results Found For Character Search: " . $search . " </p>";
				
			}
			
			makeLogEntry("User Search", $_SESSION["CurrentPage"], $_SESSION["Character Name"], "A character search has been made on the admin control page for " . $search . ".");
			
		}
	}	
}

?>
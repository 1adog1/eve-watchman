<nav class="navbar navbar-expand-md bg-dark navbar-dark sticky-top">
	<ul class="navbar-nav">
		<a class="navbar-brand" href="/">Eve Watchman</a>
		
		<?php 
		
		$pageList = ["Manager" => ["Required Roles" => ["Super Admin", "Configure Corp", "Configure Alliance"], "Link" => "/manage/"], "Character Manager" => ["Required Roles" => ["Super Admin", "ACL Admin", "Character Controller"], "Link" => "/characterManager/"], "ACL Control" => ["Required Roles" => ["Super Admin", "ACL Admin"], "Link" => "/ACL/"], "Site Logs" => ["Required Roles" => ["Super Admin"], "Link" => "/logView/"]];
		
		if ($_SESSION["AccessRoles"] != ["None"] and $_SESSION["AccessRoles"] != []) {
			
			foreach ($pageList as $pageTitle => $pageInfo) {
				
				foreach ($pageInfo["Required Roles"] as $throwaway => $roles) {
					
					if (in_array($roles, $_SESSION["AccessRoles"])) {
						
						echo "
						<li class='nav-item'>
							<a class='nav-link' href='" . $pageInfo["Link"] . "'>" . $pageTitle . "</a>
						</li>
						";
						
						break;
						
					}
					
				}
				
			}
			
		}
		
		?>
		
		
	</ul>
	<ul class="navbar-nav ml-auto">
	
		<?php
		
			$bytes = random_bytes(8);
			$_SESSION["UniqueState"] = bin2hex($bytes);
            
            $relayquerystring = http_build_query([
                "response_type" => "code", 
                "redirect_uri" => $clientredirect, 
                "client_id" => $clientid, 
                "scope" => $clientscopes, 
                "state" => $_SESSION["UniqueState"]
            ]);
            
            $managementquerystring = http_build_query([
                "response_type" => "code", 
                "redirect_uri" => $clientredirect, 
                "client_id" => $clientid, 
                "scope" => "", 
                "state" => $_SESSION["UniqueState"]
            ]);
			
		?>
		
		<li class="nav-item mt-2 mr-2" style="text-align: center;">
			<strong>Relay Character Login</strong>
			<a href="https://login.eveonline.com/v2/oauth/authorize/?<?php echo $relayquerystring; ?>">
				<img class="LoginImage" src="../../resources/images/sso_image.png">
			</a>		
		</li>
		
		<?php if ($_SESSION["AccessRoles"] == ["None"]) : ?>
		
			<li class="nav-item mt-2 mr-2" style="text-align: center;">
				<strong>Management Login</strong>
				<a href="https://login.eveonline.com/v2/oauth/authorize/?<?php echo $managementquerystring; ?>">
					<img class="LoginImage" src="../../resources/images/sso_image.png">
				</a>
			</li>
			
		<?php else : ?>
		
			<li class="nav-item mr-2">
			
				<?php 
					if ($_SESSION["CharacterID"] != 0) {

						echo "
						<div class='h4 mt-2 mr-3'><strong>" . $_SESSION["Character Name"] . "</strong></div>";
					}
				?>				
			
			</li>
		
			<li class="nav-item mr-3">
				<?php 
					if ($_SESSION["CharacterID"] != 0) {

						echo "
						<strong>Corporation: </strong>" . $_SESSION["Corporation Name"] . "<br>
						<strong>Alliance: </strong>" . $_SESSION["Alliance Name"]
						;
					}
				?>
			</li>

			<li class="nav-item mt-2">
				<a href="/eveauth/logout?callback=<?php echo $_SERVER["REQUEST_URI"] ?>" class="btn btn-outline-danger" role="button">Logout</a>
			</li>
			
		<?php endif; ?>
		
	</ul>
</nav>
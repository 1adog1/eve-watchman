<nav class="navbar navbar-expand-md bg-dark navbar-dark sticky-top">
	<ul class="navbar-nav">
		<a class="navbar-brand" href="/">Eve Watchman</a>
		
		<?php 
		
		$pageList = ["Manager" => ["Required Roles" => ["Super Admin", "Configure Corp", "Configure Alliance"], "Link" => "/manage/"], "ACL Control" => ["Required Roles" => ["Super Admin", "ACL Admin"], "Link" => "/ACL/"], "Site Logs" => ["Required Roles" => ["Super Admin"], "Link" => "/logView/"]];
		
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
			
		?>
		
		<li class="nav-item mt-2 mr-2" style="text-align: center;">
			<strong>Relay Character Login</strong>
			<a href="https://login.eveonline.com/oauth/authorize?response_type=code&redirect_uri=<?php echo $clientredirect; ?>&client_id=<?php echo $clientid; ?>&scope=<?php echo $clientscopes; ?>&state=<?php echo $_SESSION["UniqueState"]; ?>">
				<img class="LoginImage" src="../../resources/images/sso_image.png">
			</a>		
		</li>
		
		<?php if ($_SESSION["AccessRoles"] == ["None"]) : ?>
		
			<li class="nav-item mt-2 mr-2" style="text-align: center;">
				<strong>Management Login</strong>
				<a href="https://login.eveonline.com/oauth/authorize?response_type=code&redirect_uri=<?php echo $clientredirect; ?>&client_id=<?php echo $clientid; ?>&scope=&state=<?php echo $_SESSION["UniqueState"]; ?>">
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
				<a href="/eveauth/logout.php?callback=<?php echo $_SERVER["REQUEST_URI"] ?>" class="btn btn-outline-danger" role="button">Logout</a>
			</li>
			
		<?php endif; ?>
		
	</ul>
</nav>
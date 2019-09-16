# Eve Watchman
Eve Watchman is a web app for Eve Online allowing corporations and alliances to receive notifications relating to their structures and sovereignty. It includes a GUI allowing for the management of notifications at the corporation and alliance levels, and can send notifications to both Discord and Slack through their respective Webhook functionalities.

## Requirements
* Apache ≥ 2.4
* PHP ≥ 7
  * The `pdo_mysql` Extension
  * The `curl` Extension
  * The `php_openssl.dll` Extension
* Python ≥ 3.5
  * [requests](https://pypi.org/project/requests/)
  * [PyYaml](https://pypi.org/project/PyYAML/)
  * [schedule](https://pypi.org/project/schedule/)
  * [Python MySQL Connector](https://dev.mysql.com/downloads/connector/python/)
* An SQL Server
  * If you are using MySQL, the Authentication Method **MUST** be the Legacy Version. PDO does not support the use of `caching_sha2_password` Authentication. 
* A Registered Eve Online Application. 
  * This can be setup via the [Eve Online Developers Site](https://developers.eveonline.com/).

## Webapp Setup
* Setup the Configuration File in `/config/config.php` as needed.
* Ensure Apache is configured to allow `.htaccess` files with use of the rewrite engine.
* Ensure Apache is configured to allow https connections.
* Ensure PHP is configured to allow `.user.ini` files. 
* Set `/public` as Document Root in Apache.

## Relay Setup
* After setting up the configuration file make sure to connect to the webserver's home page at least once. 
* Then just run `relay.py` to get the monitoring started.

### To Deploy the Relay on a Seperate Server
In the event that it's not easy to deploy the entire app to one server, the Python-Based Relay can be transferred to another server by following the instructions below:
* Make sure to connect to the webserver at least once after the `/config/config.php` file as been setup.
* Take `relay.py`, `notifier.py`, `ESI.py`, and `testing.py` and add them to the new server. Treat whatever directory they're in as the "top directory" for the step below. 
* Now, take the newly generated `config.json` file in `/config/` on the old server and add it to the directory `/config/` on the new server.

## Supported Notifications
The following notifications are supported and configured according to the following categories:

### Upwell Attack/Reinforcement Events
* StructureDestroyed
* StructureLostArmor
* StructureLostShields
* StructureUnderAttack
### Moon Detonations
* MoonminingAutomaticFracture
* MoonminingLaserFired
### Moon Management
* MoonminingExtractionCancelled
* MoonminingExtractionFinished
* MoonminingExtractionStarted
### Upwell Management Events
* StructureAnchoring
* StructureFuelAlert
* StructureOnline
* StructureUnanchoring
* StructureServicesOffline
* StructureWentHighPower
* StructureWentLowPower
* StructuresReinforcementChanged
* OwnershipTransferred
### Sovereignty Attacks/Reinforcement
* EntosisCaptureStarted
* SovCommandNodeEventStarted
* SovStructureReinforced
* SovStructureDestroyed
### Sovereignty Management
* SovAllClaimAquiredMsg
* SovAllClaimLostMsg
* SovStructureSelfDestructRequested
* SovStructureSelfDestructFinished
* SovStructureSelfDestructCancel
#### Customs Office Events
* OrbitalAttacked
* OrbitalReinforced
### POS Attack Events
* TowerAlertMsg
### POS Management Events
* TowerResourceAlertMsg
* AllAnchoringMsg

## ToDo
* Add page for verifying / managing relay characters.
* Allow multiple characters to be assigned to each configuration (reducing the potential error time). 

## Credits
* Much of the relay is based on the [reconbot](https://github.com/flakas/reconbot) project by flakas.
* The [Unofficial Notification Documentation](https://github.com/antihax/goesi/blob/master/notification/notification.go) by antihax was instrumental in creating many of the custom notifications.

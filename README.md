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
* Setup the Configuration File in `/config/watchmanConfig.ini` as needed.
 * If you need to move this file you'll need to change the path it's accessed from in `/config/config.php`
* Ensure Apache is configured to allow `.htaccess` files with use of the rewrite engine.
* Ensure Apache is configured to allow https connections.
* Ensure PHP is configured to allow `.user.ini` files. 
* Set `/public` as Document Root in Apache.

## Relay Setup
* After setting up the `/config/watchmanConfig.ini` file you can either run `/relay/singleRunRelay.py` as a cronjob or `/relay/automaticallyRunRelay.py` once to begin relaying notifications. 

### To Deploy the Relay on a Seperate Server
In the event that it's not easy to deploy the entire app to one server, the Python-Based Relay can be transferred to another server by following the instructions below:
* Make sure to copy the `/config/watchmanConfig.ini` file along with the `/resources/data/geographicInformation.json` and `/resources/data/TypeIDs.json` files somewhere python can access it after it has been setup.
* Move the `/relay` folder to wherever you'll be running it from.
* In `/relay/relay.py` change  the `pathOverride` variable to an absolute path where your copies of the above 3 files are being stored.

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

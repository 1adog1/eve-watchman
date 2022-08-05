# [WIP] Eve Watchman

Eve Watchman is a web app for Eve Online allowing corporations and alliances to receive notifications relating to their structures and sovereignty. It includes a GUI allowing for the management of notifications at the corporation and alliance levels, and can send notifications to both Discord and Slack through their respective Webhook functionalities.

This Branch is a rewrite of the application using a new custom framework. **Versions prior to Firetower - 0 - 0 cannot be upgraded to this version while still retaining data!**

**Current Version: Firetower - 1 - 0**

## Requirements

The core of this framework requires the following:

* Apache ≥ 2.4
  * The `DocumentRoot` config option to set `/public`
  * The `FallbackResource` config option set to `/index.php`
* PHP ≥ 8.0
  * The `curl` Built-In Extension
  * The `pdo_mysql` Built-In Extension
  * The `openssl` Built-In Extension
  * Python ≥ 3.9
    * [requests](https://pypi.org/project/requests/)
    * [PyYaml](https://pypi.org/project/PyYAML/)
    * [Python MySQL Connector](https://dev.mysql.com/downloads/connector/python/)
* An SQL Server
  * If you are using MySQL, the Authentication Method **MUST** be the Legacy Version. PDO does not support the use of `caching_sha2_password` Authentication.
* A Registered Eve Online Application.
  * This can be setup via the [Eve Online Developers Site](https://developers.eveonline.com/).
  * See the [Configuration File](/config/config.ini.dist) for important details about setting this up.
* [When Using The Neucore Authentication Method] A Neucore Application
  * The application needs the `app-chars` and `app-groups` roles added, along with any groups that you want to be able to set access roles for.
  * _NOTE: It is not currently recommended to use this authentication method, as access is tied to the corporation / alliance of the logged-in character. An update to make this authentication method viable is planned for the future._

## Web App Setup
* Rename the Configuration File in `/config/config.ini.dist` to `/config/config.ini` and setup as needed.
  * If you need to move this file you'll need to change the path it's accessed from in `/config/config.php`

## Relay Setup
* After setting up the `/config/config.ini` file and accessing the webserver at least once, you can run `/scripts/Python/run.py` as a cronjob to begin relaying notifications.
  * It's recommended to run this script once a minute, or at an even higher frequency if you have the capability.

### To Deploy the Relay on a Separate Server
   In the event that it's not easy to deploy the entire app to one server, the Python-Based Relay can be transferred to another server by following the instructions below:
* Make sure to copy the `/config/config.ini` file somewhere python can access it.
* Move the `/scripts/Python/` folder to wherever you'll be running it from.
* In `/Relay/main.py` change the `CONFIG_PATH_OVERRIDE` variable to an absolute path where your copy of `config.ini` is being stored.

## Supported Notifications
The following notifications are supported and configured according to the following categories:

### Upwell Attack/Reinforcement Events
* StructureDestroyed
* StructureLostArmor
* StructureLostShields
* StructureUnderAttack
### Upwell Management Events
* StructureAnchoring
* StructureFuelAlert
* StructureOnline
* StructureUnanchoring
* StructureServicesOffline
* StructureWentHighPower
* StructureWentLowPower
* StructureImpendingAbandonmentAssetsAtRisk
* StructuresReinforcementChanged
* OwnershipTransferred
### Moon Detonations
* MoonminingAutomaticFracture
* MoonminingLaserFired
### Moon Management
* MoonminingExtractionCancelled
* MoonminingExtractionFinished
* MoonminingExtractionStarted
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
### Customs Office Events
* OrbitalAttacked
* OrbitalReinforced
### POS Attack Events
* TowerAlertMsg
### POS Management Events
* TowerResourceAlertMsg
* AllAnchoringMsg

## Credits
* Much of the relay is based on the [reconbot](https://github.com/flakas/reconbot) project by flakas.
* The [Unofficial Notification Documentation](https://github.com/antihax/goesi/blob/master/notification/notification.go) by antihax was instrumental in creating many of the custom notifications.
* Icons used across the web app are from the [Bootstrap Icons](https://icons.getbootstrap.com/) project.

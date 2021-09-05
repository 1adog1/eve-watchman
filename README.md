# [WIP] Eve Watchman

Eve Watchman is a web app for Eve Online allowing corporations and alliances to receive notifications relating to their structures and sovereignty. It includes a GUI allowing for the management of notifications at the corporation and alliance levels, and can send notifications to both Discord and Slack through their respective Webhook functionalities.

This Branch is a Work-In-Progress rewrite of the application using a new custom framework.

## Requirements

The core of this framework requires the following: 

* Apache ≥ 2.4
  * The `DocumentRoot` config option to set `/public`
  * The `FallbackResource` config option set to `/index.php`
* PHP ≥ 8.0
  * The `curl` Built-In Extension
  * The `pdo_mysql` Built-In Extension
  * The `openssl` Built-In Extension
* An SQL Server
  * If you are using MySQL, the Authentication Method **MUST** be the Legacy Version. PDO does not support the use of `caching_sha2_password` Authentication. 
* A Registered Eve Online Application. 
  * This can be setup via the [Eve Online Developers Site](https://developers.eveonline.com/).
* [When Using The Neucore Authentication Method] A Neucore Application
  * The application needs the `app-chars` and `app-groups` roles added, along with any groups that you want to be able to set access roles for.


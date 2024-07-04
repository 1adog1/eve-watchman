# Changelog

Changes for each version along with any requirements to update from the previous version will be listed below.

## Patch Version Firetower – 4 – 2 Update

### Notification Parsing

* Added upcoming names for the new skyhook notifications

### UPDATE INSTRUCTIONS (From Version Firetower – 0 – *)

1. Pause operation of the Relay.
2. Sync up files with the repository.
3. Restart operation of the Relay.

## Patch Version Firetower – 4 – 1 Update

### Notification Parsing

* The following skyhook related notifications are now supported - note their type names will change in the near future:
  * (Skyhook Online) `unknown notification type (281)`
  * (Skyhook Reinforced) `unknown notification type (282)`
  * (Skyhook Under Attack) `unknown notification type (283)`
  * (Skyhook Anchoring) `unknown notification type (285)`

### Testing

* The notification parse testing script is functional again. 

### UPDATE INSTRUCTIONS (From Version Firetower – 0 – *)

1. Pause operation of the Relay.
2. Sync up files with the repository.
3. Restart operation of the Relay.

## Minor Version Firetower – 4 – 0 Update

### Relay Script

* Changed how notifications pulled from ESI are checked against those in the database.
  * Notifications are now only pulled from the database if their ID is in the list of those pulled from ESI, rather than in the list of the relay's 500 most recent notifications.
  * This is the first half of the fix for a rare but significant bug, caused by a character with a large relayable notification history moving to a relay corporation with long-lived, highly active relays. The bug caused the character's old notifications to be relayed repeatedly, until the affected relays were deleted and recreated.
* Fixed a bug where the relay console output didn't accurately reflect what characters were being checked.

### Notification Parsing

* Notifications for Upwell Structures that do not belong to the corporation owning the relay are now suppressed, unless the notification type is `OwnershipTransferred`.
  * This is the second half of the fix to the first bug mentioned in the above section. 

### Web App

* Attempting to access a page that doesn't exist or for which the user doesn't have sufficient privileges now results in a `404 Not Found` Status Code.

### UPDATE INSTRUCTIONS (From Version Firetower – 0 – *)

1. Pause operation of the Relay.
2. Sync up files with the repository.
3. Restart operation of the Relay.

## Minor Version Firetower – 3 – 0 Update

### Relay Script

* Fixed a time complexity oversight that could result in massive delays to the relay.
* Simplified the process of detecting a notification parse failure.
* Notifications which are not approved for relaying are now logged as Suppressed and registered to avoid duplicate checking.

### Notification Testing Script

* Notifications now display the correct time sent.
* Removed reference to print-only mode, which is no longer supported.

### Documentation

* Fixed Version Formatting

### Notification Parsing

* The following corporation related notifications are now supported:
  * `CorpTaxChangeMsg`
  * `CorpNewCEOMsg`
  * `CorpVoteCEORevokedMsg`

* The following shareholder related notifications are now supported:
  * `CorpVoteMsg`
  * `CorpNewsMsg`

### UPDATE INSTRUCTIONS (From Version Firetower – 0 – *)

1. Pause operation of the Relay.
2. Sync up files with the repository.
3. Restart operation of the Relay.


## Patch Version Firetower – 2 – 3 Update

### Relay Script

* Fixed a parsing error with OrbitalReinforced notifications.
* Updated text of OrbitalAttacked notifications changing "Health" to "Shield" remaining.

### Web App

* The app no longer logs first-time connections to the homepage.
* Fixed deprecated code involving passing null to the subject argument of preg_split.

### UPDATE INSTRUCTIONS (From Version Firetower – 0 – *)

1. Pause operation of the Relay.
2. Sync up files with the repository.
3. Restart operation of the Relay.


## Patch Version Firetower – 2 – 2 Update

### Web App

* Fixed deprecated code involving passing null to non-nullable functions.

### UPDATE INSTRUCTIONS (From Version Firetower – 0 – *)

1. Pause operation of the Relay.
2. Sync up files with the repository.
3. Restart operation of the Relay.


## Patch Version Firetower – 2 – 1 Update

### Web App

* Fixed deprecated code in the page handler.

### UPDATE INSTRUCTIONS (From Version Firetower – 0 – *)

1. Pause operation of the Relay.
2. Sync up files with the repository.
3. Restart operation of the Relay.


## Minor Version Firetower – 2 – 0 Update

### App Core

* Added support for the use of Environment Variables instead of a Config file.

### UPDATE INSTRUCTIONS (From Version Firetower – 0 – *)

1. Pause operation of the Relay.
2. Sync up files with the repository.
3. See the [Configuration File](/config/config.ini.dist) for a list of environment keys if you'd like to switch.
4. Restart operation of the Relay.


## Minor Version Firetower – 1 – 0 Update

### Relay Script

* Redesigned the file structure of the Relay and associated files.
* Added blanket logging of all exceptions that occur within the Relay.
* Relay will now attempt to gracefully shutdown in the event of an uncaught exception.

### UPDATE INSTRUCTIONS (From Version Firetower – 0 – *)

1. Pause operation of the Relay.
2. Sync up files with the repository.
3. Restart operation of the Relay.


## Major Version Firetower – 0 – 0 Update

### App Core

* The Web App has received a from-scratch rewrite, and is now based on [Project Overhaul](https://github.com/1adog1/project-overhaul).
  * ESI libraries with support for automatic retries and shared caching have been added in both Python and PHP. This massively increases the speed and reliability of both parts of the app.

### Web App

* The Relay and Character Management pages have had their UI and functionality completely overhauled.
* Director and Station Manager characters are now clearly labeled across the app.

### Relay Management

* Webhooks are now verified and associated with servers and channels when creating a new relay.
* Notification Types are now much more clearly labeled and categorized when creating new relays, with some also containing tooltips with additional information.
* Some client-side checks now occur when setting up a relay to ensure a user has input all the required information before enabling the creation button.

### Character Management
* Relay characters are now categorized by alliance and corporation.
  * These corporations are labeled based on the following:
    * Has at least one valid, authed character: Green
    * Has at least one authed in character, but none are valid: Yellow
    * Has no authed in characters (see next point down): Red
* An option has been added to display all corporations in an alliance that don't have any relay characters authed in.

### Relay
* The Relay and associated functionality has received a from-scratch rewrite.
* The Relay will now regularly check and update the affiliation, token validity, and roles of relay characters.
* A dedicated class has been made for controlling the sending of messages to webhooks.
  * It now respects the Retry-After header when a webhook is rate-limited.
* The maximum response time for notifications has been reduced from 11 to 10 minutes (inline with ESI's cache time on the endpoint).

### Notification Parsing
* The format of relayed notifications has been overhauled.
  * The corporation a notification was sent for is now included for all relay messages.
* Notification parsing should now be faster and much more robust.
  * Unexpected failures during parsing now lead to the notification's raw data being sent instead of the entire Relay hard-crashing.
* The `StructureImpendingAbandonmentAssetsAtRisk` notification is now supported for corporation structures.
* `OwnershipTransferred` notifications involving Customs Offices are now correctly parsed.

### UPDATE INSTRUCTIONS (From All Previous Versions)

1. Shut down the Web App and Relay.
2. Delete the old project folder and drop the old database (backing up as desired).
3. Start a new project folder and sync it up with the `main` branch of the repository.
4. Setup `/config/config.ini`. Make sure to use a different Eve Online application and database name than your previous instance of the app used to use.
5. Continue following the instructions over in the [README](README.md) to setup the app from scratch.

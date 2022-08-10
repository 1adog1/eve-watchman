# Changelog

Changes for each version along with any requirements to update from the previous version will be listed below.

## Minor Version Firetower-2-0 Update

### App Core

* Added support for the use of Environment Variables instead of a Config file.

### UPDATE INSTRUCTIONS (From Version Firetower-0-*)

1. Pause operation of the Relay.
2. Sync up files with the repository.
3. See the [Configuration File](/config/config.ini.dist) for a list of environment keys if you'd like to switch.
4. Restart operation of the Relay.

## Minor Version Firetower-1-0 Update

### Relay Script

* Redesigned the file structure of the Relay and associated files.
* Added blanket logging of all exceptions that occur within the Relay.
* Relay will now attempt to gracefully shutdown in the event of an uncaught exception.

### UPDATE INSTRUCTIONS (From Version Firetower-0-*)

1. Pause operation of the Relay.
2. Sync up files with the repository.
3. Restart operation of the Relay.

## Major Version Firetower-0-0 Update

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

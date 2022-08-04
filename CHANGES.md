# Changelog

Changes for each version along with any requirements to update from the previous version will be listed below.

## Minor Version Firetower-1-0 Update

### Relay Script

* Redesigned the file structure of the Relay and associated files.
* Added blanket logging of all exceptions that occur within the Relay.
* Relay will now attempt to gracefully shutdown in the event of an uncaught exception.

### Update Instructions (From Version Firetower-0-*)

1. Pause operation of the Relay.
2. Sync up files with the repository.
3. Restart operation of the Relay.

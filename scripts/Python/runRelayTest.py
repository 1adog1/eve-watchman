from Notifications import Notification
from Terminus import RelayTerminus
import ESI

import time
import yaml
import requests
import inspect
import os
import configparser
import json

from pprint import pprint
from datetime import datetime
from pathlib import Path

import mysql.connector as DatabaseConnector

#If you've moved your config.ini file, set this variable to the path of the folder containing it (no trailing slash).
CONFIG_PATH_OVERRIDE = None

def dataFile(extraFolder):

    filename = inspect.getframeinfo(inspect.currentframe()).filename
    path = os.path.join(os.path.dirname(os.path.abspath(filename)), "../..")

    dataLocation = str(path) + extraFolder

    return(dataLocation)

configPath = (CONFIG_PATH_OVERRIDE) if (CONFIG_PATH_OVERRIDE is not None) else (dataFile("/config"))

if Path(configPath + "/config.ini").is_file():

    config = configparser.ConfigParser()
    config.read(dataFile("/config") + "/config.ini")

    databaseInfo = config["Database"]
    EveAuthInfo = config["Eve Authentication"]

else:

    try:

        databaseInfo = {}
        databaseInfo["DatabaseServer"] = os.environ["ENV_WATCHMAN_DATABASE_SERVER"]
        databaseInfo["DatabasePort"] = os.environ["ENV_WATCHMAN_DATABASE_PORT"]
        databaseInfo["DatabaseUsername"] = os.environ["ENV_WATCHMAN_DATABASE_USERNAME"]
        databaseInfo["DatabasePassword"] = os.environ["ENV_WATCHMAN_DATABASE_PASSWORD"]
        databaseInfo["DatabaseName"] = os.environ["ENV_WATCHMAN_DATABASE_NAME"]

        EveAuthInfo = {}
        EveAuthInfo["ClientID"] = os.environ["ENV_WATCHMAN_EVE_CLIENT_ID"]
        EveAuthInfo["ClientSecret"] = os.environ["ENV_WATCHMAN_EVE_CLIENT_SECRET"]
        EveAuthInfo["ClientScopes"] = os.environ["ENV_WATCHMAN_EVE_CLIENT_SCOPES"] if "ENV_WATCHMAN_EVE_CLIENT_SCOPES" in os.environ else "esi-universe.read_structures.v1 esi-characters.read_corporation_roles.v1 esi-characters.read_notifications.v1"
        EveAuthInfo["DefaultScopes"] = os.environ["ENV_WATCHMAN_EVE_DEFAULT_SCOPES"] if "ENV_WATCHMAN_EVE_DEFAULT_SCOPES" in os.environ else "esi-search.search_structures.v1"
        EveAuthInfo["ClientRedirect"] = os.environ["ENV_WATCHMAN_EVE_CLIENT_REDIRECT"]
        EveAuthInfo["AuthType"] = os.environ["ENV_WATCHMAN_EVE_AUTH_TYPE"] if "ENV_WATCHMAN_EVE_AUTH_TYPE" in os.environ else "Eve"
        EveAuthInfo["SuperAdmins"] = os.environ["ENV_WATCHMAN_EVE_SUPER_ADMINS"]

    except:

        raise Warning("No Configuration File or Required Environment Variables Found!")

sq1Database = DatabaseConnector.connect(
    user=databaseInfo["DatabaseUsername"],
    password=databaseInfo["DatabasePassword"],
    host=databaseInfo["DatabaseServer"],
    port=int(databaseInfo["DatabasePort"]),
    database=databaseInfo["DatabaseName"]
)

"""
Keys for the testingData dictionary should be notification types (as given by ESI).

Values can be one of the following:
    - A YAML String containing notification data as given by ESI.
    - A Dictionary containing the parsed data of a YAML String as given by ESI.
    - A List containing multiple variations of the above two possibilities to test.

"""
testingData = {}

#Slack or Discord
testingPlatform = "Slack"

#Webhook URL corresponding to the platform in testingPlatform.
testingWebhook = ""

#none (string), here, channel, or everyone
testingPingType = "none"

#The character ID of a relay character authed into the webapp. Your choice will impact the test's ability to evaluate structure names.
relayCharacterID = 0
#The corporation ID and name being relayed for. If it doesn't match the owner of a notification's structure the notification may be suppressed.
relayForID = 0
relayForName = ""

ESIAuth = ESI.AuthHandler(
    sq1Database,
    EveAuthInfo["ClientID"],
    EveAuthInfo["ClientSecret"],
    "Relay"
)

for type, data in testingData.items():

    if isinstance(data, dict):

        notificationData = Notification(
            sq1Database,
            type,
            int(time.time()),
            yaml.dump(data, Dumper=yaml.SafeDumper),
            relayForID,
            relayForName,
            testingPlatform,
            testingPingType,
            ESIAuth.getAccessToken(relayCharacterID, retries=1)
        )

        notificationData.formatForRelaying()
        sender = RelayTerminus(notificationData.outputData, testingPlatform, testingWebhook)
        sender.send(2)

    elif isinstance(data, str):

        notificationData = Notification(
            sq1Database,
            type,
            int(time.time()),
            data,
            relayForID,
            relayForName,
            testingPlatform,
            testingPingType,
            ESIAuth.getAccessToken(relayCharacterID, retries=1)
        )

        notificationData.formatForRelaying()
        sender = RelayTerminus(notificationData.outputData, testingPlatform, testingWebhook)
        sender.send(2)

    elif isinstance(data, list):

        for nestedData in data:

            if isinstance(nestedData, dict):

                notificationData = Notification(
                    sq1Database,
                    type,
                    int(time.time()),
                    yaml.dump(nestedData, Dumper=yaml.SafeDumper),
                    relayForID,
                    relayForName,
                    testingPlatform,
                    testingPingType,
                    ESIAuth.getAccessToken(relayCharacterID, retries=1)
                )

                notificationData.formatForRelaying()
                sender = RelayTerminus(notificationData.outputData, testingPlatform, testingWebhook)
                sender.send(2)

            elif isinstance(nestedData, str):

                notificationData = Notification(
                    sq1Database,
                    type,
                    int(time.time()),
                    nestedData,
                    relayForID,
                    relayForName,
                    testingPlatform,
                    testingPingType,
                    ESIAuth.getAccessToken(relayCharacterID, retries=1)
                )

                notificationData.formatForRelaying()
                sender = RelayTerminus(notificationData.outputData, testingPlatform, testingWebhook)
                sender.send(2)

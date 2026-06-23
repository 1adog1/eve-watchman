from Notifications import Notification
from Terminus import RelayTerminus
import ESI

import time
import yaml

import mysql.connector as DatabaseConnector

from OverhaulConfig import Config

configVariables = Config()

sq1Database = DatabaseConnector.connect(
    user=configVariables.database.username,
    password=configVariables.database.password,
    host=configVariables.database.server,
    port=int(configVariables.database.port),
    database=configVariables.database.name
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
    configVariables.eve_auth.client_id,
    configVariables.eve_auth.client_secret,
    "Relay"
)

for type, data in testingData.items():

    if isinstance(data, dict):

        notificationData = Notification(
            sq1Database,
            configVariables.versioning,
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
            configVariables.versioning,
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
                    configVariables.versioning,
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
                    configVariables.versioning,
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

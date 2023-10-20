from Notifications.Notification_Utilities import NotificationUtilities
from Notifications.Notification_Type_Register import TypeRegister
from Notifications.Notification_Types import TypeFormatter
import yaml

class Notification(NotificationUtilities, TypeRegister, TypeFormatter):

    pingTable = {
        "Slack": {
            "everyone": "<!everyone>",
            "channel": "<!channel>",
            "here": "<!here>",
            "none": ""
        },
        "Discord": {
            "everyone": "@everyone",
            "channel": "@everyone",
            "here": "@here",
            "none": ""
        }
    }

    def __init__(
        self,
        database_connection,
        incoming_type,
        incoming_time,
        incoming_text,
        relay_for_id,
        relay_for,
        relay_platform,
        relay_ping,
        access_token
    ):

        self.outputData = {
            "Title": "",
            "Fields": {},
            "Ping": self.pingTable[relay_platform][relay_ping],
            "For": relay_for,
            "Time": incoming_time
        }

        self.parseFailure = False

        self.timestamp = incoming_time
        self.ldap_timestamp = int((int(self.timestamp) + 11644473600) * 10000000)
        self.type = incoming_type
        self.data = yaml.load(incoming_text, Loader=yaml.SafeLoader)
        self.ping_type = relay_ping
        self.platform = relay_platform

        self.relay_owner = relay_for_id
        self.verified_owner = None

        NotificationUtilities.__init__(self, database_connection, access_token)
        TypeRegister.__init__(self)

    def shouldItRelay(self, recent_pos_fuel_alerts = []):

        return (
            (
                self.verified_owner is None
                or self.type == "OwnershipTransferred"
                or int(self.verified_owner) == int(self.relay_owner)
            )
            and
            (
                self.type != "StructureImpendingAbandonmentAssetsAtRisk"
                or self.data["isCorpOwned"]
            )
            and (
                self.type != "TowerResourceAlertMsg"
                or int(self.data["moonID"]) not in recent_pos_fuel_alerts
            )
        )

    def formatForRelaying(self):

        if (
            self.type in self.typeList and
            callable(getattr(self, self.typeList[self.type], None))
        ):

            method = getattr(self, self.typeList[self.type])

            try:

                method(self.data)

            except:

                self.outputData["Title"] = "A " + self.type + " Notification Failed to Parse!"
                self.outputData["Fields"] = {"Raw Data": str(self.data)}
                self.parseFailure = True

        else:

            raise NameError("The " + self.type + " notification type does not have a valid registered method.")

from Timers.Timer_Utilities import TimerUtilities
from Timers.Timer_Type_Register import TimerRegister
from Timers.Timer_Types import TimerFormatter
import time
import yaml

class Timers(TimerUtilities, TimerRegister, TimerFormatter):

    def __init__(
        self,
        database_connection,
        incoming_type,
        incoming_time,
        incoming_text,
        relay_for_id,
        timerboard_type,
        access_token
    ):

        self.postData = {}
        self.parseFailure = False

        self.timestamp = incoming_time
        self.ldap_timestamp = int((int(self.timestamp) + 11644473600) * 10000000)
        self.type = incoming_type
        self.data = yaml.load(incoming_text, Loader=yaml.SafeLoader)
        self.platform = timerboard_type

        self.timer_owner = relay_for_id
        self.verified_owner = None

        TimerUtilities.__init__(self, database_connection, access_token)
        TimerRegister.__init__(self)

    def shouldItPost(self):

        return (
            self.timer > time.time()
            and (
                self.verified_owner is None
                or int(self.verified_owner) == int(self.timer_owner)
            )
        )

    def formatTimer(self):

        if (
            self.type in self.typeList and
            callable(getattr(self, self.typeList[self.type], None))
        ):

            method = getattr(self, self.typeList[self.type])

            try:

                method(self.data, self.timer_owner, self.platform)

            except:

                self.parseFailure = True

        else:

            raise NameError("The " + self.type + " notification type does not have a valid registered method.")
        
    def getPostData(self):

        if self.platform == "RC2":

            return self.getRC2()

        else:

            raise NameError("The " + self.platform + " platform does not exist.")
        
    def getRC2(self):

        return {
            "type_id": self.postData["Structure Type ID"],
            "name": self.postData["Structure Name"],
            "timer_expires": self.postData["Timer ISO"],
            "timer_type": self.postData["Timer Type"],
            "power_state": self.postData["Structure State"],
            "owner": self.postData["Owner Ticker"],
            "is_hostile": False,
            "solar_system": self.postData["System ID"],
            "dashboard_id": 0
        }

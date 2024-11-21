class TimerFormatter(object):

    def CitadelLostShields(self, notificationData, ownerID, boardType):

        affiliationData = self.getCorporationAffiliation(ownerID)

        self.postData["Structure Type ID"] = int(notificationData["structureTypeID"])
        self.postData["Structure Name"] = self.getStructureName(notificationData["structureID"], notificationData["structureTypeID"], notificationData["solarsystemID"])
        self.postData["Timer ISO"] = self.parseTimestamp(notificationData["timestamp"])
        self.postData["Timer Type"] = "Armor"
        self.postData["Structure State"] = "High"

        if affiliationData["Alliance Ticker"] is not None:
            self.postData["Owner Ticker"] = affiliationData["Alliance Ticker"]
        else:
            self.postData["Owner Ticker"] = affiliationData["Corporation Ticker"]

        self.postData["System ID"] = int(notificationData["solarsystemID"])

    def CitadelLostArmor(self, notificationData, ownerID, boardType):

        affiliationData = self.getCorporationAffiliation(ownerID)

        self.postData["Structure Type ID"] = int(notificationData["structureTypeID"])
        self.postData["Structure Name"] = self.getStructureName(notificationData["structureID"], notificationData["structureTypeID"], notificationData["solarsystemID"])
        self.postData["Timer ISO"] = self.parseTimestamp(notificationData["timestamp"])
        self.postData["Timer Type"] = "Hull"
        self.postData["Structure State"] = "Low"

        if affiliationData["Alliance Ticker"] is not None:
            self.postData["Owner Ticker"] = affiliationData["Alliance Ticker"]
        else:
            self.postData["Owner Ticker"] = affiliationData["Corporation Ticker"]

        self.postData["System ID"] = int(notificationData["solarsystemID"])

    def OrbitalReinforced(self, notificationData, ownerID, boardType):

        affiliationData = self.getCorporationAffiliation(ownerID)

        self.postData["Structure Type ID"] = int(notificationData["typeID"])
        self.postData["Structure Name"] = self.getStructureName(None, notificationData["typeID"], notificationData["solarSystemID"], notificationData["planetID"])
        self.postData["Timer ISO"] = self.parseTimestamp(notificationData["reinforceExitTime"])
        self.postData["Timer Type"] = "Armor"
        self.postData["Structure State"] = "High"

        if affiliationData["Alliance Ticker"] is not None:
            self.postData["Owner Ticker"] = affiliationData["Alliance Ticker"]
        else:
            self.postData["Owner Ticker"] = affiliationData["Corporation Ticker"]

        self.postData["System ID"] = int(notificationData["solarSystemID"])

    def SkyhookReinforced(self, notificationData, ownerID, boardType):

        affiliationData = self.getCorporationAffiliation(ownerID)

        self.postData["Structure Type ID"] = int(notificationData["typeID"])
        self.postData["Structure Name"] = self.getStructureName(None, notificationData["typeID"], notificationData["solarsystemID"], notificationData["planetID"])
        self.postData["Timer ISO"] = self.parseTimestamp(notificationData["timestamp"])
        self.postData["Timer Type"] = "Armor"
        self.postData["Structure State"] = "High"

        if affiliationData["Alliance Ticker"] is not None:
            self.postData["Owner Ticker"] = affiliationData["Alliance Ticker"]
        else:
            self.postData["Owner Ticker"] = affiliationData["Corporation Ticker"]

        self.postData["System ID"] = int(notificationData["solarsystemID"])

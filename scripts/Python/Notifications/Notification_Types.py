class TypeFormatter(object):

    def EntosisCaptureStarted(self, notificationData):

        systemName = self.getSystemName(notificationData["solarSystemID"])
        structureType = self.getTypeName(notificationData["structureTypeID"])

        self.outputData["Title"] = "The {system} {structure} is Being Captured!".format(system=systemName, structure=structureType)
        self.outputData["Fields"]["Location"] = self.getLocationLink(notificationData["solarSystemID"])
        self.outputData["Fields"]["ADM"] = self.getADM(notificationData["solarSystemID"], notificationData["structureTypeID"])

    def SovCommandNodeEventStarted(self, notificationData):

        systemName = self.getSystemName(notificationData["solarSystemID"])
        structureType = {1: "Territorial Claim Unit", 2: "Infrastructure Hub"}[int(notificationData["campaignEventType"])]

        self.outputData["Title"] = "Command Nodes Have Decloaked For The {system} {structure}!".format(system=systemName, structure=structureType)
        self.outputData["Fields"]["Location"] = self.getLocationLink(notificationData["solarSystemID"])
        self.outputData["Fields"]["Constellation"] = self.getConstellationLink(notificationData["constellationID"])

    def SovStructureReinforced(self, notificationData):

        systemName = self.getSystemName(notificationData["solarSystemID"])
        structureType = {1: "Territorial Claim Unit", 2: "Infrastructure Hub"}[int(notificationData["campaignEventType"])]

        self.outputData["Title"] = "The {system} {structure} Has Been Reinforced!".format(system=systemName, structure=structureType)
        self.outputData["Fields"]["Location"] = self.getLocationLink(notificationData["solarSystemID"])
        self.outputData["Fields"]["Nodes Decloak"] = self.parseTimestamp(notificationData["decloakTime"])

    def SovStructureDestroyed(self, notificationData):

        systemName = self.getSystemName(notificationData["solarSystemID"])
        structureType = self.getTypeName(notificationData["structureTypeID"])

        self.outputData["Title"] = "The {system} {structure} Has Been Destroyed!".format(system=systemName, structure=structureType)
        self.outputData["Fields"]["Location"] = self.getLocationLink(notificationData["solarSystemID"])

    def SovClaimAcquired(self, notificationData):

        systemName = self.getSystemName(notificationData["solarSystemID"])
        corpData = self.getCorporationAffiliation(notificationData["corpID"])

        self.outputData["Title"] = "Sovereignty Acquired In {system}!".format(system=systemName)
        self.outputData["Fields"]["Location"] = self.getLocationLink(notificationData["solarSystemID"])
        self.outputData["Fields"]["Owner"] = self.getCorpLink(corpData)

    def SovClaimLost(self, notificationData):

        systemName = self.getSystemName(notificationData["solarSystemID"])
        corpData = self.getCorporationAffiliation(notificationData["corpID"])

        self.outputData["Title"] = "Sovereignty Lost In {system}!".format(system=systemName)
        self.outputData["Fields"]["Location"] = self.getLocationLink(notificationData["solarSystemID"])
        self.outputData["Fields"]["Owner"] = self.getCorpLink(corpData)

    def SovSelfDestructRequested(self, notificationData):

        systemName = self.getSystemName(notificationData["solarSystemID"])
        structureType = self.getTypeName(notificationData["structureTypeID"])

        self.outputData["Title"] = "A Self-Destruct Request Has Been Made For The {system} {structure}!".format(system=systemName, structure=structureType)
        self.outputData["Fields"]["Location"] = self.getLocationLink(notificationData["solarSystemID"])
        self.outputData["Fields"]["Requester"] = self.getEntityLink(notificationData["charID"])
        self.outputData["Fields"]["Destruction Time"] = self.parseTimestamp(notificationData["destructTime"])

    def SovSelfDestructFinished(self, notificationData):

        systemName = self.getSystemName(notificationData["solarSystemID"])
        structureType = self.getTypeName(notificationData["structureTypeID"])

        self.outputData["Title"] = "The {system} {structure} Has Self-Destructed!".format(system=systemName, structure=structureType)
        self.outputData["Fields"]["Location"] = self.getLocationLink(notificationData["solarSystemID"])

    def SovSelfDestructCancel(self, notificationData):

        systemName = self.getSystemName(notificationData["solarSystemID"])
        structureType = self.getTypeName(notificationData["structureTypeID"])

        self.outputData["Title"] = "The Self-Destruct Request For The {system} {structure} Has Been Canceled!".format(system=systemName, structure=structureType)
        self.outputData["Fields"]["Location"] = self.getLocationLink(notificationData["solarSystemID"])
        self.outputData["Fields"]["Canceller"] = self.getEntityLink(notificationData["charID"])

    def ExtractionStarted(self, notificationData):

        structureName = self.getStructure(notificationData["structureID"], notificationData["structureTypeID"], notificationData["solarSystemID"])

        self.outputData["Title"] = "Extraction Started For {structure}!".format(structure=structureName)
        self.outputData["Fields"]["Location"] = self.getLocationLink(systemID=notificationData["solarSystemID"], moonID=notificationData["moonID"])
        self.outputData["Fields"]["Started By"] = self.getEntityLink(notificationData["startedBy"])
        self.outputData["Fields"]["Ready At"] = self.parseTimestamp(notificationData["readyTime"])
        self.outputData["Fields"]["Auto-Detonation At"] = self.parseTimestamp(notificationData["autoTime"])

    def ExtractionFinished(self, notificationData):

        structureName = self.getStructure(notificationData["structureID"], notificationData["structureTypeID"], notificationData["solarSystemID"])

        self.outputData["Title"] = "{structure} is Ready to Detonate!".format(structure=structureName)
        self.outputData["Fields"]["Location"] = self.getLocationLink(systemID=notificationData["solarSystemID"], moonID=notificationData["moonID"])
        self.outputData["Fields"]["Auto-Detonation At"] = self.parseTimestamp(notificationData["autoTime"])
        #This is a bit of a mess, but it beats a standalone for loop with multi-line string concatenation
        self.outputData["Fields"]["Ore Available"] = "```\n" + "\n".join(["{ore}: {amount:,} m³".format(
            ore=self.getTypeName(type),
            amount=round(amount)
        ) for type, amount in notificationData["oreVolumeByType"].items()]) + "\n```"

    def ExtractionCancelled(self, notificationData):

        structureName = self.getStructure(notificationData["structureID"], notificationData["structureTypeID"], notificationData["solarSystemID"])

        self.outputData["Title"] = "The Extraction For {structure} Has Been Cancelled!".format(structure=structureName)
        self.outputData["Fields"]["Location"] = self.getLocationLink(systemID=notificationData["solarSystemID"], moonID=notificationData["moonID"])
        self.outputData["Fields"]["Cancelled By"] = "Outside Influence" if notificationData["cancelledBy"] is None else self.getEntityLink(notificationData["cancelledBy"])

    def AutomaticFracture(self, notificationData):

        structureName = self.getStructure(notificationData["structureID"], notificationData["structureTypeID"], notificationData["solarSystemID"])

        self.outputData["Title"] = "{structure} Has Automatically Detonated!".format(structure=structureName)
        self.outputData["Fields"]["Location"] = self.getLocationLink(systemID=notificationData["solarSystemID"], moonID=notificationData["moonID"])
        #This is a bit of a mess, but it beats a standalone for loop with multi-line string concatenation
        self.outputData["Fields"]["Ore Available"] = "```\n" + "\n".join(["{ore}: {amount:,} m³".format(
            ore=self.getTypeName(type),
            amount=round(amount)
        ) for type, amount in notificationData["oreVolumeByType"].items()]) + "\n```"

    def ManualFracture(self, notificationData):

        structureName = self.getStructure(notificationData["structureID"], notificationData["structureTypeID"], notificationData["solarSystemID"])

        self.outputData["Title"] = "{structure} Has Been Manually Detonated!".format(structure=structureName)
        self.outputData["Fields"]["Location"] = self.getLocationLink(systemID=notificationData["solarSystemID"], moonID=notificationData["moonID"])
        self.outputData["Fields"]["Detonated By"] = self.getEntityLink(notificationData["firedBy"])
        #This is a bit of a mess, but it beats a standalone for loop with multi-line string concatenation
        self.outputData["Fields"]["Ore Available"] = "```\n" + "\n".join(["{ore}: {amount:,} m³".format(
            ore=self.getTypeName(type),
            amount=round(amount)
        ) for type, amount in notificationData["oreVolumeByType"].items()]) + "\n```"

    def CitadelAnchoring(self, notificationData):

        structureName = self.getStructure(notificationData["structureID"], notificationData["structureTypeID"], notificationData["solarsystemID"])
        corpData = self.getCorporationAffiliation(notificationData["ownerCorpLinkData"][2])

        self.outputData["Title"] = "{structure} Has Started Anchoring!".format(structure=structureName)
        self.outputData["Fields"]["Location"] = self.getLocationLink(notificationData["solarsystemID"])
        self.outputData["Fields"]["Owner"] = self.getCorpLink(corpData)
        self.outputData["Fields"]["Anchored At"] = self.parseTimestamp(self.ldap_timestamp + notificationData["timeLeft"])

    def CitadelUnanchoring(self, notificationData):

        structureName = self.getStructure(notificationData["structureID"], notificationData["structureTypeID"], notificationData["solarsystemID"])
        corpData = self.getCorporationAffiliation(notificationData["ownerCorpLinkData"][2])

        self.outputData["Title"] = "{structure} Has Started Unanchoring!".format(structure=structureName)
        self.outputData["Fields"]["Location"] = self.getLocationLink(notificationData["solarsystemID"])
        self.outputData["Fields"]["Owner"] = self.getCorpLink(corpData)
        self.outputData["Fields"]["Unanchored At"] = self.parseTimestamp(self.ldap_timestamp + notificationData["timeLeft"])

    def CitadelOnline(self, notificationData):

        structureName = self.getStructure(notificationData["structureID"], notificationData["structureTypeID"], notificationData["solarsystemID"])

        self.outputData["Title"] = "{structure} is Online!".format(structure=structureName)
        self.outputData["Fields"]["Location"] = self.getLocationLink(notificationData["solarsystemID"])

    def HighPower(self, notificationData):

        structureName = self.getStructure(notificationData["structureID"], notificationData["structureTypeID"], notificationData["solarsystemID"])

        self.outputData["Title"] = "{structure} Went High Power!".format(structure=structureName)
        self.outputData["Fields"]["Location"] = self.getLocationLink(notificationData["solarsystemID"])

    def LowPower(self, notificationData):

        structureName = self.getStructure(notificationData["structureID"], notificationData["structureTypeID"], notificationData["solarsystemID"])

        self.outputData["Title"] = "{structure} Went Low Power!".format(structure=structureName)
        self.outputData["Fields"]["Location"] = self.getLocationLink(notificationData["solarsystemID"])

    def AbandonmentRisk(self, notificationData):

        structureName = self.getStructure(notificationData["structureID"], notificationData["structureTypeID"], notificationData["solarsystemID"])

        self.outputData["Title"] = "{structure} is at Risk of Becoming Abandoned!".format(structure=structureName)
        self.outputData["Fields"]["Location"] = self.getLocationLink(notificationData["solarsystemID"])
        self.outputData["Fields"]["Abandoned In"] = "{remaining} Days".format(remaining=notificationData["daysUntilAbandon"])

    def CitadelFuelAlert(self, notificationData):

        structureName = self.getStructure(notificationData["structureID"], notificationData["structureTypeID"], notificationData["solarsystemID"])

        self.outputData["Title"] = "{structure} is Low on Fuel!".format(structure=structureName)
        self.outputData["Fields"]["Location"] = self.getLocationLink(notificationData["solarsystemID"])
        #This is a bit of a mess, but it beats a standalone for loop with multi-line string concatenation
        self.outputData["Fields"]["Fuel Remaining"] = "```\n" + "\n".join(["{amount:,} {fuel}s".format(
            fuel=self.getTypeName(type),
            amount=amount
        ) for amount, type in notificationData["listOfTypesAndQty"]]) + "\n```"

    def CitadelServicesOffline(self, notificationData):

        structureName = self.getStructure(notificationData["structureID"], notificationData["structureTypeID"], notificationData["solarsystemID"])

        self.outputData["Title"] = "{structure} Has Had Services Go Offline!".format(structure=structureName)
        self.outputData["Fields"]["Location"] = self.getLocationLink(notificationData["solarsystemID"])
        self.outputData["Fields"]["Offline Services"] = "```\n" + "\n".join([self.getTypeName(type) for type in notificationData["listOfServiceModuleIDs"]]) + "\n```"

    def OwnershipTransferred(self, notificationData):

        structureName = self.getStructure(notificationData["structureID"], notificationData["structureTypeID"], notificationData["solarSystemID"], notificationData["structureName"])
        oldCorpData = self.getCorporationAffiliation(notificationData["oldOwnerCorpID"])
        newCorpData = self.getCorporationAffiliation(notificationData["newOwnerCorpID"])

        self.outputData["Title"] = "{structure} Has Changed Hands!".format(structure=structureName)
        self.outputData["Fields"]["Location"] = self.getLocationLink(notificationData["solarSystemID"])
        self.outputData["Fields"]["Transferrer"] = self.getEntityLink(notificationData["charID"])
        self.outputData["Fields"]["Ownership Trace"] = "{old} ➜ {new}".format(
            old=self.getCorpLink(oldCorpData),
            new=self.getCorpLink(newCorpData)
        )

    def CitadelReinforcementChanged(self, notificationData):

        typeBreakdown = {}
        for eachStructure in notificationData["allStructureInfo"]:
            if eachStructure[2] not in typeBreakdown:
                typeBreakdown[eachStructure[2]] = {"Name": self.getTypeName(eachStructure[2]), "Count": 0}
            typeBreakdown[eachStructure[2]]["Count"] += 1

        finalBreakdown = dict(sorted(typeBreakdown.items(), key=lambda x: x[1]["Name"]))

        self.outputData["Title"] = "Citadel Reinforcement Cycles Changed!"
        self.outputData["Fields"]["Structure Breakdown"] = "```\n" + "\n".join(["{type}: {quantity:,}".format(
            type=info["Name"],
            quantity=info["Count"]
        ) for type, info in finalBreakdown.items()]) + "\n```"
        self.outputData["Fields"]["New Time"] = "{hour}:00".format(hour=notificationData["hour"])
        self.outputData["Fields"]["Takes Effect"] = self.parseTimestamp(notificationData["timestamp"])

    def CitadelUnderAttack(self, notificationData):

        structureName = self.getStructure(notificationData["structureID"], notificationData["structureTypeID"], notificationData["solarsystemID"])

        self.outputData["Title"] = "{structure} is Under Attack!".format(structure=structureName)
        self.outputData["Fields"]["Location"] = self.getLocationLink(notificationData["solarsystemID"])
        self.outputData["Fields"]["Attacker"] = self.getEntityLink(notificationData["charID"])
        self.outputData["Fields"]["Health Remaining"] = "{shield:.2f}% Shield | {armor:.2f}% Armor | {structure:.2f}% Structure".format(
            shield=notificationData["shieldPercentage"],
            armor=notificationData["armorPercentage"],
            structure=notificationData["hullPercentage"]
        )

    def CitadelLostShields(self, notificationData):

        structureName = self.getStructure(notificationData["structureID"], notificationData["structureTypeID"], notificationData["solarsystemID"])

        self.outputData["Title"] = "{structure} Has Lost Shields!".format(structure=structureName)
        self.outputData["Fields"]["Location"] = self.getLocationLink(notificationData["solarsystemID"])
        self.outputData["Fields"]["Vulnerable At"] = self.parseTimestamp(notificationData["timestamp"])

    def CitadelLostArmor(self, notificationData):

        structureName = self.getStructure(notificationData["structureID"], notificationData["structureTypeID"], notificationData["solarsystemID"])

        self.outputData["Title"] = "{structure} Has Lost Armor!".format(structure=structureName)
        self.outputData["Fields"]["Location"] = self.getLocationLink(notificationData["solarsystemID"])
        self.outputData["Fields"]["Vulnerable At"] = self.parseTimestamp(notificationData["timestamp"])

    def CitadelDestroyed(self, notificationData):

        structureName = self.getStructure(notificationData["structureID"], notificationData["structureTypeID"], notificationData["solarsystemID"])
        corpData = self.getCorporationAffiliation(notificationData["ownerCorpLinkData"][2])

        self.outputData["Title"] = "{structure} Has Been Destroyed!".format(structure=structureName)
        self.outputData["Fields"]["Location"] = self.getLocationLink(notificationData["solarsystemID"])
        self.outputData["Fields"]["Owner"] = self.getCorpLink(corpData)

        if notificationData["isAbandoned"]:
            self.outputData["Fields"]["State"] = "Abandoned"

    def OrbitalAttacked(self, notificationData):

        self.outputData["Title"] = "A Customs Office is Under Attack!"
        self.outputData["Fields"]["Location"] = self.getLocationLink(notificationData["solarSystemID"], planetID=notificationData["planetID"])
        self.outputData["Fields"]["Attacker"] = self.getEntityLink(notificationData["aggressorID"])
        self.outputData["Fields"]["Health Remaining"] = "{shield:.2%}".format(shield=notificationData["shieldLevel"])

    def OrbitalReinforced(self, notificationData):

        self.outputData["Title"] = "A Customs Office Has Been Reinforced!"
        self.outputData["Fields"]["Location"] = self.getLocationLink(notificationData["solarSystemID"], planetID=notificationData["planetID"])
        self.outputData["Fields"]["Attacker"] = self.getEntityLink(notificationData["aggressorID"])
        self.outputData["Fields"]["Health Remaining"] = "{shield:.2%}".format(shield=notificationData["shieldLevel"])
        self.outputData["Fields"]["Vulnerable At"] = self.parseTimestamp(notificationData["reinforceExitTime"])

    def TowerAnchoring(self, notificationData):

        structureType = self.getTypeName(notificationData["typeID"])
        corpData = self.getCorporationAffiliation(notificationData["corpID"])

        self.outputData["Title"] = "{structure} Has Started Anchoring!".format(structure=structureType)
        self.outputData["Fields"]["Location"] = self.getLocationLink(notificationData["solarSystemID"], moonID=notificationData["moonID"])
        self.outputData["Fields"]["Owner"] = self.getCorpLink(corpData)

    def TowerFuelAlert(self, notificationData):

        structureType = self.getTypeName(notificationData["typeID"])
        corpData = self.getCorporationAffiliation(notificationData["corpID"])

        self.outputData["Title"] = "{structure} is Low on Fuel!".format(structure=structureType)
        self.outputData["Fields"]["Location"] = self.getLocationLink(notificationData["solarSystemID"], moonID=notificationData["moonID"])
        self.outputData["Fields"]["Owner"] = self.getCorpLink(corpData)
        self.outputData["Fields"]["Fuel Remaining"] = "```\n" + "\n".join(["{amount:,} {fuel}s".format(
            fuel=self.getTypeName(eachFuel["typeID"]),
            amount=eachFuel["quantity"]
        ) for eachFuel in notificationData["wants"]]) + "\n```"

    def TowerUnderAttack(self, notificationData):

        structureType = self.getTypeName(notificationData["typeID"])

        self.outputData["Title"] = "{structure} is Under Attack!".format(structure=structureType)
        self.outputData["Fields"]["Location"] = self.getLocationLink(notificationData["solarSystemID"], moonID=notificationData["moonID"])
        self.outputData["Fields"]["Attacker"] = self.getEntityLink(notificationData["aggressorID"])
        self.outputData["Fields"]["Health Remaining"] = "{shield:.2%} Shield | {armor:.2%} Armor | {structure:.2%} Structure".format(
            shield=notificationData["shieldValue"],
            armor=notificationData["armorValue"],
            structure=notificationData["hullValue"]
        )
import ESI
from datetime import datetime, UTC

class NotificationUtilities(object):

    def __init__(self, database_connection, access_token):

        self.ESIHandler = ESI.Handler(database_connection, access_token)

    def getTypeName(self, typeID):

        typeCall = self.ESIHandler.call("/universe/types/{type_id}/", type_id=int(typeID), retries=1)

        if typeCall["Success"]:

            return typeCall["Data"]["name"]

        else:

            return "Unknown Type {id}".format(id=typeID)

    def getSystemName(self, systemID):

        systemCall = self.ESIHandler.call("/universe/systems/{system_id}/", system_id=int(systemID), retries=1)

        if systemCall["Success"]:

            return systemCall["Data"]["name"]

        else:

            return "Unknown System {id}".format(id=systemID)

    def getConstellationLink(self, constellationID):

        constellationCall = self.ESIHandler.call("/universe/constellations/{constellation_id}/", constellation_id=int(constellationID), retries=1)

        if constellationCall["Success"]:

            if "region_id" in constellationCall["Data"]:

                regionCall = self.ESIHandler.call("/universe/regions/{region_id}/", region_id=int(constellationCall["Data"]["region_id"]), retries=1)

                if regionCall["Success"]:

                    return self.makeLink(
                        link="https://evemaps.dotlan.net/map/{region}/{constellation}".format(
                            region=regionCall["Data"]["name"].replace(" ", "_"),
                            constellation=constellationCall["Data"]["name"].replace(" ", "_")
                        ),
                        text=constellationCall["Data"]["name"]
                    )

                else:

                    return constellationCall["Data"]["name"]

            else:

                return constellationCall["Data"]["name"]

        else:

            return "Unknown Constellation {id}".format(id=constellationID)

    def getCharacterAffiliation(self, characterID):

        characterData = {
            "Success": False,
            "ID": int(characterID),
            "Name": None,
            "Corporation ID": None,
            "Corporation": None,
            "Alliance ID": None,
            "Alliance": None
        }

        affiliationCall = self.ESIHandler.call("/characters/affiliation/", characters=[characterID], retries=1)

        if affiliationCall["Success"]:

            namesToCheck = []

            affiliationData = affiliationCall["Data"][0]

            namesToCheck.append(int(affiliationData["character_id"]))

            characterData["Corporation ID"] = int(affiliationData["corporation_id"])
            namesToCheck.append(int(affiliationData["corporation_id"]))

            if "alliance_id" in affiliationData:

                characterData["Alliance ID"] = int(affiliationData["alliance_id"])
                namesToCheck.append(int(affiliationData["alliance_id"]))

            namesCall = self.ESIHandler.call("/universe/names/", ids=namesToCheck, retries=1)

            if namesCall["Success"]:

                for eachName in namesCall["Data"]:

                    if eachName["category"] == "character":
                        characterData["Name"] = eachName["name"]
                    if eachName["category"] == "corporation":
                        characterData["Corporation"] = eachName["name"]
                    if eachName["category"] == "alliance":
                        characterData["Alliance"] = eachName["name"]

                characterData["Success"] = True

        return characterData

    def getCorporationAffiliation(self, corporationID):

        corporationData = {
            "Success": False,
            "Corporation ID": int(corporationID),
            "Corporation": None,
            "Alliance ID": None,
            "Alliance": None
        }

        corporationsCall = self.ESIHandler.call("/corporations/{corporation_id}/", corporation_id=corporationID, retries=1)

        if corporationsCall["Success"]:

            corporationData["Corporation"] = corporationsCall["Data"]["name"]

            if "alliance_id" in corporationsCall["Data"]:

                corporationData["Alliance ID"] = int(corporationsCall["Data"]["alliance_id"])

                alliancesCall = self.ESIHandler.call("/alliances/{alliance_id}/", alliance_id=int(corporationsCall["Data"]["alliance_id"]), retries=1)

                if alliancesCall["Success"]:

                    corporationData["Alliance"] = alliancesCall["Data"]["name"]

                    corporationData["Success"] = True

            else:

                corporationData["Success"] = True

        return corporationData

    def getLocationLink(self, systemID, planetID = None, moonID = None):

        locationData = {
            "System": None,
            "Constellation": None,
            "Region": None,
            "Planet": None,
            "Moon": None
        }

        systemCall = self.ESIHandler.call("/universe/systems/{system_id}/", system_id=int(systemID), retries=1)

        if systemCall["Success"]:

            locationData["System"] = systemCall["Data"]["name"]

            if "constellation_id" in systemCall["Data"]:

                constellationCall = self.ESIHandler.call("/universe/constellations/{constellation_id}/", constellation_id=int(systemCall["Data"]["constellation_id"]), retries=1)

                if constellationCall["Success"]:

                    locationData["Constellation"] = constellationCall["Data"]["name"]

                    if "region_id" in constellationCall["Data"]:

                        regionCall = self.ESIHandler.call("/universe/regions/{region_id}/", region_id=int(constellationCall["Data"]["region_id"]), retries=1)

                        if regionCall["Success"]:

                            locationData["Region"] = regionCall["Data"]["name"]

                        else:

                            locationData["Region"] = "Unknown Region {id}".format(id=constellationCall["Data"]["region_id"])

                    else:

                        locationData["Region"] = "Unknown Region"

                else:

                    locationData["Constellation"] = "Unknown Constellation {id}".format(id=systemCall["Data"]["constellation_id"])
                    locationData["Region"] = "Unknown Region"

            else:

                locationData["Constellation"] = "Unknown Constellation"
                locationData["Region"] = "Unknown Region"

        else:

            locationData["System"] = "Unknown System {id}".format(id=systemID)
            locationData["Constellation"] = "Unknown Constellation"
            locationData["Region"] = "Unknown Region"

        if moonID is not None:

            moonCall = self.ESIHandler.call("/universe/moons/{moon_id}/", moon_id=int(moonID), retries=1)

            if moonCall["Success"]:

                locationData["Moon"] = moonCall["Data"]["name"]

            moonSlug = locationData["Moon"].replace(locationData["System"] + " ", "").replace("Moon ", "Moon-").replace(" ", "")

            return "{Moon} [{Region}]".format(
                Moon=self.makeLink(
                    link="https://evemaps.dotlan.net/system/{system}/{slug}".format(
                        system=locationData["System"].replace(" ", "_"),
                        slug=moonSlug
                    ),
                    text=locationData["Moon"]
                ),
                Region=self.makeLink(
                    link="https://evemaps.dotlan.net/map/{region}/{system}".format(
                        region=locationData["Region"].replace(" ", "_"),
                        system=locationData["System"].replace(" ", "_")
                    ),
                    text=locationData["Region"]
                )
            )

        elif planetID is not None:

            planetCall = self.ESIHandler.call("/universe/planets/{planet_id}/", planet_id=int(planetID), retries=1)

            if planetCall["Success"]:

                locationData["Planet"] = planetCall["Data"]["name"]

            planetSlug = locationData["Planet"].replace(locationData["System"] + " ", "")

            return "{Planet} [{Region}]".format(
                Planet=self.makeLink(
                    link="https://evemaps.dotlan.net/system/{system}/{slug}".format(
                        system=locationData["System"].replace(" ", "_"),
                        slug=planetSlug
                    ),
                    text=locationData["Planet"]
                ),
                Region=self.makeLink(
                    link="https://evemaps.dotlan.net/map/{region}/{system}".format(
                        region=locationData["Region"].replace(" ", "_"),
                        system=locationData["System"].replace(" ", "_")
                    ),
                    text=locationData["Region"]
                )
            )

        else:

            return "{System} [{Region}]".format(
                System=self.makeLink(
                    link="https://evemaps.dotlan.net/system/{system}".format(
                        system=locationData["System"].replace(" ", "_")
                    ),
                    text=locationData["System"]
                ),
                Region=self.makeLink(
                    link="https://evemaps.dotlan.net/map/{region}/{system}".format(
                        region=locationData["Region"].replace(" ", "_"),
                        system=locationData["System"].replace(" ", "_")
                    ),
                    text=locationData["Region"]
                )
            )

    def getADM(self, systemID, structureTypeID):

        sovCall = self.ESIHandler.call("/sovereignty/structures/", retries=1)

        if sovCall["Success"]:

            for eachStructure in sovCall["Data"]:

                if int(systemID) == int(eachStructure["solar_system_id"]) and int(structureTypeID) == int(eachStructure["structure_type_id"]) and "vulnerability_occupancy_level" in eachStructure:

                    return eachStructure["vulnerability_occupancy_level"]

        return 0

    def getStructure(self, structureID, structureTypeID, systemID, fallBackName = None):

        #Sovereignty Structures
        if int(structureTypeID) in [32458, 32226]:

            systemCall = self.ESIHandler.call("/universe/systems/{system_id}/", system_id=int(systemID), retries=1)

            if systemCall["Success"]:

                systemName = systemCall["Data"]["name"]

            else:

                systemName = "Unknown System {id}".format(id=systemID)

            typeCall = self.ESIHandler.call("/universe/types/{type_id}/", type_id=int(structureTypeID), retries=1)

            if typeCall["Success"]:

                typeName = typeCall["Data"]["name"]

            else:

                typeName = "Unknown Type {id}".format(id=structureTypeID)

            return systemName + " " + typeName

        #Customs Offices / Gantries
        elif int(structureTypeID) in [2233, 3962]:

            return fallBackName
        
        #Skyhooks
        elif int(structureTypeID) == 81080:

            return "An Orbital Skyhook"

        #Upwell Structures
        else:

            typeCall = self.ESIHandler.call("/universe/types/{type_id}/", type_id=int(structureTypeID), retries=1)

            if typeCall["Success"]:

                typeName = typeCall["Data"]["name"]

            else:

                typeName = "Unknown Type {id}".format(id=structureTypeID)

            structureCall = self.ESIHandler.call("/universe/structures/{structure_id}/", structure_id=int(structureID), retries=1)

            if structureCall["Success"]:

                structureName = structureCall["Data"]["name"]
                self.verified_owner = structureCall["Data"]["owner_id"]

            else:

                structureName = "Unknown Structure {id}".format(id=structureID)

            return "{name} ({type})".format(
                name=structureName,
                type=typeName
            )

    def getCorpLink(self, corpData):

        if corpData["Alliance ID"] is not None:

            return "{Corp} [{Alliance}]".format(
                Corp=self.makeLink(
                    link="https://zkillboard.com/corporation/{id}/".format(id=corpData["Corporation ID"]),
                    text=corpData["Corporation"]
                ),
                Alliance=self.makeLink(
                    link="https://evemaps.dotlan.net/alliance/{id}".format(id=corpData["Alliance ID"]),
                    text=corpData["Alliance"]
                )
            )

        else:

            return "{Corp}".format(
                Corp=self.makeLink(
                    link="https://zkillboard.com/corporation/{id}/".format(id=corpData["Corporation ID"]),
                    text=corpData["Corporation"]
                )
            )

    def getCharacterLink(self, playerData):

        if playerData["Alliance ID"] is not None:

            return "{Character} ({Corp}) [{Alliance}]".format(
                Character=self.makeLink(
                    link="https://zkillboard.com/character/{id}/".format(id=playerData["ID"]),
                    text=playerData["Name"]
                ),
                Corp=self.makeLink(
                    link="https://evemaps.dotlan.net/corp/{id}".format(id=playerData["Corporation ID"]),
                    text=playerData["Corporation"]
                ),
                Alliance=self.makeLink(
                    link="https://evemaps.dotlan.net/alliance/{id}".format(id=playerData["Alliance ID"]),
                    text=playerData["Alliance"]
                )
            )

        else:

            return "{Character} ({Corp})".format(
                Character=self.makeLink(
                    link="https://zkillboard.com/character/{id}/".format(id=playerData["ID"]),
                    text=playerData["Name"]
                ),
                Corp=self.makeLink(
                    link="https://evemaps.dotlan.net/corp/{id}".format(id=playerData["Corporation ID"]),
                    text=playerData["Corporation"]
                )
            )

    def getEntityLink(self, entityID):

        if entityID is None or int(entityID) == 0:

            return "Unknown Character"

        #NPC Corporation Entities
        elif 1000000 <= int(entityID) <= 2000000:

            corpData = self.getCorporationAffiliation(entityID)
            return self.getCorpLink(corpData)

        #Players
        else:

            playerData = self.getCharacterAffiliation(entityID)
            return self.getCharacterLink(playerData)

    def makeLink(self, link, text):

        if self.platform == "Slack":

            return "<{link}|{text}>".format(link=link, text=text)

        elif self.platform == "Discord":

            return "[{text}]({link})".format(text=text, link=link)

        else:

            return ""

    def parseTimestamp(self, timestamp):

        #Eve notifications use both LDAP and Unix timestamps, we need to be able to work with both.
        if len(str(timestamp)) >= 17:

            unixTime = int((int(timestamp) / 10000000) - 11644473600)

        else:

            unixTime = int(timestamp)

        return datetime.fromtimestamp(unixTime, UTC).strftime("%d %B, %Y - %H:%M:%S EVE")

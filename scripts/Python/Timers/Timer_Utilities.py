import ESI
from datetime import datetime, UTC

class TimerUtilities(object):

    def __init__(self, database_connection, access_token):

        self.ESIHandler = ESI.Handler(database_connection, access_token)

    def getCorporationAffiliation(self, corporationID):

        corporationData = {
            "Corporation ID": int(corporationID),
            "Corporation": None,
            "Corporation Ticker": None,
            "Alliance ID": None,
            "Alliance": None,
            "Alliance Ticker": None
        }

        corporationsCall = self.ESIHandler.call("/corporations/{corporation_id}/", corporation_id=corporationID, retries=1)

        if corporationsCall["Success"]:

            corporationData["Corporation"] = corporationsCall["Data"]["name"]
            corporationData["Corporation Ticker"] = corporationsCall["Data"]["ticker"]

            if "alliance_id" in corporationsCall["Data"]:

                corporationData["Alliance ID"] = int(corporationsCall["Data"]["alliance_id"])

                alliancesCall = self.ESIHandler.call("/alliances/{alliance_id}/", alliance_id=int(corporationsCall["Data"]["alliance_id"]), retries=1)

                if alliancesCall["Success"]:

                    corporationData["Alliance"] = alliancesCall["Data"]["name"]
                    corporationData["Alliance Ticker"] = alliancesCall["Data"]["ticker"]

                else:

                    raise RuntimeError("Alliance Call Failed")

        else:

            raise RuntimeError("Corporation Call Failed")

        return corporationData

    def getLocation(self, systemID, planetID = None):

        locationData = {
            "System": None,
            "Constellation": None,
            "Region": None,
            "Planet": None,
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

                            raise RuntimeError("Region Call Failed")

                    else:

                        raise RuntimeError("Region Not in Constellation")

                else:

                    raise RuntimeError("Constellation Call Failed")

            else:

                raise RuntimeError("Constellation Not in System")

        else:

            raise RuntimeError("System Call Failed")

        if planetID is not None:

            planetCall = self.ESIHandler.call("/universe/planets/{planet_id}/", planet_id=int(planetID), retries=1)

            if planetCall["Success"]:

                locationData["Planet"] = planetCall["Data"]["name"].replace(locationData["System"] + " ", "")

            else:

                raise RuntimeError("Planet Call Failed")

        return locationData

    def getStructureName(self, structureID, structureTypeID, systemID, planetID = None):

        #Customs Offices / Gantries / Skyhooks
        if int(structureTypeID) in [2233, 3962, 81080]:

            locationData = self.getLocation(systemID, planetID)

            return "{system} {planet}".format(
                system=locationData["System"],
                planet=locationData["Planet"],
            )

        #Upwell Structures
        else:

            structureCall = self.ESIHandler.call("/universe/structures/{structure_id}/", structure_id=int(structureID), retries=1)

            if structureCall["Success"]:

                structureName = structureCall["Data"]["name"]
                self.verified_owner = structureCall["Data"]["owner_id"]

            else:

                raise RuntimeError("Structure Call Failed")

            return "{name}".format(
                name=structureName
            )

    def parseTimestamp(self, timestamp):

        #Eve notifications use both LDAP and Unix timestamps, we need to be able to work with both.
        if len(str(timestamp)) >= 17:

            unixTime = int((int(timestamp) / 10000000) - 11644473600)

        else:

            unixTime = int(timestamp)

        self.timer = unixTime
        return (datetime.fromtimestamp(unixTime, UTC).isoformat(timespec="seconds").split("+")[0] + "Z")

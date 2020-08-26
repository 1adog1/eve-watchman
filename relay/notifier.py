import ESI
import requests
import json
import time

from datetime import datetime

def StructureDestroyed(timestamp, fulldetails, typeidlist, geographicinformation, bolders, pinger, accessToken):
    
    structuredetails = ESI.getStructureDetails(fulldetails["structureID"], accessToken)
    structureType = typeidlist[str(structuredetails["type_id"])]
    structureName = structuredetails["name"]
    ownerDetails = ESI.getCorpData(structuredetails["owner_id"])
    ownerName = ownerDetails["name"]
    systemName = ESI.getSystemName(geographicinformation, str(fulldetails["solarsystemID"]))
    regionName = ESI.getRegionName(geographicinformation, str(fulldetails["solarsystemID"]))

    notifyingMessage = (pinger + " Citadel Destroyed - [" + timestamp + "]\n" + bolders + structureName + " (" + structureType + ") Has Been Destroyed!" + bolders + "\nLocation: " + getLink(systemName, ("http://evemaps.dotlan.net/system/" + systemName.replace(" ","_")), bolders) + " [" + getLink(regionName, ("http://evemaps.dotlan.net/map/" + regionName.replace(" ","_") + "/" + systemName.replace(" ","_")), bolders) + "]\nOwner: " + getLink(ownerName, ("http://evemaps.dotlan.net/corp/" + ownerName.replace(" ","_")), bolders))

    return notifyingMessage

def StructureLostArmor(timestamp, fulldetails, typeidlist, geographicinformation, bolders, pinger, accessToken):

    structuredetails = ESI.getStructureDetails(fulldetails["structureID"], accessToken)
    structureType = typeidlist[str(structuredetails["type_id"])]
    structureName = structuredetails["name"]
    ownerDetails = ESI.getCorpData(structuredetails["owner_id"])
    ownerName = ownerDetails["name"]
    systemName = ESI.getSystemName(geographicinformation, str(fulldetails["solarsystemID"]))
    regionName = ESI.getRegionName(geographicinformation, str(fulldetails["solarsystemID"]))

    notifyingMessage = (pinger + " Citadel Lost Armor - [" + timestamp + "]\n" + bolders + structureName + " (" + structureType + ") Has Lost Armor!" + bolders + "\nLocation: " + getLink(systemName, ("http://evemaps.dotlan.net/system/" + systemName.replace(" ","_")), bolders) + " [" + getLink(regionName, ("http://evemaps.dotlan.net/map/" + regionName.replace(" ","_") + "/" + systemName.replace(" ","_")), bolders) + "]\nOwner: " + getLink(ownerName, ("http://evemaps.dotlan.net/corp/" + ownerName.replace(" ","_")), bolders))

    if "timestamp" in fulldetails:
        notifyingMessage += "\nVulnerable At: " + getRealTime(fulldetails["timestamp"])

    return notifyingMessage

def StructureLostShields(timestamp, fulldetails, typeidlist, geographicinformation, bolders, pinger, accessToken):

    structuredetails = ESI.getStructureDetails(fulldetails["structureID"], accessToken)
    structureType = typeidlist[str(structuredetails["type_id"])]
    structureName = structuredetails["name"]
    ownerDetails = ESI.getCorpData(structuredetails["owner_id"])
    ownerName = ownerDetails["name"]
    systemName = ESI.getSystemName(geographicinformation, str(fulldetails["solarsystemID"]))
    regionName = ESI.getRegionName(geographicinformation, str(fulldetails["solarsystemID"]))

    notifyingMessage = (pinger + " Citadel Lost Shields - [" + timestamp + "]\n" + bolders + structureName + " (" + structureType + ") Has Lost Shields!" + bolders + "\nLocation: " + getLink(systemName, ("http://evemaps.dotlan.net/system/" + systemName.replace(" ","_")), bolders) + " [" + getLink(regionName, ("http://evemaps.dotlan.net/map/" + regionName.replace(" ","_") + "/" + systemName.replace(" ","_")), bolders) + "]\nOwner: " + getLink(ownerName, ("http://evemaps.dotlan.net/corp/" + ownerName.replace(" ","_")), bolders))
    
    if "timestamp" in fulldetails:
        notifyingMessage += "\nVulnerable At: " + getRealTime(fulldetails["timestamp"])

    return notifyingMessage

def StructureUnderAttack(timestamp, fulldetails, typeidlist, geographicinformation, bolders, pinger, accessToken):

    structuredetails = ESI.getStructureDetails(fulldetails["structureID"], accessToken)
    structureType = typeidlist[str(structuredetails["type_id"])]
    structureName = structuredetails["name"]
    ownerDetails = ESI.getCorpData(structuredetails["owner_id"])
    ownerName = ownerDetails["name"]
    systemName = ESI.getSystemName(geographicinformation, str(fulldetails["solarsystemID"]))
    regionName = ESI.getRegionName(geographicinformation, str(fulldetails["solarsystemID"]))
    attackerString = ESI.getFullCharacterLink(fulldetails["charID"], bolders)

    notifyingMessage = (pinger + " Citadel Under Attack - [" + timestamp + "]\n" + bolders + structureName + " (" + structureType + ") Is Under Attack!" + bolders + "\nLocation: " + getLink(systemName, ("http://evemaps.dotlan.net/system/" + systemName.replace(" ","_")), bolders) + " [" + getLink(regionName, ("http://evemaps.dotlan.net/map/" + regionName.replace(" ","_") + "/" + systemName.replace(" ","_")), bolders) + "]\nOwner: " + getLink(ownerName, ("http://evemaps.dotlan.net/corp/" + ownerName.replace(" ","_")), bolders) + "\nAttacker: " + attackerString + "\nHealth: " + str(round(float(fulldetails["shieldPercentage"]), 2)) + "% Shield / " + str(round(float(fulldetails["armorPercentage"]), 2)) + "% Armor / " + str(round(float(fulldetails["hullPercentage"]), 2)) + "% Structure")

    return notifyingMessage

def MoonminingAutomaticFracture(timestamp, fulldetails, typeidlist, geographicinformation, bolders, pinger, accessToken):

    structuredetails = ESI.getStructureDetails(fulldetails["structureID"], accessToken)
    structureType = typeidlist[str(structuredetails["type_id"])]
    structureName = structuredetails["name"]
    ownerDetails = ESI.getCorpData(structuredetails["owner_id"])
    ownerName = ownerDetails["name"]
    systemName = ESI.getSystemName(geographicinformation, str(fulldetails["solarSystemID"]))
    regionName = ESI.getRegionName(geographicinformation, str(fulldetails["solarSystemID"]))
    moonDetails = ESI.getMoonDetails(fulldetails["moonID"])
    moonName = moonDetails["name"]
    
    notifyingMessage = (pinger + " Moon Auto-Fracture - [" + timestamp + "]\n" + bolders + structureName + " (" + structureType + ") Has Automatically Detonated!" + bolders + "\nLocation: " + getLink(systemName, ("http://evemaps.dotlan.net/system/" + systemName.replace(" ","_")), bolders) + " (" + moonName.replace(systemName, "Planet") + ") [" + getLink(regionName, ("http://evemaps.dotlan.net/map/" + regionName.replace(" ","_") + "/" + systemName.replace(" ","_")), bolders) + "]\nOwner: " + getLink(ownerName, ("http://evemaps.dotlan.net/corp/" + ownerName.replace(" ","_")), bolders) + "\nOre Available: ```\n")
    
    for ores in fulldetails["oreVolumeByType"]:
        notifyingMessage += (typeidlist[str(ores)] + ": " + "{:,}".format(int(fulldetails["oreVolumeByType"][ores])) + " m3\n")
    
    notifyingMessage += "```"

    return notifyingMessage

def MoonminingExtractionCancelled(timestamp, fulldetails, typeidlist, geographicinformation, bolders, pinger, accessToken):

    structuredetails = ESI.getStructureDetails(fulldetails["structureID"], accessToken)
    structureType = typeidlist[str(structuredetails["type_id"])]
    structureName = structuredetails["name"]
    ownerDetails = ESI.getCorpData(structuredetails["owner_id"])
    ownerName = ownerDetails["name"]
    systemName = ESI.getSystemName(geographicinformation, str(fulldetails["solarSystemID"]))
    regionName = ESI.getRegionName(geographicinformation, str(fulldetails["solarSystemID"]))
    if fulldetails["cancelledBy"] == None:
        cancellerString = "Unknown"
    else:
        cancellerString = ESI.getFullCharacterLink(fulldetails["cancelledBy"], bolders)
        
    moonDetails = ESI.getMoonDetails(fulldetails["moonID"])
    moonName = moonDetails["name"]
    
    notifyingMessage = (pinger + " Moon Cancelled Extraction - [" + timestamp + "]\n" + bolders + structureName + " (" + structureType + ") Has Had Its Extraction Cancelled!" + bolders + "\nLocation: " + getLink(systemName, ("http://evemaps.dotlan.net/system/" + systemName.replace(" ","_")), bolders) + " (" + moonName.replace(systemName, "Planet") + ") [" + getLink(regionName, ("http://evemaps.dotlan.net/map/" + regionName.replace(" ","_") + "/" + systemName.replace(" ","_")), bolders) + "]\nOwner: " + getLink(ownerName, ("http://evemaps.dotlan.net/corp/" + ownerName.replace(" ","_")), bolders) + "\nCancelled By: " + cancellerString)

    return notifyingMessage

def MoonminingExtractionFinished(timestamp, fulldetails, typeidlist, geographicinformation, bolders, pinger, accessToken):

    structuredetails = ESI.getStructureDetails(fulldetails["structureID"], accessToken)
    structureType = typeidlist[str(structuredetails["type_id"])]
    structureName = structuredetails["name"]
    ownerDetails = ESI.getCorpData(structuredetails["owner_id"])
    ownerName = ownerDetails["name"]
    systemName = ESI.getSystemName(geographicinformation, str(fulldetails["solarSystemID"]))
    regionName = ESI.getRegionName(geographicinformation, str(fulldetails["solarSystemID"]))
    moonDetails = ESI.getMoonDetails(fulldetails["moonID"])
    moonName = moonDetails["name"]
    
    notifyingMessage = (pinger + " Moon Finished Extraction - [" + timestamp + "]\n" + bolders + structureName + " (" + structureType + ") Has Finished Its Extraction!" + bolders + "\nLocation: " + getLink(systemName, ("http://evemaps.dotlan.net/system/" + systemName.replace(" ","_")), bolders) + " (" + moonName.replace(systemName, "Planet") + ") [" + getLink(regionName, ("http://evemaps.dotlan.net/map/" + regionName.replace(" ","_") + "/" + systemName.replace(" ","_")), bolders) + "]\nOwner: " + getLink(ownerName, ("http://evemaps.dotlan.net/corp/" + ownerName.replace(" ","_")), bolders) + "\nOre Available: ```\n")
    
    for ores in fulldetails["oreVolumeByType"]:
        notifyingMessage += (typeidlist[str(ores)] + ": " + "{:,}".format(int(fulldetails["oreVolumeByType"][ores])) + " m3\n")
    
    notifyingMessage += "```"

    return notifyingMessage

def MoonminingExtractionStarted(timestamp, fulldetails, typeidlist, geographicinformation, bolders, pinger, accessToken):

    structuredetails = ESI.getStructureDetails(fulldetails["structureID"], accessToken)
    structureType = typeidlist[str(structuredetails["type_id"])]
    structureName = structuredetails["name"]
    ownerDetails = ESI.getCorpData(structuredetails["owner_id"])
    ownerName = ownerDetails["name"]
    systemName = ESI.getSystemName(geographicinformation, str(fulldetails["solarSystemID"]))
    regionName = ESI.getRegionName(geographicinformation, str(fulldetails["solarSystemID"]))
    starterString = ESI.getFullCharacterLink(fulldetails["startedBy"], bolders)
    moonDetails = ESI.getMoonDetails(fulldetails["moonID"])
    moonName = moonDetails["name"]
    
    notifyingMessage = (pinger + " Moon Began Extraction - [" + timestamp + "]\n" + bolders + structureName + " (" + structureType + ") Has Started A New Extraction!" + bolders + "\nLocation: " + getLink(systemName, ("http://evemaps.dotlan.net/system/" + systemName.replace(" ","_")), bolders) + " (" + moonName.replace(systemName, "Planet") + ") [" + getLink(regionName, ("http://evemaps.dotlan.net/map/" + regionName.replace(" ","_") + "/" + systemName.replace(" ","_")), bolders) + "]\nOwner: " + getLink(ownerName, ("http://evemaps.dotlan.net/corp/" + ownerName.replace(" ","_")), bolders) + "\nStarted By: " + starterString + "\nReady At: " + getRealTime(fulldetails["readyTime"]))

    return notifyingMessage

def MoonminingLaserFired(timestamp, fulldetails, typeidlist, geographicinformation, bolders, pinger, accessToken):

    structuredetails = ESI.getStructureDetails(fulldetails["structureID"], accessToken)
    structureType = typeidlist[str(structuredetails["type_id"])]
    structureName = structuredetails["name"]
    ownerDetails = ESI.getCorpData(structuredetails["owner_id"])
    ownerName = ownerDetails["name"]
    systemName = ESI.getSystemName(geographicinformation, str(fulldetails["solarSystemID"]))
    regionName = ESI.getRegionName(geographicinformation, str(fulldetails["solarSystemID"]))
    moonDetails = ESI.getMoonDetails(fulldetails["moonID"])
    moonName = moonDetails["name"]
    fireString = ESI.getFullCharacterLink(fulldetails["firedBy"], bolders)
    
    notifyingMessage = (pinger + " Moon Manual Fracture - [" + timestamp + "]\n" + bolders + structureName + " (" + structureType + ") Has Been Manually Detonated!" + bolders + "\nLocation: " + getLink(systemName, ("http://evemaps.dotlan.net/system/" + systemName.replace(" ","_")), bolders) + " (" + moonName.replace(systemName, "Planet") + ") [" + getLink(regionName, ("http://evemaps.dotlan.net/map/" + regionName.replace(" ","_") + "/" + systemName.replace(" ","_")), bolders) + "]\nOwner: " + getLink(ownerName, ("http://evemaps.dotlan.net/corp/" + ownerName.replace(" ","_")), bolders) + "\nDetonated By: " + fireString + "\nOre Available: ```\n")
    
    for ores in fulldetails["oreVolumeByType"]:
        notifyingMessage += (typeidlist[str(ores)] + ": " + "{:,}".format(int(fulldetails["oreVolumeByType"][ores])) + " m3\n")
    
    notifyingMessage += "```"

    return notifyingMessage

def StructureAnchoring(timestamp, fulldetails, typeidlist, geographicinformation, bolders, pinger, accessToken):
    from datetime import timezone, datetime

    structuredetails = ESI.getStructureDetails(fulldetails["structureID"], accessToken)
    structureType = typeidlist[str(structuredetails["type_id"])]
    structureName = structuredetails["name"]
    ownerDetails = ESI.getCorpData(structuredetails["owner_id"])
    ownerName = ownerDetails["name"]
    systemName = ESI.getSystemName(geographicinformation, str(fulldetails["solarsystemID"]))
    regionName = ESI.getRegionName(geographicinformation, str(fulldetails["solarsystemID"]))
    AnchoredAt = datetime.strptime(timestamp, "%d %B, %Y - %H:%M:%S EVE")
    currentTime = (int(AnchoredAt.replace(tzinfo=timezone.utc).timestamp()) + 11644473600) * 10000000
    vulnerableTime = currentTime + fulldetails["timeLeft"]

    notifyingMessage = (pinger + " Citadel Anchoring - [" + timestamp + "]\n" + bolders + structureName + " (" + structureType + ") Has Begun Anchoring!" + bolders + "\nLocation: " + getLink(systemName, ("http://evemaps.dotlan.net/system/" + systemName.replace(" ","_")), bolders) + " [" + getLink(regionName, ("http://evemaps.dotlan.net/map/" + regionName.replace(" ","_") + "/" + systemName.replace(" ","_")), bolders) + "]\nOwner: " + getLink(ownerName, ("http://evemaps.dotlan.net/corp/" + ownerName.replace(" ","_")), bolders) + "\nAnchoring Completes: " + getRealTime(vulnerableTime))

    return notifyingMessage

def StructureFuelAlert(timestamp, fulldetails, typeidlist, geographicinformation, bolders, pinger, accessToken):
    
    structuredetails = ESI.getStructureDetails(fulldetails["structureID"], accessToken)
    structureType = typeidlist[str(structuredetails["type_id"])]
    structureName = structuredetails["name"]
    ownerDetails = ESI.getCorpData(structuredetails["owner_id"])
    ownerName = ownerDetails["name"]
    systemName = ESI.getSystemName(geographicinformation, str(fulldetails["solarsystemID"]))
    regionName = ESI.getRegionName(geographicinformation, str(fulldetails["solarsystemID"]))

    notifyingMessage = (pinger + " Citadel Low On Fuel - [" + timestamp + "]\n" + bolders + structureName + " (" + structureType + ") Is Low On Fuel!" + bolders + "\nLocation: " + getLink(systemName, ("http://evemaps.dotlan.net/system/" + systemName.replace(" ","_")), bolders) + " [" + getLink(regionName, ("http://evemaps.dotlan.net/map/" + regionName.replace(" ","_") + "/" + systemName.replace(" ","_")), bolders) + "]\nOwner: " + getLink(ownerName, ("http://evemaps.dotlan.net/corp/" + ownerName.replace(" ","_")), bolders) + "\nFuel Remaining: \n```\n")
    
    for fuels in fulldetails["listOfTypesAndQty"]:
        notifyingMessage += (typeidlist[str(fuels[1])] + ": " + "{:,}".format(int(fuels[0])) + " Units\n")
        
    notifyingMessage += "```"

    return notifyingMessage

def StructureOnline(timestamp, fulldetails, typeidlist, geographicinformation, bolders, pinger, accessToken):
    
    structuredetails = ESI.getStructureDetails(fulldetails["structureID"], accessToken)
    structureType = typeidlist[str(structuredetails["type_id"])]
    structureName = structuredetails["name"]
    ownerDetails = ESI.getCorpData(structuredetails["owner_id"])
    ownerName = ownerDetails["name"]
    systemName = ESI.getSystemName(geographicinformation, str(fulldetails["solarsystemID"]))
    regionName = ESI.getRegionName(geographicinformation, str(fulldetails["solarsystemID"]))

    notifyingMessage = (pinger + " Citadel Online - [" + timestamp + "]\n" + bolders + structureName + " (" + structureType + ") Is Online!" + bolders + "\nLocation: " + getLink(systemName, ("http://evemaps.dotlan.net/system/" + systemName.replace(" ","_")), bolders) + " [" + getLink(regionName, ("http://evemaps.dotlan.net/map/" + regionName.replace(" ","_") + "/" + systemName.replace(" ","_")), bolders) + "]\nOwner: " + getLink(ownerName, ("http://evemaps.dotlan.net/corp/" + ownerName.replace(" ","_")), bolders))

    return notifyingMessage

def StructureUnanchoring(timestamp, fulldetails, typeidlist, geographicinformation, bolders, pinger, accessToken):
    from datetime import timezone, datetime

    structuredetails = ESI.getStructureDetails(fulldetails["structureID"], accessToken)
    structureType = typeidlist[str(structuredetails["type_id"])]
    structureName = structuredetails["name"]
    ownerDetails = ESI.getCorpData(structuredetails["owner_id"])
    ownerName = ownerDetails["name"]
    systemName = ESI.getSystemName(geographicinformation, str(fulldetails["solarsystemID"]))
    regionName = ESI.getRegionName(geographicinformation, str(fulldetails["solarsystemID"]))
    AnchoredAt = datetime.strptime(timestamp, "%d %B, %Y - %H:%M:%S EVE")
    currentTime = (int(AnchoredAt.replace(tzinfo=timezone.utc).timestamp()) + 11644473600) * 10000000
    vulnerableTime = currentTime + fulldetails["timeLeft"]

    notifyingMessage = (pinger + " Citadel Unanchoring - [" + timestamp + "]\n" + bolders + structureName + " (" + structureType + ") Is Unanchoring!" + bolders + "\nLocation: " + getLink(systemName, ("http://evemaps.dotlan.net/system/" + systemName.replace(" ","_")), bolders) + " [" + getLink(regionName, ("http://evemaps.dotlan.net/map/" + regionName.replace(" ","_") + "/" + systemName.replace(" ","_")), bolders) + "]\nOwner: " + getLink(ownerName, ("http://evemaps.dotlan.net/corp/" + ownerName.replace(" ","_")), bolders) + "\nUnanchored At: " + getRealTime(vulnerableTime))

    return notifyingMessage

def StructureServicesOffline(timestamp, fulldetails, typeidlist, geographicinformation, bolders, pinger, accessToken):
    
    structuredetails = ESI.getStructureDetails(fulldetails["structureID"], accessToken)
    structureType = typeidlist[str(structuredetails["type_id"])]
    structureName = structuredetails["name"]
    ownerDetails = ESI.getCorpData(structuredetails["owner_id"])
    ownerName = ownerDetails["name"]
    systemName = ESI.getSystemName(geographicinformation, str(fulldetails["solarsystemID"]))
    regionName = ESI.getRegionName(geographicinformation, str(fulldetails["solarsystemID"]))

    notifyingMessage = (pinger + " Citadel Services Offline - [" + timestamp + "]\n" + bolders + structureName + " (" + structureType + ") Has Services That Have Gone Offline!" + bolders + "\nLocation: " + getLink(systemName, ("http://evemaps.dotlan.net/system/" + systemName.replace(" ","_")), bolders) + " [" + getLink(regionName, ("http://evemaps.dotlan.net/map/" + regionName.replace(" ","_") + "/" + systemName.replace(" ","_")), bolders) + "]\nOwner: " + getLink(ownerName, ("http://evemaps.dotlan.net/corp/" + ownerName.replace(" ","_")), bolders) + "\nOffline Services: \n```\n")
    
    for services in fulldetails["listOfServiceModuleIDs"]:
        notifyingMessage += (typeidlist[str(services)] + "\n")
        
    notifyingMessage += "```"

    return notifyingMessage

def StructureWentHighPower(timestamp, fulldetails, typeidlist, geographicinformation, bolders, pinger, accessToken):
    
    structuredetails = ESI.getStructureDetails(fulldetails["structureID"], accessToken)
    structureType = typeidlist[str(structuredetails["type_id"])]
    structureName = structuredetails["name"]
    ownerDetails = ESI.getCorpData(structuredetails["owner_id"])
    ownerName = ownerDetails["name"]
    systemName = ESI.getSystemName(geographicinformation, str(fulldetails["solarsystemID"]))
    regionName = ESI.getRegionName(geographicinformation, str(fulldetails["solarsystemID"]))

    notifyingMessage = (pinger + " Citadel High Power - [" + timestamp + "]\n" + bolders + structureName + " (" + structureType + ") Has Entered High Power!" + bolders + "\nLocation: " + getLink(systemName, ("http://evemaps.dotlan.net/system/" + systemName.replace(" ","_")), bolders) + " [" + getLink(regionName, ("http://evemaps.dotlan.net/map/" + regionName.replace(" ","_") + "/" + systemName.replace(" ","_")), bolders) + "]\nOwner: " + getLink(ownerName, ("http://evemaps.dotlan.net/corp/" + ownerName.replace(" ","_")), bolders))

    return notifyingMessage

def StructureWentLowPower(timestamp, fulldetails, typeidlist, geographicinformation, bolders, pinger, accessToken):
    
    structuredetails = ESI.getStructureDetails(fulldetails["structureID"], accessToken)
    structureType = typeidlist[str(structuredetails["type_id"])]
    structureName = structuredetails["name"]
    ownerDetails = ESI.getCorpData(structuredetails["owner_id"])
    ownerName = ownerDetails["name"]
    systemName = ESI.getSystemName(geographicinformation, str(fulldetails["solarsystemID"]))
    regionName = ESI.getRegionName(geographicinformation, str(fulldetails["solarsystemID"]))

    notifyingMessage = (pinger + " Citadel Low Power - [" + timestamp + "]\n" + bolders + structureName + " (" + structureType + ") Has Entered Low Power!" + bolders + "\nLocation: " + getLink(systemName, ("http://evemaps.dotlan.net/system/" + systemName.replace(" ","_")), bolders) + " [" + getLink(regionName, ("http://evemaps.dotlan.net/map/" + regionName.replace(" ","_") + "/" + systemName.replace(" ","_")), bolders) + "]\nOwner: " + getLink(ownerName, ("http://evemaps.dotlan.net/corp/" + ownerName.replace(" ","_")), bolders))

    return notifyingMessage

def StructuresReinforcementChanged(timestamp, fulldetails, typeidlist, geographicinformation, bolders, pinger, accessToken):
        
    notifyingMessage = (pinger + " Citadel Reinforcement Change - [" + timestamp + "]\n" + bolders + "A Set of Structures Have Had Their Reinforcement Cycles Changed!" + bolders + "\nNew Reinforcement Hour: " + 
    "{:02}".format(fulldetails["hour"]) + ":00\nOverview of Affected Structures: \n```\n")
    
    structureDict = {}
    
    for eachStructure in fulldetails["allStructureInfo"]:
    
        if typeidlist[str(eachStructure[2])] not in structureDict:
            structureDict[typeidlist[str(eachStructure[2])]] = 0
            
        structureDict[typeidlist[str(eachStructure[2])]] += 1
        
    for eachType in structureDict:
        notifyingMessage += (eachType + ": " + str(structureDict[eachType]) + "\n")
        
    notifyingMessage += ("Total Affected Structures: " + str(fulldetails["numStructures"]) + "\n```")

    return notifyingMessage

def OwnershipTransferred(timestamp, fulldetails, typeidlist, geographicinformation, bolders, pinger, accessToken):
    
    systemName = ESI.getSystemName(geographicinformation, str(fulldetails["solarSystemID"]))
    regionName = ESI.getRegionName(geographicinformation, str(fulldetails["solarSystemID"]))
    transfererString = ESI.getFullCharacterLink(fulldetails["charID"], bolders)

    corpDetails = ESI.getCorpData(fulldetails["oldOwnerCorpID"])
    corpName = corpDetails["name"]
    
    if "alliance_id" in corpDetails:
        allianceDetails = ESI.getAllianceData(corpDetails["alliance_id"])
        allianceName = allianceDetails["name"]
        
        oldOwnerString = (getLink(corpName, ("http://evemaps.dotlan.net/corp/" + str(fulldetails["oldOwnerCorpID"])), bolders) + " [" + getLink(allianceName, ("http://evemaps.dotlan.net/alliance/" + str(corpDetails["alliance_id"])), bolders) + "]")
    
    else:
        oldOwnerString = (getLink(corpName, ("http://evemaps.dotlan.net/corp/" + str(fulldetails["oldOwnerCorpID"])), bolders))
        
    corpDetails = ESI.getCorpData(fulldetails["newOwnerCorpID"])
    corpName = corpDetails["name"]
    
    if "alliance_id" in corpDetails:
        allianceDetails = ESI.getAllianceData(corpDetails["alliance_id"])
        allianceName = allianceDetails["name"]
        
        newOwnerString = (getLink(corpName, ("http://evemaps.dotlan.net/corp/" + str(fulldetails["newOwnerCorpID"])), bolders) + " [" + getLink(allianceName, ("http://evemaps.dotlan.net/alliance/" + str(corpDetails["alliance_id"])), bolders) + "]")
    
    else:
        newOwnerString = (getLink(corpName, ("http://evemaps.dotlan.net/corp/" + str(fulldetails["newOwnerCorpID"])), bolders))

    
    if fulldetails["oldOwnerCorpID"] == 1000132:
        structureType = typeidlist[str(fulldetails["structureTypeID"])]
        structureName = ("A Sovereignty Structure in " + systemName)
    
    else:
        structuredetails = ESI.getStructureDetails(fulldetails["structureID"], accessToken)
        structureType = typeidlist[str(structuredetails["type_id"])]
        structureName = structuredetails["name"]
        
    notifyingMessage = (pinger + " Structure Ownership Transfer - [" + timestamp + "]\n" + bolders + structureName + " (" + structureType + ") Has Changed Ownership!" + bolders + "\nLocation: " + getLink(systemName, ("http://evemaps.dotlan.net/system/" + systemName.replace(" ","_")), bolders) + " [" + getLink(regionName, ("http://evemaps.dotlan.net/map/" + regionName.replace(" ","_") + "/" + systemName.replace(" ","_")), bolders) + "]\nOld Owner: " + oldOwnerString + "\nNew Owner: " + newOwnerString + "\nTransferer: " + transfererString)

    return notifyingMessage

def EntosisCaptureStarted(timestamp, fulldetails, typeidlist, geographicinformation, bolders, pinger, accessToken):

    structureType = typeidlist[str(fulldetails["structureTypeID"])]
    systemName = ESI.getSystemName(geographicinformation, str(fulldetails["solarSystemID"]))
    regionName = ESI.getRegionName(geographicinformation, str(fulldetails["solarSystemID"]))
    
    admLevel = ESI.getADM(fulldetails["structureTypeID"], fulldetails["solarSystemID"])

    notifyingMessage = (pinger + " System Being Captured - [" + timestamp + "]\n" + bolders + "The " + structureType + " In " + systemName + " Is Being Captured!" + bolders + "\nLocation: " + getLink(systemName, ("http://evemaps.dotlan.net/system/" + systemName.replace(" ","_")), bolders) + " [" + getLink(regionName, ("http://evemaps.dotlan.net/map/" + regionName.replace(" ","_") + "/" + systemName.replace(" ","_")), bolders) + "]\nADM: " + str(round(admLevel, 1)))

    return notifyingMessage

def SovCommandNodeEventStarted(timestamp, fulldetails, typeidlist, geographicinformation, bolders, pinger, accessToken):
    
    structureTypes = {"1" : "Territorial Claim Unit", "2" : "Infrastructure Hub"}

    structureType = structureTypes[str(fulldetails["campaignEventType"])]
    systemName = ESI.getSystemName(geographicinformation, str(fulldetails["solarSystemID"]))
    regionName = ESI.getRegionName(geographicinformation, str(fulldetails["solarSystemID"]))
    constellationDetails = ESI.getConstellationDetails(fulldetails["constellationID"])
    constellationName = constellationDetails["name"]

    notifyingMessage = (pinger + " Command Node Event Started - [" + timestamp + "]\n" + bolders + "Command Nodes Have Decloaked For The " + structureType + " In " + systemName + "!" + bolders + "\nLocation: " + getLink(systemName, ("http://evemaps.dotlan.net/system/" + systemName.replace(" ","_")), bolders) + " [" + getLink(regionName, ("http://evemaps.dotlan.net/map/" + regionName.replace(" ","_") + "/" + systemName.replace(" ","_")), bolders) + "]\nConstellation: " + getLink(constellationName, ("http://evemaps.dotlan.net/map/" + regionName.replace(" ","_") + "/" + constellationName.replace(" ","_")), bolders))

    return notifyingMessage

def SovStructureReinforced(timestamp, fulldetails, typeidlist, geographicinformation, bolders, pinger, accessToken):
    
    structureTypes = {"1" : "Territorial Claim Unit", "2" : "Infrastructure Hub"}

    structureType = structureTypes[str(fulldetails["campaignEventType"])]
    systemName = ESI.getSystemName(geographicinformation, str(fulldetails["solarSystemID"]))
    regionName = ESI.getRegionName(geographicinformation, str(fulldetails["solarSystemID"]))

    notifyingMessage = (pinger + " Sovereignty Structure Reinforced - [" + timestamp + "]\n" + bolders + "The " + structureType + " In " + systemName + " Has Been Reinforced!" + bolders + "\nLocation: " + getLink(systemName, ("http://evemaps.dotlan.net/system/" + systemName.replace(" ","_")), bolders) + " [" + getLink(regionName, ("http://evemaps.dotlan.net/map/" + regionName.replace(" ","_") + "/" + systemName.replace(" ","_")), bolders) + "]\nNodes Decloak At: " + getRealTime(fulldetails["decloakTime"]))

    return notifyingMessage

def SovStructureDestroyed(timestamp, fulldetails, typeidlist, geographicinformation, bolders, pinger, accessToken):

    structureType = typeidlist[str(fulldetails["structureTypeID"])]
    systemName = ESI.getSystemName(geographicinformation, str(fulldetails["solarSystemID"]))
    regionName = ESI.getRegionName(geographicinformation, str(fulldetails["solarSystemID"]))

    notifyingMessage = (pinger + " Sovereignty Structure Destroyed - [" + timestamp + "]\n" + bolders + "The " + structureType + " In " + systemName + " Has Been Destroyed!" + bolders + "\nLocation: " + getLink(systemName, ("http://evemaps.dotlan.net/system/" + systemName.replace(" ","_")), bolders) + " [" + getLink(regionName, ("http://evemaps.dotlan.net/map/" + regionName.replace(" ","_") + "/" + systemName.replace(" ","_")), bolders) + "]")

    return notifyingMessage

def SovAllClaimAquiredMsg(timestamp, fulldetails, typeidlist, geographicinformation, bolders, pinger, accessToken):

    systemName = ESI.getSystemName(geographicinformation, str(fulldetails["solarSystemID"]))
    regionName = ESI.getRegionName(geographicinformation, str(fulldetails["solarSystemID"]))
    corpDetails = ESI.getCorpData(fulldetails["corpID"])
    corpName = corpDetails["name"]
    if "alliance_id" in corpDetails:
        allianceDetails = ESI.getAllianceData(corpDetails["alliance_id"])
        allianceName = allianceDetails["name"]
    else:
        allianceName = "[No Alliance]"
        
    notifyingMessage = (pinger + " Sovereignty Claim Acquired - [" + timestamp + "]\n" + bolders + "Sovereignty Has Been Acquired In " + systemName + "!" + bolders + "\nLocation: " + getLink(systemName, ("http://evemaps.dotlan.net/system/" + systemName.replace(" ","_")), bolders) + " [" + getLink(regionName, ("http://evemaps.dotlan.net/map/" + regionName.replace(" ","_") + "/" + systemName.replace(" ","_")), bolders) + "]\nOwner: " + getLink(corpName, ("http://evemaps.dotlan.net/corp/" + str(fulldetails["corpID"])), bolders) + " [" + getLink(allianceName, ("http://evemaps.dotlan.net/alliance/" + str(corpDetails["alliance_id"])), bolders) + "]")

    return notifyingMessage

def SovAllClaimLostMsg(timestamp, fulldetails, typeidlist, geographicinformation, bolders, pinger, accessToken):

    systemName = ESI.getSystemName(geographicinformation, str(fulldetails["solarSystemID"]))
    regionName = ESI.getRegionName(geographicinformation, str(fulldetails["solarSystemID"]))
    corpDetails = ESI.getCorpData(fulldetails["corpID"])
    corpName = corpDetails["name"]
    if "alliance_id" in corpDetails:
        allianceDetails = ESI.getAllianceData(corpDetails["alliance_id"])
        allianceName = allianceDetails["name"]
    else:
        allianceName = "[No Alliance]"
        
    notifyingMessage = (pinger + " Sovereignty Claim Lost - [" + timestamp + "]\n" + bolders + "Sovereignty Has Been Lost In " + systemName + "!" + bolders + "\nLocation: " + getLink(systemName, ("http://evemaps.dotlan.net/system/" + systemName.replace(" ","_")), bolders) + " [" + getLink(regionName, ("http://evemaps.dotlan.net/map/" + regionName.replace(" ","_") + "/" + systemName.replace(" ","_")), bolders) + "]\nOwner: " + getLink(corpName, ("http://evemaps.dotlan.net/corp/" + str(fulldetails["corpID"])), bolders) + " [" + getLink(allianceName, ("http://evemaps.dotlan.net/alliance/" + str(corpDetails["alliance_id"])), bolders) + "]")

    return notifyingMessage

def SovStructureSelfDestructRequested(timestamp, fulldetails, typeidlist, geographicinformation, bolders, pinger, accessToken):

    structureType = typeidlist[str(fulldetails["structureTypeID"])]
    systemName = ESI.getSystemName(geographicinformation, str(fulldetails["solarSystemID"]))
    regionName = ESI.getRegionName(geographicinformation, str(fulldetails["solarSystemID"]))
    requesterString = ESI.getFullCharacterLink(fulldetails["charID"], bolders)
        
    notifyingMessage = (pinger + " Sovereignty Started Self-Destruct - [" + timestamp + "]\n" + bolders + "A Self-Destruct Request Has Been Made For The " + structureType + " In " + systemName + "!" + bolders + "\nLocation: " + getLink(systemName, ("http://evemaps.dotlan.net/system/" + systemName.replace(" ","_")), bolders) + " [" + getLink(regionName, ("http://evemaps.dotlan.net/map/" + regionName.replace(" ","_") + "/" + systemName.replace(" ","_")), bolders) + "]\nRequested By: " + requesterString + "\nDestruction Time: " + getRealTime(fulldetails["destructTime"]))

    return notifyingMessage

def SovStructureSelfDestructFinished(timestamp, fulldetails, typeidlist, geographicinformation, bolders, pinger, accessToken):

    structureType = typeidlist[str(fulldetails["structureTypeID"])]
    systemName = ESI.getSystemName(geographicinformation, str(fulldetails["solarSystemID"]))
    regionName = ESI.getRegionName(geographicinformation, str(fulldetails["solarSystemID"]))
        
    notifyingMessage = (pinger + " Sovereignty Finished Self-Destruct - [" + timestamp + "]\n" + bolders + "The Self-Destruct Request For The " + structureType + " In " + systemName + " Has Completed!" + bolders + "\nLocation: " + getLink(systemName, ("http://evemaps.dotlan.net/system/" + systemName.replace(" ","_")), bolders) + " [" + getLink(regionName, ("http://evemaps.dotlan.net/map/" + regionName.replace(" ","_") + "/" + systemName.replace(" ","_")), bolders) + "]")

    return notifyingMessage

def SovStructureSelfDestructCancel(timestamp, fulldetails, typeidlist, geographicinformation, bolders, pinger, accessToken):

    structureType = typeidlist[str(fulldetails["structureTypeID"])]
    systemName = ESI.getSystemName(geographicinformation, str(fulldetails["solarSystemID"]))
    regionName = ESI.getRegionName(geographicinformation, str(fulldetails["solarSystemID"]))
    cancellerString = ESI.getFullCharacterLink(fulldetails["charID"], bolders)
        
    notifyingMessage = (pinger + " Sovereignty Cancelled Self-Destruct - [" + timestamp + "]\n" + bolders + "The Self-Destruct Request For The " + structureType + " In " + systemName + " Has Been Cancelled!" + bolders + "\nLocation: " + getLink(systemName, ("http://evemaps.dotlan.net/system/" + systemName.replace(" ","_")), bolders) + " [" + getLink(regionName, ("http://evemaps.dotlan.net/map/" + regionName.replace(" ","_") + "/" + systemName.replace(" ","_")), bolders) + "]\nCancelled By: " + cancellerString)

    return notifyingMessage

def OrbitalAttacked(timestamp, fulldetails, typeidlist, geographicinformation, bolders, pinger, accessToken):

    systemName = ESI.getSystemName(geographicinformation, str(fulldetails["solarSystemID"]))
    regionName = ESI.getRegionName(geographicinformation, str(fulldetails["solarSystemID"]))
    planetDetails = ESI.getPlanetDetails(fulldetails["planetID"])
    planetName = planetDetails["name"]
    attackerString = ESI.getFullCharacterLink(fulldetails["aggressorID"], bolders)

    notifyingMessage = (pinger + " Customs Office Under Attack - [" + timestamp + "]\n" + bolders + "A Customs Office Is Under Attack!" + bolders + "\nLocation: " + getLink(systemName, ("http://evemaps.dotlan.net/system/" + systemName.replace(" ","_")), bolders) + " (" + planetName.replace(systemName, "Planet") + ") [" + getLink(regionName, ("http://evemaps.dotlan.net/map/" + regionName.replace(" ","_") + "/" + systemName.replace(" ","_")), bolders) + "]\nAttacker: " + attackerString + "\nHealth: " + str(round((float(fulldetails["shieldLevel"]) * 100), 2)) + "% Shield")

    return notifyingMessage

def OrbitalReinforced(timestamp, fulldetails, typeidlist, geographicinformation, bolders, pinger, accessToken):

    systemName = ESI.getSystemName(geographicinformation, str(fulldetails["solarSystemID"]))
    regionName = ESI.getRegionName(geographicinformation, str(fulldetails["solarSystemID"]))
    planetDetails = ESI.getPlanetDetails(fulldetails["planetID"])
    planetName = planetDetails["name"]
    attackerString = ESI.getFullCharacterLink(fulldetails["aggressorID"], bolders)

    notifyingMessage = (pinger + " Customs Office Reinforced - [" + timestamp + "]\n" + bolders + "A Customs Office Has Been Reinforced!" + bolders + "\nLocation: " + getLink(systemName, ("http://evemaps.dotlan.net/system/" + systemName.replace(" ","_")), bolders) + " (" + planetName.replace(systemName, "Planet") + ") [" + getLink(regionName, ("http://evemaps.dotlan.net/map/" + regionName.replace(" ","_") + "/" + systemName.replace(" ","_")), bolders) + "]\nAttacker: " + attackerString + "\nComes Out At: " + getRealTime(fulldetails["reinforceExitTime"]))

    return notifyingMessage

def TowerAlertMsg(timestamp, fulldetails, typeidlist, geographicinformation, bolders, pinger, accessToken):
    
    posType = typeidlist[str(fulldetails["typeID"])]
    systemName = ESI.getSystemName(geographicinformation, str(fulldetails["solarSystemID"]))
    regionName = ESI.getRegionName(geographicinformation, str(fulldetails["solarSystemID"]))
    moonDetails = ESI.getMoonDetails(fulldetails["moonID"])
    moonName = moonDetails["name"]
    attackerString = ESI.getFullCharacterLink(fulldetails["aggressorID"], bolders)
    
    notifyingMessage = (pinger + " Tower Under Attack - [" + timestamp + "]\n" + bolders + "A(n) " + posType + " in " + systemName + " Is Under Attack!" + bolders + "\nLocation: " + getLink(systemName, ("http://evemaps.dotlan.net/system/" + systemName.replace(" ","_")), bolders) + " (" + moonName.replace(systemName, "Planet") + ") [" + getLink(regionName, ("http://evemaps.dotlan.net/map/" + regionName.replace(" ","_") + "/" + systemName.replace(" ","_")), bolders) + "]\nAttacker: " + attackerString + "\nHealth: " + str(round(float(fulldetails["shieldValue"] * 100), 2)) + "% Shield / " + str(round(float(fulldetails["armorValue"] * 100), 2)) + "% Armor / " + str(round(float(fulldetails["hullValue"] * 100), 2)) + "% Structure")

    return notifyingMessage

def TowerResourceAlertMsg(timestamp, fulldetails, typeidlist, geographicinformation, bolders, pinger, accessToken):
    
    posType = typeidlist[str(fulldetails["typeID"])]
    systemName = ESI.getSystemName(geographicinformation, str(fulldetails["solarSystemID"]))
    regionName = ESI.getRegionName(geographicinformation, str(fulldetails["solarSystemID"]))
    moonDetails = ESI.getMoonDetails(fulldetails["moonID"])
    moonName = moonDetails["name"]
    corpDetails = ESI.getCorpData(fulldetails["corpID"])
    corpName = corpDetails["name"]
    
    notifyingMessage = (pinger + " Tower Low On Fuel - [" + timestamp + "]\n" + bolders + "A(n) " + posType + " in " + systemName + " Is Low On Fuel!" + bolders + "\nLocation: " + getLink(systemName, ("http://evemaps.dotlan.net/system/" + systemName.replace(" ","_")), bolders) + " (" + moonName.replace(systemName, "Planet") + ") [" + getLink(regionName, ("http://evemaps.dotlan.net/map/" + regionName.replace(" ","_") + "/" + systemName.replace(" ","_")), bolders) + "]\nOwner: " + getLink(corpName, ("http://evemaps.dotlan.net/corp/" + corpName.replace(" ","_")), bolders) + "\nRequired Fuel: \n```\n")
    
    for fuels in fulldetails["wants"]:
        notifyingMessage += (typeidlist[str(fuels["typeID"])] + ": " + str(fuels["quantity"]) + " Units Remaining\n")
    
    notifyingMessage += "```"

    return notifyingMessage
    
def AllAnchoringMsg(timestamp, fulldetails, typeidlist, geographicinformation, bolders, pinger, accessToken):
    
    posType = typeidlist[str(fulldetails["typeID"])]
    systemName = ESI.getSystemName(geographicinformation, str(fulldetails["solarSystemID"]))
    regionName = ESI.getRegionName(geographicinformation, str(fulldetails["solarSystemID"]))
    moonDetails = ESI.getMoonDetails(fulldetails["moonID"])
    moonName = moonDetails["name"]
    
    corpDetails = ESI.getCorpData(fulldetails["corpID"])
    corpName = corpDetails["name"]
    
    if "alliance_id" in corpDetails:
        allianceDetails = ESI.getAllianceData(corpDetails["alliance_id"])
        allianceName = allianceDetails["name"]
        
        anchorerString = (getLink(corpName, ("http://evemaps.dotlan.net/corp/" + str(fulldetails["corpID"])), bolders) + " [" + getLink(allianceName, ("http://evemaps.dotlan.net/alliance/" + str(corpDetails["alliance_id"])), bolders) + "]")
    
    else:
        anchorerString = (getLink(corpName, ("http://evemaps.dotlan.net/corp/" + str(fulldetails["corpID"])), bolders))
    
    notifyingMessage = (pinger + " Tower Anchoring - [" + timestamp + "]\n" + bolders + "A(n) " + posType + " Has Begun Anchoring in " + systemName + "!" + bolders + "\nLocation: " + getLink(systemName, ("http://evemaps.dotlan.net/system/" + systemName.replace(" ","_")), bolders) + " (" + moonName.replace(systemName, "Planet") + ") [" + getLink(regionName, ("http://evemaps.dotlan.net/map/" + regionName.replace(" ","_") + "/" + systemName.replace(" ","_")), bolders) + "]\nAnchoring Entity: " + anchorerString)

    return notifyingMessage
    
def findFunction(type):
    functionList = {
    "EntosisCaptureStarted" : EntosisCaptureStarted,
    "StructureDestroyed" : StructureDestroyed,
    "StructureLostArmor" : StructureLostArmor,
    "StructureLostShields" : StructureLostShields,
    "StructureUnderAttack" : StructureUnderAttack,
    "MoonminingAutomaticFracture" : MoonminingAutomaticFracture,
    "MoonminingExtractionCancelled" : MoonminingExtractionCancelled,
    "MoonminingExtractionFinished" : MoonminingExtractionFinished,
    "MoonminingExtractionStarted" : MoonminingExtractionStarted,
    "MoonminingLaserFired" : MoonminingLaserFired,
    "StructureAnchoring" : StructureAnchoring,
    "StructureFuelAlert" : StructureFuelAlert,
    "StructureOnline" : StructureOnline,
    "StructureUnanchoring" : StructureUnanchoring,
    "StructureServicesOffline" : StructureServicesOffline,
    "StructureWentHighPower" : StructureWentHighPower,
    "StructureWentLowPower" : StructureWentLowPower,
    "StructuresReinforcementChanged" : StructuresReinforcementChanged,
    "OwnershipTransferred" : OwnershipTransferred,
    "SovCommandNodeEventStarted" : SovCommandNodeEventStarted,
    "SovStructureReinforced" : SovStructureReinforced,
    "SovStructureDestroyed" : SovStructureDestroyed,
    "SovAllClaimAquiredMsg" : SovAllClaimAquiredMsg,
    "SovAllClaimLostMsg" : SovAllClaimLostMsg,
    "SovStructureSelfDestructRequested" : SovStructureSelfDestructRequested,
    "SovStructureSelfDestructFinished" : SovStructureSelfDestructFinished,
    "SovStructureSelfDestructCancel" : SovStructureSelfDestructCancel,
    "OrbitalAttacked" : OrbitalAttacked,
    "OrbitalReinforced" : OrbitalReinforced,
    "TowerAlertMsg" : TowerAlertMsg,
    "TowerResourceAlertMsg" : TowerResourceAlertMsg,
    "AllAnchoringMsg" : AllAnchoringMsg
    }

    return functionList[type]

def getLink(text, url, bolders):

    if bolders == "**":
        link = "[" + text + "](<" + url + ">)"
    
    else:
        link = "<" + url + "|" + text + ">"
        
    return link
    
def getRealTime(intTime):
    
    if len(str(intTime)) >= 17:
        unixTime = int((int(intTime) / 10000000) - 11644473600)
    else:
        unixTime = int(intTime)
    
    readableTime = datetime.utcfromtimestamp(unixTime).strftime("%d %B, %Y - %H:%M:%S EVE")
    
    return readableTime
    
def postToDiscord(messageToPost, webhookURL):
    
    totalErrors = 0
    
    while True:
        toPost = requests.post(webhookURL, data={"content" : messageToPost})
        
        if str(toPost.status_code) == "204":
            break
        else:
            totalErrors += 1
            
            print("Error Sending Discord Message (Probably Due to Rate Limiting) - Trying Again")
            
            time.sleep(5)
                        
            if totalErrors == 10:
                break
    
    time.sleep(0.5)

def postToSlack(messageToPost, webhookURL):
    goodStatusCodes = ["200", "201", "202", "203", "204"]

    totalErrors = 0
    
    while True:
    
        toPost = requests.post(webhookURL, data={"payload" : json.dumps({"text" : messageToPost})})
        
        if str(toPost.status_code) in goodStatusCodes:
            break
        else:
            totalErrors += 1
            
            print("Error Sending Slack Message (Probably Due to Rate Limiting) - Trying Again")
            
            time.sleep(5)
                        
            if totalErrors == 10:
                break
                
    time.sleep(0.5)

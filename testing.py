import traceback
import os
import time
import requests
import schedule
import inspect
import json

import ESI
import notifier

from pathlib import Path
from datetime import datetime
from datetime import timezone

import mysql.connector as DatabaseConnector

def dataFile():

    filename = inspect.getframeinfo(inspect.currentframe()).filename
    path = os.path.dirname(os.path.abspath(filename))
    
    dataLocation = str(path)
    
    return(dataLocation)

if Path(dataFile() + "/config/config.json").is_file():

    with open(dataFile() + "/config/config.json", "r") as configFile:
        configData = json.load(configFile)
        
        databaseInfo = configData["Database"]
        appInfo = configData["App"]
    
else:
    raise Warning("Configuration file has not been generated!")

with open(dataFile() + "/resources/data/geographicInformation.json", "r") as geographyFile:
    geographicInformation = json.load(geographyFile)
        
with open(dataFile() + "/resources/data/TypeIDs.json", "r") as typeIDFile:
    typeIDList = json.load(typeIDFile)

refreshToken = ""
accessToken = ESI.getAccessToken(appInfo, refreshToken)

#Citadel ID: 1028858195912
#Citadel System ID: 30000144
#Citadel TypeID: 35834
#Moon ID: 40009237
#Target Timestamp: 132101258260000000
#Sov System ID: 30001198
#Sov Constellation ID: 20000175
#Ihub Campaign: 2
#Ihub Type ID: 32458
#Hundreds of Nanoseconds in an hour: 36000000000
#Target Character ID: 95998620
#Target Corp ID: 98522659
#OreDict: {22:100000000}
#Planet ID: 40076017
    
functionList = {
"EntosisCaptureStarted" : {"solarSystemID":30001198, "structureTypeID":32458},
"StructureDestroyed" : {"solarsystemID":30000144,"structureID":1028858195912,"structureTypeID":35834},
"StructureLostArmor" : {"solarsystemID":30000144,"structureID":1028858195912,"structureTypeID":35834,"timestamp":132101258260000000},
"StructureLostShields" : {"solarsystemID":30000144,"structureID":1028858195912,"structureTypeID":35834,"timestamp":132101258260000000},
"StructureUnderAttack" : {"armorPercentage":100,"charID":95998620,"hullPercentage":100,"shieldPercentage":93.2523453635347,"solarsystemID":30000144,"structureID":1028858195912,"structureTypeID":35834},
"MoonminingAutomaticFracture" : {"moonID":40009237,"oreVolumeByType":{22:100000000},"solarSystemID":30000144,"structureID":1028858195912,"structureTypeID":35834},
"MoonminingExtractionCancelled" : {"moonID":40009237,"solarSystemID":30000144,"structureID":1028858195912,"structureTypeID":35834,"cancelledBy":95998620},
"MoonminingExtractionFinished" : {"moonID":40009237,"oreVolumeByType":{22:100000000},"solarSystemID":30000144,"structureID":1028858195912,"structureTypeID":35834},
"MoonminingExtractionStarted" : {"moonID":40009237,"oreVolumeByType":{22:100000000},"solarSystemID":30000144,"structureID":1028858195912,"structureTypeID":35834,"startedBy":95998620,"readyTime":132101258260000000},
"MoonminingLaserFired" : {"moonID":40009237,"oreVolumeByType":{22:100000000},"solarSystemID":30000144,"structureID":1028858195912,"structureTypeID":35834,"firedBy":95998620},
"StructureAnchoring" : {"solarsystemID":30000144,"structureID":1028858195912,"structureTypeID":35834,"timeLeft":36000000000},
"StructureFuelAlert" : {"listOfTypesAndQty":[[1000,4246]],"solarsystemID":30000144,"structureID":1028858195912,"structureTypeID":35834},
"StructureOnline" : {"solarsystemID":30000144,"structureID":1028858195912,"structureTypeID":35834},
"StructureUnanchoring" : {"solarsystemID":30000144,"structureID":1028858195912,"structureTypeID":35834,"timeLeft":36000000000},
"StructureServicesOffline" : {'listOfServiceModuleIDs': [35886, 35878], 'solarsystemID': 30002614, 'structureID': 1026947207468, 'structureShowInfoData': ['showinfo', 35825, 1026947207468], 'structureTypeID': 35825},
"StructureWentHighPower" : {"solarsystemID":30000144,"structureID":1028858195912,"structureTypeID":35834},
"StructureWentLowPower" : {"solarsystemID":30000144,"structureID":1028858195912,"structureTypeID":35834},
"StructuresReinforcementChanged" : {'allStructureInfo': [[1025688974365, 'Bittanshal - Starlight of the South', 35832]], 'hour': 0, 'numStructures': 1, 'timestamp': 132121315521418439, 'weekday': 4},
"OwnershipTransferred" : {'charID': 92625869, 'newOwnerCorpID': 98444656, 'oldOwnerCorpID': 98177788, 'solarSystemID': 30001245, 'structureID': 1030900929122, 'structureName': "BUZ-DB - Dan & Kungan's Pleasure Dungeon", 'structureTypeID': 35833},
"SovCommandNodeEventStarted" : {"campaignEventType":2,"constellationID":20000175,"solarSystemID":30001198},
"SovStructureReinforced" : {"campaignEventType":2,"solarSystemID":30001198,"decloakTime":132101258260000000},
"SovStructureDestroyed" : {"solarSystemID":30001198,"structureTypeID":32458},
"SovAllClaimAquiredMsg" : {"corpID":98522659,"solarSystemID":30001198},
"SovAllClaimLostMsg" : {"corpID":98522659,"solarSystemID":30001198},
"SovStructureSelfDestructRequested" : {"charID":95998620,"destructTime":132101258260000000,"solarSystemID":30001198,"structureTypeID":32458},
"SovStructureSelfDestructFinished" : {"solarSystemID":30001198,"structureTypeID":32458},
"SovStructureSelfDestructCancel" : {"charID":95998620,"solarSystemID":30001198,"structureTypeID":32458},
"OrbitalAttacked" : {"aggressorID":95998620,"planetID":40076017,"shieldLevel":0,"solarSystemID":30001198},
"OrbitalReinforced" : {"aggressorID":95998620,"planetID":40076017,"shieldLevel":0,"solarSystemID":30001198,"reinforceExitTime":132101258260000000},
"TowerAlertMsg" : {'aggressorAllianceID': 99003214, 'aggressorCorpID': 98522659, 'aggressorID': 95998620, 'armorValue': 1.0, 'hullValue': 1.0, 'moonID': 40076354, 'shieldValue': 0.5102011021233386, 'solarSystemID': 30001203, 'typeID': 20060},
"TowerResourceAlertMsg" : {'allianceID': 99003214, 'corpID': 98444656, 'moonID': 40076354, 'solarSystemID': 30001203, 'typeID': 20060, 'wants': [{'quantity': 0, 'typeID': 4247}]},
"AllAnchoringMsg" : {'allianceID': 99009054, 'corpID': 98598954, 'corpsPresent': [], 'moonID': 40078944, 'solarSystemID': 30001243, 'typeID': 12235}
}

for things in functionList:
    toCall = notifier.findFunction(things)
    finalMessage = toCall("25 August, 2019 - 00:00:00 EVE", functionList[things], typeIDList, geographicInformation, "*", "<!channel>", accessToken)
    
    print("----- " + things + " -----\n")
    print(finalMessage)
    print("\n\n")

print("\n\n\n ---------------------------------------------------- \n\n\n")
    
for things in functionList:
    toCall = notifier.findFunction(things)
    finalMessage = toCall("25 August, 2019 - 00:00:00 EVE", functionList[things], typeIDList, geographicInformation, "**", "@everyone", accessToken)
    
    print("----- " + things + " -----\n")
    print(finalMessage)
    print("\n\n")
    

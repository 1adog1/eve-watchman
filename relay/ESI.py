def getAccessToken(appInfo, refreshToken):
    import base64
    import requests
    import json

    toHeader = appInfo["ClientID"] + ":" + appInfo["ClientSecret"]
    authHeader = "Basic " + base64.urlsafe_b64encode(toHeader.encode("utf-8")).decode()
    authBody = {"grant_type":"refresh_token","refresh_token":refreshToken}

    accessPOST = requests.post("https://login.eveonline.com/v2/oauth/token", headers={"Host":"login.eveonline.com", "content-type":"application/x-www-form-urlencoded", "Authorization":authHeader}, data=authBody)
    accessResponse = json.loads(accessPOST.text)
    
    try:
        return accessResponse
    except:
        return "Bad Token"
        
def getNotifications(characterID, accessToken):
    import requests
    import json
    
    headers = {"authorization" : "Bearer " + accessToken}

    notificationsRequest = requests.get("https://esi.evetech.net/latest/characters/" + str(characterID) + "/notifications/?datasource=tranquility", headers=headers)
    
    notificationData = json.loads(notificationsRequest.text)
    
    return notificationData

def getADM(structureTypeID, systemID):
    import requests
    import json

    admRequest = requests.get("https://esi.evetech.net/latest/sovereignty/structures/?datasource=tranquility")
    
    admData = json.loads(admRequest.text)
    
    for structures in admData:
        if int(structureTypeID) == int(structures["structure_type_id"]) and int(systemID) == int(structures["solar_system_id"]):
            if "vulnerability_occupancy_level" in structures:
                return structures["vulnerability_occupancy_level"]
            else:
                return 0
    
    return 0
    
def getPlanetDetails(planetID):
    import requests
    import json
    
    planetRequest = requests.get("https://esi.evetech.net/latest/universe/planets/" + str(planetID) + "/?datasource=tranquility")
    
    planetData = json.loads(planetRequest.text)
    
    return planetData
    
def getMoonDetails(moonID):
    import requests
    import json
    
    moonRequest = requests.get("https://esi.evetech.net/latest/universe/moons/" + str(moonID) + "/?datasource=tranquility")
    
    moonData = json.loads(moonRequest.text)
    
    return moonData
    
def getConstellationDetails(constellationID):
    import requests
    import json
    
    constellationRequest = requests.get("https://esi.evetech.net/latest/universe/constellations/" + str(constellationID) + "/?datasource=tranquility")
    
    constellationData = json.loads(constellationRequest.text)
    
    return constellationData
    
def getStructureDetails(structureID, accessToken):
    import requests
    import json
    
    headers = {"authorization" : "Bearer " + accessToken}
    
    structureRequest = requests.get("https://esi.evetech.net/latest/universe/structures/" + str(structureID) + "/?datasource=tranquility", headers=headers)
    
    structureData = json.loads(structureRequest.text)
    
    if "error" in structureData:
        placeholderData = {"name":"[Unknown Structure]", "owner_id":1000001, "type_id":42601}
        
        return placeholderData
    else:
        return structureData
        
def getSystemName(geographicinformation, systemID):
    try:
        return geographicinformation[systemID]["name"]
        
    except:
        return ("Unknown System " + str(systemID))
    
def getRegionName(geographicinformation, systemID):
    try:
        return geographicinformation[systemID]["region"]
        
    except:
        return ("Unknown System " + str(systemID))

def getCharacterData(characterID):
    import requests
    import json
    
    headers = {"accept":"application/json", "Content-Type":"application/json"}
    dataToCheck = json.dumps([characterID])
    
    characterAffilationRequest = requests.post("https://esi.evetech.net/latest/characters/affiliation/?datasource=tranquility", data=dataToCheck, headers=headers)
        
    if characterAffilationRequest.status_code == requests.codes.ok:

        characterData = json.loads(characterAffilationRequest.text)[0]

        namesToCheck = [characterData["character_id"], characterData["corporation_id"]]

        if "alliance_id" in characterData:
            namesToCheck.append(characterData["alliance_id"])
        
        nameDataToCheck = json.dumps(namesToCheck)
        
        characterNamesRequest = requests.post("https://esi.evetech.net/latest/universe/names/?datasource=tranquility", data=nameDataToCheck, headers=headers)
        namesData = json.loads(characterNamesRequest.text)
        
        for eachName in namesData:
            if eachName["category"] == "character":
                characterData["name"] = eachName["name"]
            if eachName["category"] == "corporation":
                characterData["corporation_name"] = eachName["name"]
            if eachName["category"] == "alliance":
                characterData["alliance_name"] = eachName["name"]

        return characterData
        
    else:
    
        return False
    
def getCorpData(corporationID):
    import requests
    import json
    
    corpRequest = requests.get("https://esi.evetech.net/latest/corporations/" + str(corporationID) + "/?datasource=tranquility")
    corpData = json.loads(corpRequest.text)
        
    return corpData
    
def getAllianceData(allianceID):
    import requests
    import json
    
    allianceRequest = requests.get("https://esi.evetech.net/latest/alliances/" + str(allianceID) + "/?datasource=tranquility")
    allianceData = json.loads(allianceRequest.text)
        
    return allianceData
    
def getFullCharacterLink(characterID, bolders):
    import notifier

    characterDetails = getCharacterData(characterID)
    if characterDetails == False:
        corpDetails = getCorpData(characterID)
        corpName = corpDetails["name"]
        
        characterString = "(" + notifier.getLink(corpName, ("http://evemaps.dotlan.net/corp/" + str(characterID)), bolders) + ")"

        if "alliance_id" in corpDetails:
            allianceDetails = getAllianceData(corpDetails["alliance_id"])
            allianceName = allianceDetails["name"]
            
            characterString = ("(" + notifier.getLink(corpName, ("http://evemaps.dotlan.net/corp/" + str(characterID)), bolders) + ") [" + notifier.getLink(allianceName, ("http://evemaps.dotlan.net/alliance/" + str(corpDetails["alliance_id"])), bolders) + "]")
        
    else:
        characterName = characterDetails["name"]
        corpName = characterDetails["corporation_name"]
        
        if "alliance_id" in characterDetails:
            allianceName = characterDetails["alliance_name"]
            
            characterString = (notifier.getLink(characterName, ("https://zkillboard.com/character/" + str(characterID) + "/"), bolders) + " (" + notifier.getLink(corpName, ("http://evemaps.dotlan.net/corp/" + str(characterDetails["corporation_id"])), bolders) + ") [" + notifier.getLink(allianceName, ("http://evemaps.dotlan.net/alliance/" + str(characterDetails["alliance_id"])), bolders) + "]")
        
        else:
            characterString = (notifier.getLink(characterName, ("https://zkillboard.com/character/" + str(characterID) + "/"), bolders) + " (" + notifier.getLink(corpName, ("http://evemaps.dotlan.net/corp/" + str(characterDetails["corporation_id"])), bolders) + ")")
            
    return characterString

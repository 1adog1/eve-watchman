import json
import yaml
import inspect
import traceback
import os
import time
import requests
import schedule
import configparser

import ESI
import notifier

from pathlib import Path
from datetime import datetime
from datetime import timezone

import mysql.connector as DatabaseConnector


#################
# PATH OVERRIDE #
#################
configPathOverride = "/var/app/"
dataPathOverride = False

#If you need to run the python part of this app elsewhere for whatever reason, set the above two variables to absolute paths where the watchmanConfig.ini and two .json files will be contained respectively. Otherwise, keep them set to False.

def dataFile(pathOverride, extraFolder):
    
    if not pathOverride:
    
        filename = inspect.getframeinfo(inspect.currentframe()).filename
        path = os.path.join(os.path.dirname(os.path.abspath(filename)), "..")
        
        dataLocation = str(path) + extraFolder
        
        return(dataLocation)
    
    else:
        return(pathOverride)

if Path(dataFile(configPathOverride, "/config") + "/watchmanConfig.ini").is_file():
    config = configparser.ConfigParser()
    config.read(dataFile(configPathOverride, "/config") + "/watchmanConfig.ini")
    
    databaseInfo = config["Database"]
    appInfo = config["Authentication"]
    
else:
    raise Warning("No Configuration File Found!")
    
with open(dataFile(dataPathOverride, "/resources/data") + "/geographicInformation.json", "r") as geographyFile:
    geographicInformation = json.load(geographyFile)
        
with open(dataFile(dataPathOverride, "/resources/data") + "/TypeIDs.json", "r") as typeIDFile:
    typeIDList = json.load(typeIDFile)

def checkStagger():

    sq1Database = DatabaseConnector.connect(user=databaseInfo["DatabaseUsername"], password=databaseInfo["DatabasePassword"], host=databaseInfo["DatabaseServer"] , port=int(databaseInfo["DatabasePort"]), database=databaseInfo["DatabaseName"])

    setupCursor = sq1Database.cursor(buffered=True)
    verifyingCursor = sq1Database.cursor(buffered=True)
    
    setupQuery = ("SELECT * FROM relays")
    setupCursor.execute(setupQuery)
    
    staggerQuery = ("SELECT * FROM staggering")
    verifyingCursor.execute(staggerQuery)
    
    currentStaggers = {} 
    oldStaggers = {}
    oldPositions = {}
    toGenerate = {}
    toRegen = {}
    toDelete = {}
    
    for (relayName, relayID, relayCorpID, relayCorp, relayRefreshToken, relayAllianceID, relayAlliance, relayRoles) in setupCursor:
    
        if str(relayCorpID) not in currentStaggers:
            currentStaggers[str(relayCorpID)] = []
            
        currentStaggers[str(relayCorpID)].append(str(relayID))
    
    for (staggerCorp, staggerCharacters, staggerFrequency, staggerLast, staggerPosition) in verifyingCursor:
        oldStaggers[str(staggerCorp)] = json.loads(staggerCharacters)
        
        oldPositions[str(staggerCorp)] = int(staggerPosition)
        
    for checkCharacters in currentStaggers:
        if checkCharacters not in oldStaggers:
            toGenerate[checkCharacters] = currentStaggers[checkCharacters]
        
        else:
            currentStaggers[checkCharacters].sort()
            oldStaggers[checkCharacters].sort()
            
            if currentStaggers[checkCharacters] != oldStaggers[checkCharacters] and checkCharacters not in toRegen:
                toRegen[checkCharacters] = currentStaggers[checkCharacters]                
            
    for checkCharacters in oldStaggers:
    
        if checkCharacters not in currentStaggers:
            toDelete[checkCharacters] = oldStaggers[checkCharacters]
        
        else:
            currentStaggers[checkCharacters].sort()
            oldStaggers[checkCharacters].sort()
            
            if currentStaggers[checkCharacters] != oldStaggers[checkCharacters] and checkCharacters not in toRegen:
                toRegen[checkCharacters] = currentStaggers[checkCharacters]

    for changeCorps in toGenerate:
        changeCursor = sq1Database.cursor(buffered=True)

        toGenerate[changeCorps].sort()
        frequencyToAdd = int(660/len(toGenerate[changeCorps]))

        generateQuery = ("INSERT INTO staggering (corporationid, characters, frequency, lastrun, currentposition) VALUES ({corporationid}, '{characters}', {frequency}, {lastrun}, {currentposition})").format(corporationid=int(changeCorps), characters=json.dumps(toGenerate[changeCorps]), frequency=frequencyToAdd, lastrun=0, currentposition=0)
        
        changeCursor.execute(generateQuery)
        
        sq1Database.commit()

        print(changeCorps + " created with a frequency of " + str(frequencyToAdd) + " seconds.")

    for changeCorps in toRegen:
        changeCursor = sq1Database.cursor(buffered=True)
        
        toRegen[changeCorps].sort()
        frequencyToUpdate = int(660/len(toRegen[changeCorps]))
        
        if len(toRegen[changeCorps]) <= oldPositions[changeCorps]:
            positionToUpdate = 0
        else:
            positionToUpdate = oldPositions[changeCorps]
        
        regenQuery = ("UPDATE staggering SET characters = '{characters}', frequency = {frequency}, currentposition = {currentposition} WHERE corporationid = {corporationid}").format(characters=json.dumps(toRegen[changeCorps]), frequency=frequencyToUpdate, currentposition=positionToUpdate, corporationid=int(changeCorps))
        
        changeCursor.execute(regenQuery)
        
        sq1Database.commit()
        
        print(changeCorps + " regenerated with a new frequency of " + str(frequencyToUpdate) + " seconds.")
        
    for changeCorps in toDelete:
        changeCursor = sq1Database.cursor(buffered=True)
        
        deleteQuery = ("DELETE FROM staggering WHERE corporationid={corporationid}").format(corporationid=int(changeCorps))
        
        changeCursor.execute(deleteQuery)
        
        sq1Database.commit()
        
        print(changeCorps + " deleted.")

    sq1Database.close()

def startRelay():
    try:
    
        checkStagger()
    
        charactersChecked = 0
        
        pingTypes = {"slack_webhook":{"everyone":"<!channel>","here":"<!here>","none":""}, "discord_webhook":{"everyone":"@everyone","here":"@here","none":""}}
        
        currentTime = datetime.now()
        readableCurrentTime = currentTime.strftime("%d %B, %Y - %H:%M:%S EVE")
        print("[" + readableCurrentTime + "] Monitoring Started!\n")

        sq1Database = DatabaseConnector.connect(user=databaseInfo["DatabaseUsername"], password=databaseInfo["DatabasePassword"], host=databaseInfo["DatabaseServer"] , port=int(databaseInfo["DatabasePort"]), database=databaseInfo["DatabaseName"])
        
        def writeToLogs(logType, logMessage):
        
            unixTime = time.time()
            
            logCursor = sq1Database.cursor(buffered=True)

            logQuery = ("INSERT INTO logs (timestamp, type, page, actor, details, trueip, forwardip) VALUES (%s, %s, 'Relay', '[Server Backend]', %s, 'N/A', 'N/A')")
            logCursor.execute(logQuery, (unixTime,logType,logMessage))
            
            sq1Database.commit()

        initialCursor = sq1Database.cursor(buffered=True)
        staggerQuery = ("SELECT * FROM staggering")
        initialCursor.execute(staggerQuery)
        
        for (staggerCorp, staggerCharacters, staggerFrequency, staggerLast, staggerPosition) in initialCursor:
        
            charactersToCheck = json.loads(staggerCharacters)        
            toCheckID = charactersToCheck[int(staggerPosition)]
            
            nextRun = int(staggerLast) + int(staggerFrequency)
            currentEpoch = int(time.time())
            
            if nextRun <= currentEpoch:
            
                firstCursor = sq1Database.cursor(buffered=True)

                relayQuery = ("SELECT * FROM relays WHERE id = {id}").format(id=int(toCheckID))

                firstCursor.execute(relayQuery)

                for (relayName, relayID, relayCorpID, relayCorp, relayRefreshToken, relayAllianceID, relayAlliance, relayRoles) in firstCursor:

                    accessToken = ESI.getAccessToken(appInfo, relayRefreshToken)
                    
                    if accessToken != "Bad Token":
                        notificationDict = ESI.getNotifications(relayID, accessToken)
                        
                        secondCursor = sq1Database.cursor(buffered=True)
                        configurationQuery = ("SELECT * FROM configurations")
                        secondCursor.execute(configurationQuery)
                        
                        for (configurationID, configurationType, configurationChannel, configurationURL, configurationPingType, configurationWhitelist, configurationTimestamp, configurationAlliance, configurationAllianceID, configurationCorporation, configurationCorporationID) in secondCursor:
                                                    
                            if str(configurationCorporationID) == str(relayCorpID):
                            
                                whitelist = json.loads(configurationWhitelist)
                            
                                for notifications in notificationDict:
                                
                                    notificationTime = datetime.strptime(notifications["timestamp"], "%Y-%m-%dT%H:%M:%SZ")
                                    timestamp = int(notificationTime.replace(tzinfo=timezone.utc).timestamp())
                                    readableNotificationTime = datetime.utcfromtimestamp(timestamp).strftime("%d %B, %Y - %H:%M:%S EVE")
                                                    
                                    if notifications["type"] in whitelist and configurationTimestamp < timestamp:
                                        thirdCursor = sq1Database.cursor(buffered=True)
                                        configurationQuery = ("SELECT * FROM notifications WHERE (id = {notificationID} AND configurationid = '{configurationID}')".format(notificationID=notifications["notification_id"], configurationID=configurationID))
                                        thirdCursor.execute(configurationQuery)
                                        
                                        knownTestList = []
                                        
                                        for testers in thirdCursor:
                                            knownTestList.append(testers)
                                            
                                        if not knownTestList:
                                        
                                            fullDetails = yaml.load(notifications["text"], Loader=yaml.FullLoader)
                                            toCall = notifier.findFunction(notifications["type"])    

                                            pinger = pingTypes[configurationType][configurationPingType]
                                        
                                            if configurationType == "discord_webhook":
                                                bolders = "**"
                                                
                                                messageToPost = toCall(readableNotificationTime, fullDetails, typeIDList, geographicInformation, bolders, pinger, accessToken)
                                                
                                                notifier.postToDiscord(messageToPost, configurationURL)
                                            
                                            else:
                                                bolders = "*"
                                                
                                                messageToPost = toCall(readableNotificationTime, fullDetails, typeIDList, geographicInformation, bolders, pinger, accessToken)
                                                
                                                notifier.postToSlack(messageToPost, configurationURL)
                                            
                                            fourthCursor = sq1Database.cursor(buffered=True)                        
                                            insertion = ("INSERT INTO notifications (timestamp, type, configurationid, id) VALUES ({timestamp}, '{type}', '{configurationid}', {id})").format(timestamp=timestamp, type=notifications["type"], configurationid=configurationID, id=int(notifications["notification_id"]))
                                            fourthCursor.execute(insertion)
                                            
                                            sq1Database.commit()
                                            
                                            successString = (notifications["type"] + " Notification Sent for " + configurationCorporation + " to " + configurationChannel + "!")
                                            
                                            writeToLogs("Relay Sent", successString)
                                            
                                            print(successString)
                    
                        charactersChecked += 1
                    
                        print("Successfully Checked " + relayName)
                    
                    else:
                        print("Failed to get access token for " + relayName)
            
                finalCursor = sq1Database.cursor(buffered=True)
                
                if (len(charactersToCheck) - 1) == staggerPosition:
                    updatePosition = 0
                else:
                    updatePosition = staggerPosition + 1
                    
                updateLast = int(time.time())
                
                finalQuery = ("UPDATE staggering SET lastrun = {lastrun}, currentposition = {currentposition} WHERE corporationid = {corporationid}").format(lastrun=updateLast, currentposition=updatePosition, corporationid=int(staggerCorp))
                finalCursor.execute(finalQuery)
                
                sq1Database.commit()
                
                print("Checked the corporation " + str(staggerCorp) + ", new position: " + str(updatePosition + 1) + "/" + str(len(charactersToCheck)) + ".\n")
            
        sq1Database.close()
        
        currentTime = datetime.now()
        readableCurrentTime = currentTime.strftime("%d %B, %Y - %H:%M:%S EVE")
        print("[" + readableCurrentTime + "] Monitoring Concluded!\n" + str(charactersChecked) + " characters checked!\n\n")
        
    except:
        traceback.print_exc()
        
        error = traceback.format_exc()
        try:
            writeToLogs("Relay Error", error)
        except:
            print("Failed to write a log entry!")

def automateRelay():
    schedule.every(30).seconds.do(startRelay)

    currentTime = datetime.now()
    readableCurrentTime = currentTime.strftime("%d %B, %Y - %H:%M:%S EVE")
    print(" --- [" + readableCurrentTime + "] EVE WATCHMAN - RELAY SUCCESSFULLY STARTED --- ")
            
    while True:
        schedule.run_pending()
        time.sleep(1)

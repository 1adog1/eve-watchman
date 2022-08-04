from Notifications import Notification
from EntityControl import Character, Corporation
from Terminus import Terminus
import ESI

import inspect
import os
import configparser
import time
import json
import traceback

from datetime import datetime, timezone
from pathlib import Path

import mysql.connector as DatabaseConnector

#If you've moved your config.ini file, set this variable to the path of the folder containing it (no trailing slash).
CONFIG_PATH_OVERRIDE = None

def dataFile(extraFolder):

    filename = inspect.getframeinfo(inspect.currentframe()).filename
    path = os.path.join(os.path.dirname(os.path.abspath(filename)), "../../..")

    dataLocation = str(path) + extraFolder

    return(dataLocation)

configPath = (CONFIG_PATH_OVERRIDE) if (CONFIG_PATH_OVERRIDE is not None) else (dataFile("/config"))

if Path(configPath + "/config.ini").is_file():

    config = configparser.ConfigParser()
    config.read(dataFile("/config") + "/config.ini")

    databaseInfo = config["Database"]
    EveAuthInfo = config["Eve Authentication"]

else:

    raise Warning("No Configuration File Found!")

def getTimeMark():

        currentTime = datetime.now()
        return currentTime.strftime("%d %B, %Y - %H:%M:%S EVE")

def makeLogEntry(passedDatabase, logType, logStatement):

    loggingCursor = passedDatabase.cursor(buffered=True)

    logInsert = "INSERT INTO logs (timestamp, type, actor, details) VALUES (%s, %s, %s, %s)"
    loggingCursor.execute(logInsert, (int(time.time()), logType, "[Relay]", logStatement))
    passedDatabase.commit()

    loggingCursor.close()

def registerNotification(passedDatabase, registerID, registerRelayID, registerType, registerTimestamp):

    registrationCursor = passedDatabase.cursor(buffered=True)

    notificationInsert = "INSERT INTO notifications (id, relayid, type, timestamp) VALUES (%s, %s, %s, %s)"
    registrationCursor.execute(notificationInsert, (registerID, registerRelayID, registerType, registerTimestamp))
    passedDatabase.commit()

    registrationCursor.close()

def run():

    print("[{Time}] Starting Run...\n".format(Time=getTimeMark()))

    sq1Database = DatabaseConnector.connect(
        user=databaseInfo["DatabaseUsername"],
        password=databaseInfo["DatabasePassword"],
        host=databaseInfo["DatabaseServer"],
        port=int(databaseInfo["DatabasePort"]),
        database=databaseInfo["DatabaseName"]
    )

    try:

        ESIAuth = ESI.AuthHandler(
            sq1Database,
            EveAuthInfo["ClientID"],
            EveAuthInfo["ClientSecret"],
            "Relay"
        )

        initialCursor = sq1Database.cursor(buffered=True)

        initialStatement = "SELECT DISTINCT corporationid FROM relaycharacters UNION SELECT corporationid FROM staggering"
        initialCursor.execute(initialStatement)

        for eachID, in initialCursor:

            currentCorporation = Corporation(eachID, EveAuthInfo["ClientID"], EveAuthInfo["ClientSecret"], sq1Database)

            if currentCorporation.valids and int(time.time()) >= currentCorporation.nextrun:

                print("Pulling notifications from {name} ({corp}){alliance}.".format(
                    name=currentCorporation.characters[currentCorporation.valids[currentCorporation.currentposition]].name,
                    corp=currentCorporation.characters[currentCorporation.valids[currentCorporation.currentposition]].corporation,
                    alliance=((" [" + currentCorporation.characters[currentCorporation.valids[currentCorporation.currentposition]].alliance + "]") if currentCorporation.characters[currentCorporation.valids[currentCorporation.currentposition]].alliance is not None else "")
                ))

                newNotifications = currentCorporation.characters[currentCorporation.valids[currentCorporation.currentposition]].getCharacterNotifications()

                relayCursor = sq1Database.cursor(buffered=True)

                relayStatement = "SELECT relays.id, relays.type, relays.url, relays.pingtype, relays.whitelist, relays.timestamp, relays.corporationname, channels.name, servers.name FROM relays LEFT JOIN channels ON relays.channelid = channels.id AND relays.type = channels.type LEFT JOIN servers ON channels.serverid = servers.id AND channels.type = servers.type WHERE relays.corporationid=%s"
                relayCursor.execute(relayStatement, (eachID,))

                for relayid, relaytype, relayurl, relayping, relaywhitelist, relaytime, relayCorp, relaychannel, relayserver in relayCursor:

                    notificationWhitelist = json.loads(relaywhitelist)

                    notificationsCursor = sq1Database.cursor(buffered=True)

                    notificationsStatement = "SELECT id FROM notifications WHERE relayid=%s"
                    notificationsCursor.execute(notificationsStatement, (relayid,))

                    alreadySent = [notificationid for notificationid, in notificationsCursor]

                    notificationsCursor.close()

                    for eachNotification in newNotifications:

                        notificationTimestamp = int(datetime.strptime(eachNotification["timestamp"], "%Y-%m-%dT%H:%M:%SZ").replace(tzinfo=timezone.utc).timestamp())

                        if notificationTimestamp >= relaytime and int(eachNotification["notification_id"]) not in alreadySent and "text" in eachNotification:

                            notificationData = Notification(
                                sq1Database,
                                eachNotification["type"],
                                notificationTimestamp,
                                eachNotification["text"],
                                relayCorp,
                                relaytype,
                                relayping,
                                ESIAuth.getAccessToken(currentCorporation.valids[currentCorporation.currentposition], retries=1)
                            )

                            if notificationData.shouldItRelay(notificationWhitelist):

                                notificationData.formatForRelaying()

                                if notificationData.outputData["Title"].endswith("Notification Failed to Parse!") and "Raw Data" in notificationData.outputData["Fields"] and len(notificationData.outputData["Fields"]) == 1:

                                    parseFailureNotice = "A(n) {type} notification for {corp} failed to parse when being formatted for delivery to the {channel} channel of the {server} {platform} server.".format(
                                        type=eachNotification["type"],
                                        corp=relayCorp,
                                        channel=relaychannel,
                                        server=relayserver,
                                        platform=relaytype
                                    )

                                    print(parseFailureNotice)

                                    makeLogEntry(sq1Database, "Notification Parse Failure", parseFailureNotice)

                                sender = Terminus(notificationData.outputData, relaytype, relayurl)
                                wasSent = sender.send(2)

                                if wasSent:

                                    sentNotice = "A(n) {type} notification was sent for {corp} to the {channel} channel of the {server} {platform} server.".format(
                                        type=eachNotification["type"],
                                        corp=relayCorp,
                                        channel=relaychannel,
                                        server=relayserver,
                                        platform=relaytype
                                    )

                                    print(sentNotice)

                                    makeLogEntry(sq1Database, "Relay Sent", sentNotice)

                                    time.sleep(1)

                                else:

                                    failureNotice = "A(n) {type} notification failed to send for {corp} to the {channel} channel of the {server} {platform} server.".format(
                                        type=eachNotification["type"],
                                        corp=relayCorp,
                                        channel=relaychannel,
                                        server=relayserver,
                                        platform=relaytype
                                    )

                                    print(failureNotice)

                                    makeLogEntry(sq1Database, "Unknown Relay Error", failureNotice)

                                registerNotification(sq1Database, eachNotification["notification_id"], relayid, eachNotification["type"], notificationTimestamp)

                relayCursor.close()
                currentCorporation.progressStagger()

        initialCursor.close()

    except:

        traceback.print_exc()

        error = traceback.format_exc()

        makeLogEntry(sq1Database, "Unknown Relay Error", error)

    sq1Database.close()

    print("\n[{Time}] Run Concluded!".format(Time=getTimeMark()))

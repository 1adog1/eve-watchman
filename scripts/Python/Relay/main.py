from Notifications import Notification
from Timers import Timers
from EntityControl import Character, Corporation
from Terminus import RelayTerminus
from Terminus import TimerTerminus
import ESI

import time
import json
import traceback

from datetime import datetime, timezone

import mysql.connector as DatabaseConnector

from OverhaulConfig import Config

configVariables = Config()

def getTimeMark():

        currentTime = datetime.now(timezone.utc)
        return currentTime.strftime("%d %B, %Y - %H:%M:%S EVE")

def makeLogEntry(passedDatabase, logType, logStatement):

    loggingCursor = passedDatabase.cursor(buffered=True)

    logInsert = "INSERT INTO logs (timestamp, type, actor, details) VALUES (%s, %s, %s, %s)"
    loggingCursor.execute(logInsert, (int(time.time()), logType, "[Relay]", logStatement))
    passedDatabase.commit()

    loggingCursor.close()

def registerNotification(passedDatabase, tableType, registerID, registerRelayID, registerType, registerTimestamp):

    registrationCursor = passedDatabase.cursor(buffered=True)

    if tableType == "Notification":
        notificationInsert = "INSERT INTO notifications (id, relayid, type, timestamp) VALUES (%s, %s, %s, %s)"
    elif tableType == "Timer":
        notificationInsert = "INSERT INTO timers (id, timerboardid, type, timestamp) VALUES (%s, %s, %s, %s)"
    registrationCursor.execute(notificationInsert, (registerID, registerRelayID, registerType, registerTimestamp))
    passedDatabase.commit()

    registrationCursor.close()

def run():

    print("[{Time}] Starting Run...\n".format(Time=getTimeMark()))

    sq1Database = DatabaseConnector.connect(
        user=configVariables.database.username,
        password=configVariables.database.password,
        host=configVariables.database.server,
        port=int(configVariables.database.port),
        database=configVariables.database.name
    )

    try:

        ESIAuth = ESI.AuthHandler(
            sq1Database,
            configVariables.eve_auth.client_id,
            configVariables.eve_auth.client_secret,
            "Relay"
        )

        initialCursor = sq1Database.cursor(buffered=True)

        initialStatement = "SELECT DISTINCT corporationid FROM relaycharacters UNION SELECT corporationid FROM staggering"
        initialCursor.execute(initialStatement)

        for eachID, in initialCursor:

            currentCorporation = Corporation(eachID, configVariables.eve_auth.client_id, configVariables.eve_auth.client_secret, configVariables.versioning, sq1Database)

            if currentCorporation.valids and int(time.time()) >= currentCorporation.nextrun:

                print("[{Time}] Pulling notifications from {name} ({corp}){alliance}...".format(
                    Time=getTimeMark(),
                    name=currentCorporation.characters[currentCorporation.valids[currentCorporation.currentposition]].name,
                    corp=currentCorporation.characters[currentCorporation.valids[currentCorporation.currentposition]].corporation,
                    alliance=((" [" + currentCorporation.characters[currentCorporation.valids[currentCorporation.currentposition]].alliance + "]") if currentCorporation.characters[currentCorporation.valids[currentCorporation.currentposition]].alliance is not None else "")
                ))

                newNotifications = currentCorporation.characters[currentCorporation.valids[currentCorporation.currentposition]].getCharacterNotifications()
                setPlaceholders = ", ".join(["%s" for x in range(len(newNotifications) + 1)])
                setValues = tuple([0] + [int(x["notification_id"]) for x in newNotifications])

                if configVariables.timerboard_options.enabled:

                    timerboardCursor = sq1Database.cursor(buffered=True)

                    timerStatement = "SELECT id, type, url, token, whitelist, timestamp, corporationid, corporationname FROM timerboards WHERE corporationid=%s"
                    timerboardCursor.execute(timerStatement, (eachID,))

                    for timerboardid, timerboardtype, timerboardurl, timerboardtoken, timerboardwhitelist, timerboardtime, relayCorpID, relayCorp in timerboardCursor:

                        print("[{Time}] Loading known {Corporation} Notifications for {Type} Timerboard".format(
                            Time=getTimeMark(),
                            Corporation=relayCorp,
                            Type=timerboardtype
                        ))

                        notificationWhitelist = json.loads(timerboardwhitelist)

                        timerCursor = sq1Database.cursor(buffered=True)

                        timerStatement = "SELECT DISTINCT id FROM timers WHERE timerboardid=%s AND id IN (" + setPlaceholders + ") ORDER BY id"
                        timerCursor.execute(timerStatement, ((timerboardid,) + setValues))

                        alreadyPosted = [timerid for timerid, in timerCursor]

                        timerCursor.close()

                        print("[{Time}] Looping through notifications...".format(Time=getTimeMark()))
                        for eachNotification in newNotifications:

                            notificationTimestamp = int(datetime.strptime(eachNotification["timestamp"], "%Y-%m-%dT%H:%M:%SZ").replace(tzinfo=timezone.utc).timestamp())

                            if notificationTimestamp >= timerboardtime and eachNotification["type"] in notificationWhitelist and int(eachNotification["notification_id"]) not in alreadyPosted and "text" in eachNotification:

                                timerData = Timers(
                                    sq1Database,
                                    configVariables.versioning,
                                    eachNotification["type"],
                                    notificationTimestamp,
                                    eachNotification["text"],
                                    relayCorpID, 
                                    timerboardtype,
                                    ESIAuth.getAccessToken(currentCorporation.valids[currentCorporation.currentposition], retries=1)
                                )

                                print("[{Time}] Formatting...".format(Time=getTimeMark()))
                                timerData.formatTimer()
                                print("[{Time}] Formatting Complete.".format(Time=getTimeMark()))

                                if timerData.parseFailure:

                                    parseFailureNotice = "A(n) {type} notification for {corp} failed to parse when being formatted for posting to a {platform} timerboard.".format(
                                        type=eachNotification["type"],
                                        corp=relayCorp,
                                        platform=timerboardtype
                                    )

                                    print(parseFailureNotice)

                                    makeLogEntry(sq1Database, "Notification Parse Failure", parseFailureNotice)

                                    registerNotification(sq1Database, "Timer", eachNotification["notification_id"], timerboardid, eachNotification["type"], notificationTimestamp)

                                elif timerData.shouldItPost():

                                    print("[{Time}] Approved to post, doing so...".format(Time=getTimeMark()))

                                    postData = timerData.getPostData()

                                    poster = TimerTerminus(postData, timerboardtype, timerboardurl, timerboardtoken)
                                    wasPosted = poster.post(2)

                                    if wasPosted:

                                        postedNotice = "A(n) {type} notification was posted for {corp} to a {platform} timerboard.".format(
                                            type=eachNotification["type"],
                                            corp=relayCorp,
                                            platform=timerboardtype
                                        )

                                        print(postedNotice)

                                        makeLogEntry(sq1Database, "Timer Created", postedNotice)

                                        time.sleep(1)

                                    else:

                                        failureNotice = "A(n) {type} notification failed to post for {corp} to a {platform} timerboard.".format(
                                            type=eachNotification["type"],
                                            corp=relayCorp,
                                            platform=timerboardtype
                                        )

                                        print(failureNotice)

                                        makeLogEntry(sq1Database, "Unknown Relay Error", failureNotice)

                                    registerNotification(sq1Database, "Timer", eachNotification["notification_id"], timerboardid, eachNotification["type"], notificationTimestamp)

                                else:

                                    print("[{Time}] Not approved to post, suppressing...".format(Time=getTimeMark()))

                                    suppressionNotice = "A(n) {type} notification for {corp} bound for a {platform} timerboard was suppressed.".format(
                                        type=eachNotification["type"],
                                        corp=relayCorp,
                                        platform=timerboardtype
                                    )

                                    print(suppressionNotice)

                                    makeLogEntry(sq1Database, "Timer Suppressed", suppressionNotice)

                                    registerNotification(sq1Database, "Timer", eachNotification["notification_id"], timerboardid, eachNotification["type"], notificationTimestamp)

                    timerboardCursor.close()

                relayCursor = sq1Database.cursor(buffered=True)

                relayStatement = "SELECT relays.id, relays.type, relays.url, relays.pingtype, relays.whitelist, relays.timestamp, relays.corporationid, relays.corporationname, channels.name, servers.name FROM relays LEFT JOIN channels ON relays.channelid = channels.id AND relays.type = channels.type LEFT JOIN servers ON channels.serverid = servers.id AND channels.type = servers.type WHERE relays.corporationid=%s"
                relayCursor.execute(relayStatement, (eachID,))

                for relayid, relaytype, relayurl, relayping, relaywhitelist, relaytime, relayCorpID, relayCorp, relaychannel, relayserver in relayCursor:

                    print("[{Time}] Loading known {Corporation} Notifications for {Channel} ({Server})".format(
                        Time=getTimeMark(),
                        Corporation=relayCorp,
                        Channel=relaychannel,
                        Server=relayserver
                    ))

                    notificationWhitelist = json.loads(relaywhitelist)

                    notificationsCursor = sq1Database.cursor(buffered=True)

                    notificationsStatement = "SELECT DISTINCT id FROM notifications WHERE relayid=%s AND id IN (" + setPlaceholders + ") ORDER BY id"
                    notificationsCursor.execute(notificationsStatement, ((relayid,) + setValues))

                    alreadySent = [notificationid for notificationid, in notificationsCursor]

                    notificationsCursor.close()

                    print("[{Time}] Looping through notifications...".format(Time=getTimeMark()))
                    for eachNotification in newNotifications:

                        notificationTimestamp = int(datetime.strptime(eachNotification["timestamp"], "%Y-%m-%dT%H:%M:%SZ").replace(tzinfo=timezone.utc).timestamp())

                        if notificationTimestamp >= relaytime and eachNotification["type"] in notificationWhitelist and int(eachNotification["notification_id"]) not in alreadySent and "text" in eachNotification:

                            notificationData = Notification(
                                sq1Database,
                                configVariables.versioning,
                                eachNotification["type"],
                                notificationTimestamp,
                                eachNotification["text"],
                                relayCorpID, 
                                relayCorp,
                                relaytype,
                                relayping,
                                ESIAuth.getAccessToken(currentCorporation.valids[currentCorporation.currentposition], retries=1)
                            )

                            print("[{Time}] Formatting...".format(Time=getTimeMark()))
                            notificationData.formatForRelaying()
                            print("[{Time}] Formatting Complete.".format(Time=getTimeMark()))

                            if notificationData.shouldItRelay():

                                print("[{Time}] Approved to relay, sending...".format(Time=getTimeMark()))

                                if notificationData.parseFailure:

                                    parseFailureNotice = "A(n) {type} notification for {corp} failed to parse when being formatted for delivery to the {channel} channel of the {server} {platform} server.".format(
                                        type=eachNotification["type"],
                                        corp=relayCorp,
                                        channel=relaychannel,
                                        server=relayserver,
                                        platform=relaytype
                                    )

                                    print(parseFailureNotice)

                                    makeLogEntry(sq1Database, "Notification Parse Failure", parseFailureNotice)

                                sender = RelayTerminus(notificationData.outputData, relaytype, relayurl)
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

                                registerNotification(sq1Database, "Notification", eachNotification["notification_id"], relayid, eachNotification["type"], notificationTimestamp)

                            else:

                                print("[{Time}] Not approved to relay, suppressing...".format(Time=getTimeMark()))

                                suppressionNotice = "A(n) {type} notification for {corp} bound for the {channel} channel of the {server} {platform} server was suppressed.".format(
                                    type=eachNotification["type"],
                                    corp=relayCorp,
                                    channel=relaychannel,
                                    server=relayserver,
                                    platform=relaytype
                                )

                                print(suppressionNotice)

                                makeLogEntry(sq1Database, "Relay Suppressed", suppressionNotice)

                                registerNotification(sq1Database, "Notification", eachNotification["notification_id"], relayid, eachNotification["type"], notificationTimestamp)

                relayCursor.close()

                print("[{Time}] Done Pulling notifications from {name} ({corp}){alliance}.\n".format(
                    Time=getTimeMark(),
                    name=currentCorporation.characters[currentCorporation.valids[currentCorporation.currentposition]].name,
                    corp=currentCorporation.characters[currentCorporation.valids[currentCorporation.currentposition]].corporation,
                    alliance=((" [" + currentCorporation.characters[currentCorporation.valids[currentCorporation.currentposition]].alliance + "]") if currentCorporation.characters[currentCorporation.valids[currentCorporation.currentposition]].alliance is not None else "")
                ))

                currentCorporation.progressStagger()

        initialCursor.close()

    except:

        traceback.print_exc()

        error = traceback.format_exc()

        makeLogEntry(sq1Database, "Unknown Relay Error", error)

    sq1Database.close()

    print("\n[{Time}] Run Concluded!".format(Time=getTimeMark()))

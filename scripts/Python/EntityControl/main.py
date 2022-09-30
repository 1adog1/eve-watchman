import ESI
import json
import time
from datetime import datetime, timezone

def getEntityTimeMark():

        currentTime = datetime.now(timezone.utc)
        return currentTime.strftime("%d %B, %Y - %H:%M:%S EVE")

class Corporation:

    def __init__(self, id, client_id, client_secret, database_connection):

        self.id = id
        self.name = None

        self.database = database_connection
        self.client_id = client_id
        self.client_secret = client_secret

        self.initialized = False
        self.frequency = 0
        self.nextrun = 0
        self.currentposition = 0
        self.nextcleanup = 0

        self.characters = {}

        self.original_valids = []
        self.valids = []

        self.getStagger()
        self.original_valids.sort()

        self.pullCharacters()
        self.valids.sort()

        if self.initialized and int(time.time()) >= self.nextcleanup and self.currentposition == 0:

            print("[{Time}] Starting Cleanup of {Corporation}...".format(Time=getEntityTimeMark(), Corporation=self.name))

            for eachID in self.characters:

                print("[{Time}] Started Updating Stats for {Character}...".format(Time=getEntityTimeMark(), Character=self.characters[eachID].name))

                self.characters[eachID].setupESI()
                self.characters[eachID].getUpdatedInfo()

                if eachID in self.valids and self.characters[eachID].corporation_id != self.id:

                    self.valids.remove(eachID)

                print("[{Time}] Finished Updating Stats for {Character}.".format(Time=getEntityTimeMark(), Character=self.characters[eachID].name))

            self.progressCleanup()

            print("[{Time}] Finished Cleanup of {Corporation}.\n".format(Time=getEntityTimeMark(), Corporation=self.name))

        if set(self.valids) != set(self.original_valids):

            if not self.valids:

                self.deleteStagger()

            elif not self.original_valids:

                self.frequency = int(600 / len(self.valids))
                self.nextrun = int(time.time())
                self.currentposition = 0
                self.nextcleanup = (int(time.time()) + 3600)
                self.createStagger()

            else:

                self.frequency = int(600 / len(self.valids))
                self.nextrun = int(time.time() + self.frequency)
                self.currentposition = 0
                self.nextcleanup = (int(time.time()) + 3600)
                self.updateStagger()

    def getStagger(self):

        staggerCursor = self.database.cursor(buffered=True)

        staggerStatement = "SELECT characters, frequency, nextrun, currentposition, nextcleanup FROM staggering WHERE corporationid=%s"
        staggerCursor.execute(staggerStatement, (self.id, ))

        for characters, frequency, nextrun, currentposition, nextcleanup in staggerCursor:

            self.initialized = True
            self.original_valids = json.loads(characters)
            self.frequency = frequency
            self.nextrun = nextrun
            self.currentposition = currentposition
            self.nextcleanup = nextcleanup

        staggerCursor.close()

    def pullCharacters(self):

        pullCursor = self.database.cursor(buffered=True)

        pullStatement = "SELECT id, corporationname FROM relaycharacters WHERE corporationid=%s"
        pullCursor.execute(pullStatement, (self.id, ))

        for eachID, corpName in pullCursor:

            self.name = corpName

            self.characters[eachID] = Character(eachID, self.client_id, self.client_secret, self.database)

            if self.characters[eachID].valid:

                self.valids.append(eachID)

        pullCursor.close()

    def progressStagger(self):

        progressCursor = self.database.cursor(buffered=True)

        self.currentposition = (self.currentposition + 1) % len(self.valids)
        self.nextrun = (int(time.time()) + self.frequency)

        progressRequest = "UPDATE staggering SET nextrun=%s, currentposition=%s WHERE corporationid=%s"
        progressCursor.execute(progressRequest, (
            self.nextrun,
            self.currentposition,
            self.id
        ))
        self.database.commit()

        progressCursor.close()

    def progressCleanup(self):

        cleanupCursor = self.database.cursor(buffered=True)

        cleanupRequest = "UPDATE staggering SET nextcleanup=%s WHERE corporationid=%s"
        cleanupCursor.execute(cleanupRequest, (
            (int(time.time()) + 3600),
            self.id
        ))
        self.database.commit()

        cleanupCursor.close()

    def createStagger(self):

        createCursor = self.database.cursor(buffered=True)

        createRequest = "INSERT INTO staggering (corporationid, characters, frequency, nextrun, currentposition, nextcleanup) VALUES (%s, %s, %s, %s, %s, %s)"
        createCursor.execute(createRequest, (
            self.id,
            json.dumps(self.valids),
            self.frequency,
            self.nextrun,
            self.currentposition,
            self.nextcleanup
        ))
        self.database.commit()

        logStatement = "{Name} Has Been Created. \nCharacter(s) Gained: {Lost}".format(
            Name = self.name,
            Lost = ", ".join(map(str, self.valids))
        )

        logUpdate = "INSERT INTO logs (timestamp, type, actor, details) VALUES (%s, %s, %s, %s)"
        createCursor.execute(logUpdate, (int(time.time()), "Relay Corporation Added", "[Entity Control]", logStatement))
        self.database.commit()

        createCursor.close()

    def updateStagger(self):

        updateCursor = self.database.cursor(buffered=True)

        updateRequest = "UPDATE staggering SET characters=%s, frequency=%s, nextrun=%s, currentposition=%s, nextcleanup=%s WHERE corporationid=%s"
        updateCursor.execute(updateRequest, (
            json.dumps(self.valids),
            self.frequency,
            self.nextrun,
            self.currentposition,
            self.nextcleanup,
            self.id
        ))
        self.database.commit()

        logStatement = "{Name}'s Characters Have Changed. \nGained: {New} \nLost: {Lost}".format(
            Name = self.name,
            New = ", ".join(map(str, list(set(self.valids) - set(self.original_valids)))),
            Lost = ", ".join(map(str, list(set(self.original_valids) - set(self.valids))))
        )

        logUpdate = "INSERT INTO logs (timestamp, type, actor, details) VALUES (%s, %s, %s, %s)"
        updateCursor.execute(logUpdate, (int(time.time()), "Relay Corporation Updated", "[Entity Control]", logStatement))
        self.database.commit()

        updateCursor.close()

    def deleteStagger(self):

        deleteCursor = self.database.cursor(buffered=True)

        deleteRequest = "DELETE FROM staggering WHERE corporationid=%s"
        deleteCursor.execute(deleteRequest, (self.id, ))
        self.database.commit()

        logStatement = "{Name} Has Lost All Characters. \nCharacter(s) Lost: {Lost}".format(
            Name = self.name,
            Lost = ", ".join(map(str, self.original_valids))
        )

        logUpdate = "INSERT INTO logs (timestamp, type, actor, details) VALUES (%s, %s, %s, %s)"
        deleteCursor.execute(logUpdate, (int(time.time()), "Relay Corporation Deleted", "[Entity Control]", logStatement))
        self.database.commit()

        deleteCursor.close()

class Character:

    def __init__(self, id, client_id, client_secret, database_connection):

        self.id = id

        self.database = database_connection
        self.client_id = client_id
        self.client_secret = client_secret

        self.valid = False

        self.name = None
        self.corporation = None
        self.corporation_id = None
        self.alliance = None
        self.alliance_id = None
        self.roles = []

        self.pullFromDatabase()
        self.setupESI()

    def pullFromDatabase(self):

        pullCursor = self.database.cursor(buffered=True)

        pullStatement = "SELECT name, corporationid, corporationname, allianceid, alliancename, status, roles FROM relaycharacters WHERE id=%s"
        pullCursor.execute(pullStatement, (self.id, ))

        for name, corporationid, corporationname, allianceid, alliancename, status, roles in pullCursor:

            self.valid = (status == "Valid")

            self.name = name
            self.corporation = corporationname
            self.corporation_id = corporationid
            self.alliance = alliancename
            self.alliance_id = allianceid
            self.roles = json.loads(roles)

        pullCursor.close()

    def setupESI(self):

        ESIAuth = ESI.AuthHandler(
            self.database,
            self.client_id,
            self.client_secret,
            "Relay"
        )

        self.access_token = ESIAuth.getAccessToken(self.id, retries=1)

        if self.valid and not self.access_token:

            self.updateDatabase(
                "Status",
                [
                    {"Variable": "status", "New": "Invalid", "Old": "Valid"}
                ]
            )
            self.valid = False

        elif not self.valid and self.access_token:

            self.updateDatabase(
                "Status",
                [
                    {"Variable": "status", "New": "Valid", "Old": "Invalid"}
                ]
            )
            self.valid = True

        self.ESIHandler = ESI.Handler(
            self.database,
            self.access_token
        )

    def getCharacterNotifications(self):

        notificationsCall = self.ESIHandler.call("/characters/{character_id}/notifications/", character_id=self.id, retries=1)

        if notificationsCall["Success"]:

            return notificationsCall["Data"]

        else:

            print("ESI Call Failure while getting notifications for {name}.".format(name=self.name))
            return []

    def getUpdatedInfo(self):

        self.new_name = None
        self.new_corporation = None
        self.new_corporation_id = None
        self.new_alliance = None
        self.new_alliance_id = None
        self.new_roles = []


        #Get all the current data from ESI
        affiliationCall = self.ESIHandler.call("/characters/affiliation/", characters=[self.id], retries=1)

        if affiliationCall["Success"]:

            namesToCheck = []

            affiliationData = affiliationCall["Data"][0]

            namesToCheck.append(int(affiliationData["character_id"]))

            self.new_corporation_id = int(affiliationData["corporation_id"])
            namesToCheck.append(int(affiliationData["corporation_id"]))

            if "alliance_id" in affiliationData:

                self.new_alliance_id = int(affiliationData["alliance_id"])
                namesToCheck.append(int(affiliationData["alliance_id"]))

            namesCall = self.ESIHandler.call("/universe/names/", ids=namesToCheck, retries=1)

            if namesCall["Success"]:

                for eachName in namesCall["Data"]:

                    if eachName["category"] == "character":
                        self.new_name = eachName["name"]
                    if eachName["category"] == "corporation":
                        self.new_corporation = eachName["name"]
                    if eachName["category"] == "alliance":
                        self.new_alliance = eachName["name"]

            else:

                print("ESI Call Failure while getting names for {name}. Halting Update.".format(name=self.name))
                return

        else:

            print("ESI Call Failure while getting affiliation for {name}. Halting Update.".format(name=self.name))
            return


        if self.new_name is None or self.new_corporation_id is None or self.new_corporation is None:

            print("During an update of {name}, one or more of the following critical attributes were set to None. Halting Update. \nName: {new_name} \nCorporation ID: {new_corp_id} \nCorporation Name: {new_corp_name}".format(
                name=self.name,
                new_name=self.new_name,
                new_corp_id=self.new_corporation_id,
                new_corp_name=self.new_corporation
            ))
            return


        #Update entries in the database as necessary
        if self.name != self.new_name:

            self.updateDatabase(
                "Name",
                [
                    {"Variable": "name", "New": self.new_name, "Old": self.name}
                ]
            )
            self.name = self.new_name

        if self.corporation_id != self.new_corporation_id:

            self.updateDatabase(
                "Corporation",
                [
                    {"Variable": "corporationname", "New": self.new_corporation, "Old": self.corporation},
                    {"Variable": "corporationid", "New": self.new_corporation_id, "Old": self.corporation_id}
                ]
            )
            self.corporation_id = self.new_corporation_id
            self.corporation = self.new_corporation

        elif self.corporation != self.new_corporation:

            self.updateDatabase(
                "Corporation Name",
                [
                    {"Variable": "corporationname", "New": self.new_corporation, "Old": self.corporation}
                ]
            )
            self.corporation = self.new_corporation

        if self.alliance_id != self.new_alliance_id:

            self.updateDatabase(
                "Alliance",
                [
                    {"Variable": "alliancename", "New": self.new_alliance, "Old": self.alliance},
                    {"Variable": "allianceid", "New": self.new_alliance_id, "Old": self.alliance_id}
                ]
            )
            self.alliance_id = self.new_alliance_id
            self.alliance = self.new_alliance

        elif self.alliance != self.new_alliance:

            self.updateDatabase(
                "Alliance Name",
                [
                    {"Variable": "alliancename", "New": self.new_alliance, "Old": self.alliance}
                ]
            )
            self.alliance = self.new_alliance

        if not self.valid:

            return

        rolesCall = self.ESIHandler.call("/characters/{character_id}/roles/", character_id=self.id, retries=1)

        if rolesCall["Success"]:

            self.new_roles = rolesCall["Data"]["roles"]

        else:

            print("ESI Call Failure while getting roles for {name}. Halting Update.".format(name=self.name))
            return

        if set(self.roles) != set(self.new_roles):

            self.updateDatabase(
                "Roles",
                [
                    {"Variable": "roles", "New": json.dumps(self.new_roles), "Old": json.dumps(self.roles)}
                ]
            )
            self.roles = self.new_roles

    def updateDatabase(self, updatedTitle, updatedVariables):

        updateCursor = self.database.cursor(buffered=True)

        for index, eachVar in enumerate(updatedVariables):

            varUpdate = "UPDATE relaycharacters SET " + eachVar["Variable"] + "=%s WHERE id=%s"
            updateCursor.execute(varUpdate, (eachVar["New"], self.id))
            self.database.commit()

            if index == 0:

                logStatement = "{Name}'s {Type} has changed from {Old} to {New}.".format(
                    Name = self.name,
                    Type = updatedTitle,
                    Old = eachVar["Old"],
                    New = eachVar["New"]
                ) if updatedTitle != "Roles" else "{Name}'s Roles Have Changed. \nGained: {New} \nLost: {Lost}".format(
                    Name = self.name,
                    New = ", ".join(list(set(json.loads(eachVar["New"])) - set(json.loads(eachVar["Old"])))),
                    Lost = ", ".join(list(set(json.loads(eachVar["Old"])) - set(json.loads(eachVar["New"]))))
                )

                logUpdate = "INSERT INTO logs (timestamp, type, actor, details) VALUES (%s, %s, %s, %s)"
                updateCursor.execute(logUpdate, (int(time.time()), "Relay Character State Change", "[Entity Control]", logStatement))
                self.database.commit()

        updateCursor.close()

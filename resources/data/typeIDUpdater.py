import requests
import time
import datetime
import json

def dataFile():
	import inspect
	import os

	filename = inspect.getframeinfo(inspect.currentframe()).filename
	path = os.path.dirname(os.path.abspath(filename))
	
	dataLocation = str(path)
	
	return(dataLocation)

newTypeIDList = {}
unknownIDs = []

firstURL = "https://esi.evetech.net/latest/universe/types/?datasource=tranquility&page="
secondURL = "https://esi.evetech.net/latest/universe/names/?datasource=tranquility"

counter = 1
removedCounter = 0

print("[" + str(datetime.datetime.now()) + "] Searching for Type IDs...")

while True:
    esiCall = requests.get(firstURL + str(counter))
    foundList = esiCall.json()
    
    if foundList == []:
        break
    
    for ids in foundList:
        unknownIDs.append(int(ids))
    
    counter += 1
    
print("[" + str(datetime.datetime.now()) + "] " + str(len(unknownIDs)) + " IDs found in " + str(counter) + " pages... Checking for known IDs.\n\n")
    
try:   
    with open(dataFile() + "/TypeIDs.json") as knownData:
        typeIDList = json.load(knownData)
                
except:
    typeIDList = {}
    
typeIDList = {int(key):typeIDList[key] for key in typeIDList}
    
for ID in unknownIDs[:]:
    if ID in typeIDList:
        newTypeIDList[ID] = str(typeIDList[ID])
        while ID in unknownIDs: 
            unknownIDs.remove(ID)
            removedCounter += 1
        
print("[" + str(datetime.datetime.now()) + "] " + str(removedCounter) + " IDs already known, " +  str(len(unknownIDs)) + " remaining IDs... Checking unknown IDs.\n\n")
    
knownCounter = 0
idsToCheck = []
for ID in unknownIDs:
    knownCounter += 1
    errorCounter = 0
    
    idsToCheck.append(int(ID))
        
    if knownCounter % 1000 == 0 or knownCounter == len(unknownIDs):
        print("[" + str(datetime.datetime.now()) + "] " + str(knownCounter) + " TypeIDs Checked...")
        
        dataToCheck = json.dumps(idsToCheck)
        headers = {"accept":"application/json", "Content-Type":"application/json"}
        
        while True:
            try:
                esiCall = requests.post(secondURL, data=dataToCheck, headers=headers)
                foundInfo = json.loads(esiCall.text)
                
                for eachNew in foundInfo:
                    newTypeIDList[int(eachNew["id"])] = str(eachNew["name"])
                
                errorCounter = 0
                
                idsToCheck = []
                
                break
            except:
                if errorCounter < 5:
                    print("[" + str(datetime.datetime.now()) + "] Error in IDs... Trying Again.")
                    errorCounter += 1
                    time.sleep(1)
                else:
                    print("[" + str(datetime.datetime.now()) + "] Error in IDa could not be resolved, excluding from results.")
                    break
                
print("[" + str(datetime.datetime.now()) + "] " + str(knownCounter) + " TypeIDs Checked...")    

print("TypeID List now contains " + str(len(newTypeIDList)) + " items.")
    
with open(dataFile() + "/TypeIDs.json", "w", encoding="utf-8", errors="replace") as writeFile:
    json.dump(dict(sorted(newTypeIDList.items())), writeFile)
    
print("File Update Successful!")

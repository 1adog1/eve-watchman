import base64
import requests
import json
import time

class AuthHandler:

    authURL = "https://login.eveonline.com/v2/oauth/token"

    def __init__(self, databaseConnection, client_id, client_secret, login_type = "Default"):
        
        self.databaseConnection = databaseConnection
        
        self.login_type = login_type
        
        headerBase = str(client_id) + ":" + str(client_secret)
        self.authHeader = "Basic " + base64.urlsafe_b64encode(headerBase.encode("utf-8")).decode()
    
    def getAccessToken(self, character_id, retries = 0):
        
        tokenData = self.pullToken(character_id)
        
        if tokenData["Status"] == "Success":
        
            return tokenData["Access Token"]
        
        elif tokenData["Status"] == "Out of Date":
        
            return self.refreshToken(character_id, tokenData["Refresh Token"], retries)
        
        elif tokenData["Status"] == "Fail":
            
            return False
    
    def pullToken(self, character_id):
        
        returnData = {"Status": "Fail", "Access Token": None, "Refresh Token": None}
        
        databaseCursor = self.databaseConnection.cursor(buffered=True)
        
        timeToCheck = int(time.time()) + 15
        
        pullStatement = "SELECT DISTINCT accesstoken, refreshtoken, recheck FROM refreshtokens WHERE type=%s AND characterid=%s"
        databaseCursor.execute(pullStatement, (self.login_type, character_id))
        
        for pulledAccessToken, pulledRefreshToken, recheckTime in databaseCursor:
        
            returnData["Refresh Token"] = pulledRefreshToken
            
            if recheckTime <= timeToCheck:
                
                returnData["Status"] = "Out of Date"
                
            else:
                
                returnData["Access Token"] = pulledAccessToken
                returnData["Status"] = "Success"
        
        databaseCursor.close()
        
        return returnData
    
    def updateRefreshToken(self, character_id, refresh_token, access_token, recheck):
        
        databaseCursor = self.databaseConnection.cursor(buffered=True)
        
        updateStatement = "UPDATE refreshtokens SET refreshtoken=%s, accesstoken=%s, recheck=%s WHERE type=%s AND characterid=%s"
        databaseCursor.execute(updateStatement, (refresh_token, access_token, recheck, self.login_type, character_id))
        
        self.databaseConnection.commit()
        databaseCursor.close()
    
    def refreshToken(self, character_id, old_token, retries = 0):
        
        headers = {
            "Authorization": self.authHeader, 
            "Content-Type": "application/x-www-form-urlencoded", 
            "Host": "login.eveonline.com"
        }
        
        payload = {
            "grant_type": "refresh_token", 
            "refresh_token": old_token
        }
        
        for retryCounter in range(retries + 1):
            
            request = requests.post(
                url = self.authURL, 
                data = payload, 
                headers = headers
            )
            
            if request.status_code == requests.codes.ok:
                
                response = json.loads(request.text)
                
                access_array = response["access_token"].split(".")
                raw_access_payload = access_array[1] + "==="
                
                access_payload = json.loads(
                    base64.urlsafe_b64decode(
                        raw_access_payload.encode("utf-8")
                    ).decode()
                )
                
                self.updateRefreshToken(character_id, response["refresh_token"], response["access_token"], access_payload["exp"])
                
                return response["access_token"]
            
            elif request.status_code == 400:
                
                return False
            
            elif retryCounter == retries:
                
                return False
    
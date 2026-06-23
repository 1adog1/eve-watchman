import hashlib
import json
import base64
import time
import requests
import email

class Base:

    defaultSuccessCodes = [200, 204]
    defaultCompatibilityDate = "2026-06-01"

    def hashRequest(self, url, method, payload, accessToken):
    
        hashingDict = {
            "URL": url, 
            "Method": method, 
            "Payload": payload, 
            "Subject": self.unpackAccessToken(accessToken)["Payload"]["sub"] if accessToken is not None else None
        }
        
        hashingData = str.encode(
            json.dumps(hashingDict, separators=(",", ":"))
        )
        
        return hashlib.sha256(hashingData).hexdigest()
        
    def unpackAccessToken(self, accessToken):
        
        if accessToken is not None:

            accessArray = accessToken.split(".")
            accessHeader = json.loads(base64.b64decode(accessArray[0] + "===").decode("utf-8"))
            accessPayload = json.loads(base64.b64decode(accessArray[1] + "===").decode("utf-8"))
            accessSignature = accessArray[2]

            return {
                "Token": accessToken,
                "Header": accessHeader,
                "Payload": accessPayload,
                "Signature": accessSignature
            }

        else:

            return {
                "Token": None,
                "Header": None,
                "Payload": None,
                "Signature": None
            }

    def cleanupCache(self):
    
        databaseCursor = self.databaseConnection.cursor(buffered=True)
    
        cleanupStatement = "DELETE FROM esicache WHERE expiration <= %s"
        currentTime = int(time.time())
        
        databaseCursor.execute(cleanupStatement, (currentTime,))
        
        self.databaseConnection.commit()
        databaseCursor.close()
        
    def checkCache(self, endpoint, hash):
    
        databaseCursor = self.databaseConnection.cursor(buffered=True)
    
        checkStatement = "SELECT response FROM esicache WHERE endpoint=%s AND hash=%s AND expiration > %s"
        currentTime = int(time.time())
        
        databaseCursor.execute(checkStatement, (endpoint, hash, currentTime))
        
        result = False
        
        for (response, ) in databaseCursor:
        
            result = json.loads(response)
        
        databaseCursor.close()
        
        return result
        
    def populateCache(self, endpoint, hash, response, expires):

        response_to_save = json.dumps(response, separators=(",", ":"))
    
        databaseCursor = self.databaseConnection.cursor(buffered=True)
    
        insertStatement = "INSERT INTO esicache (endpoint, hash, expiration, response) VALUES (%s, %s, %s, %s)"
        
        databaseCursor.execute(insertStatement, (endpoint, hash, expires, response_to_save))
        
        self.databaseConnection.commit()
        databaseCursor.close()
        
    def makeRequest(
        self, 
        endpoint, 
        url, 
        method = "GET", 
        payload = None, 
        accessToken = None, 
        compatibilityDate = None,
        expectResponse = True, 
        successCodes = [], 
        cacheTime = 0, 
        retries = 0
    ):
    
        responseData = {"Success": False, "Data": [], "Status Code": None, "Headers": None}
    
        self.cleanupCache()
        
        cacheCheck = self.checkCache(
            endpoint,
            self.hashRequest(url, method, payload, accessToken)
        )
        
        if cacheCheck != False:
        
            responseData = cacheCheck
            
            return responseData
            
        else:
        
            for retryCounter in range(retries + 1):
            
                requestMethod = getattr(requests, method.lower())
                
                headers = {
                    "accept": "application/json",
                    "X-Compatibility-Date": (compatibilityDate if compatibilityDate is not None else self.defaultCompatibilityDate),
                    "X-User-Agent": self.userAgent
                }
                
                if accessToken is not None:
                
                    headers["Authorization"] = "Bearer " + accessToken
                    
                if payload is not None:
                
                    requestData = json.dumps(payload)
                    headers["Content-Type"] = "application/json"
                    
                else:
                
                    requestData = None
                
                request = requestMethod(
                    url = url, 
                    data = requestData, 
                    headers = headers
                )

                responseData["Status Code"] = request.status_code
                responseData["Headers"] = dict(request.headers)
                
                if request.status_code in (self.defaultSuccessCodes + successCodes):
                
                    responseData["Success"] = True
                    
                    if expectResponse:
                    
                        try:
                            responseData["Data"] = json.loads(request.text)
                        except:
                            pass
                        
                        if "Expires" in request.headers:
                        
                            expiryDatetime = email.utils.parsedate_to_datetime(request.headers["Expires"])
                            expiry = int(expiryDatetime.timestamp())
                        
                        else:
                        
                            expiry = int(time.time()) + cacheTime
                        
                        self.populateCache(
                            endpoint, 
                            self.hashRequest(url, method, payload, accessToken), 
                            responseData, 
                            expiry
                        )
                        
                    return responseData
                    
                elif retryCounter == retries:
                
                    responseData["Success"] = False
                    
                    if expectResponse:
                    
                        try:
                        
                            responseData["Data"] = json.loads(request.text)
                        
                        except:
                        
                            pass
                        
                    return responseData
            
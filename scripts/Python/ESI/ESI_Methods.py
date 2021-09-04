from ESI import ESI_Base

class Methods(ESI_Base.Base):

    esiURL = "https://esi.evetech.net/"
    
    def characters(self, arguments):
    
        return self.makeRequest(
            endpoint = "/characters/{character_id}/", 
            url = (self.esiURL + "latest/characters/" + str(arguments["character_id"]) + "/?datasource=tranquility"), 
            retries = (arguments["retries"] if "retries" in arguments else 0)
        )
        
    def character_locations(self, arguments):
    
        return self.makeRequest(
            endpoint = "/characters/{character_id}/location/", 
            url = (self.esiURL + "latest/characters/" + str(arguments["character_id"]) + "/location/?datasource=tranquility"), 
            accessToken = self.accessToken, 
            retries = (arguments["retries"] if "retries" in arguments else 0)
        )

    def character_affiliations(self, arguments):
    
        return self.makeRequest(
            endpoint = "/characters/affiliation/",
            url = (self.esiURL + "latest/characters/affiliation/?datasource=tranquility"), 
            method = "POST", 
            payload = arguments["characters"], 
            cacheTime = 3600, 
            retries = (arguments["retries"] if "retries" in arguments else 0)
        )
        
    def universe_names(self, arguments):
    
        return self.makeRequest(
            endpoint = "/universe/names/",
            url = (self.esiURL + "latest/universe/names/?datasource=tranquility"), 
            method = "POST", 
            payload = arguments["ids"], 
            cacheTime = 3600, 
            retries = (arguments["retries"] if "retries" in arguments else 0)
        )        
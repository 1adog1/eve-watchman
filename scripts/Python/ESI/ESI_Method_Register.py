from ESI import ESI_Methods

class MethodRegister(ESI_Methods.Methods):

    def initalizeMethodList(self):
    
        self.methodList = {}
        
        self.register(
            endpoint = "/characters/{character_id}/", 
            method = "characters",
            requiredArguments = ["character_id"]
        )
        
        self.register(
            endpoint = "/characters/{character_id}/location/", 
            method = "character_locations",
            requiredArguments = ["character_id"]
        )
        
        self.register(
            endpoint = "/characters/affiliation/", 
            method = "character_affiliations",
            requiredArguments = ["characters"]
        )
        
        self.register(
            endpoint = "/universe/names/", 
            method = "universe_names",
            requiredArguments = ["ids"]
        )
    
    def register(self, endpoint, method, requiredArguments):
    
        self.methodList[endpoint] = {"Name": method, "Required Arguments": requiredArguments}
    
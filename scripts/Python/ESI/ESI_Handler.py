from ESI import ESI_Method_Register

class Handler(ESI_Method_Register.MethodRegister):

    def __init__(self, databaseConnection, accessToken = None):
    
        self.databaseConnection = databaseConnection
        
        self.accessToken = accessToken
        
        self.initalizeMethodList()
        
    def call(self, endpoint, **arguments):
    
        if (
            endpoint in self.methodList and 
            callable(getattr(self, self.methodList[endpoint]["Name"], None))
        ):
        
            method = getattr(self, self.methodList[endpoint]["Name"])
            
            if (
                all(args in arguments for args in self.methodList[endpoint]["Required Arguments"])
            ):
            
                return method(arguments)
                
            else:
            
                raise TypeError("One or more required arguments was not passed for the " + self.methodList[endpoint]["Name"] + "() method.")
            
        else:
        
            raise NameError("The " + endpoint + " endpoint does not have a valid registered method.")
    
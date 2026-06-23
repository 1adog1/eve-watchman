from ESI import ESI_Method_Register

class Handler(ESI_Method_Register.MethodRegister):

    def __init__(self, databaseConnection, versionVariables, accessToken = None):
    
        self.databaseConnection = databaseConnection
        
        self.accessToken = accessToken
        
        self.initializeMethodList()
        self.buildUserAgent(versionVariables)

    def buildUserAgent(self, versionVariables):

        if versionVariables.app_name and versionVariables.app_name.strip():

            self.userAgent = "{app_name}/{app_version} ({contact_info}; +{app_github}) Project-Overhaul/{overhaul_version} (+{overhaul_github})".format(
                app_name = versionVariables.app_name,
                app_version = versionVariables.app_version,
                contact_info = versionVariables.client_contact_info,
                app_github = versionVariables.app_github,
                overhaul_version = versionVariables.overhaul_version,
                overhaul_github = versionVariables.overhaul_github
            )

        else:

            self.userAgent = "Project-Overhaul/{overhaul_version} ({contact_info}; +{overhaul_github})".format(
                overhaul_version = versionVariables.overhaul_version,
                contact_info = versionVariables.client_contact_info,
                overhaul_github = versionVariables.overhaul_github
            )

    def updateAccessToken(self, accessToken):

        self.accessToken = accessToken
        
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
    
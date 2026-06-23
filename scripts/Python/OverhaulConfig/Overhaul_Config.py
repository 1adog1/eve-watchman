__all__ = ["Config"]

import os
import configparser

from pathlib import Path

import mysql.connector as DatabaseConnector

# If you moved the /config directory, put its full path here:
CONFIG_DIRECTORY = None

def cleanup_config(variable):

    return str(variable).strip("\"").strip("'") if variable is not None else None

class Database:

    def __init__(self, server, port, username, password, name):

        self.server = cleanup_config(server)
        self.port = cleanup_config(port)
        self.username = cleanup_config(username)
        self.password = cleanup_config(password)
        self.name = cleanup_config(name)

class EveAuth:

    def __init__(
            self, 
            ClientID, 
            ClientSecret, 
            ClientScopes, 
            DefaultScopes, 
            ClientRedirect, 
            ClientContactInfo, 
            AuthType, 
            SuperAdmins
        ):

        self.client_id = cleanup_config(ClientID)
        self.client_secret = cleanup_config(ClientSecret)
        self.client_scopes = cleanup_config(ClientScopes)
        self.default_scopes = cleanup_config(DefaultScopes)
        self.client_redirect = cleanup_config(ClientRedirect)
        self.client_contact_info = cleanup_config(ClientContactInfo)
        self.auth_type = cleanup_config(AuthType)
        self.super_admins = cleanup_config(SuperAdmins).replace(" ", "").split(",")

class NeucoreAuth:

    def __init__(self, AppID, AppSecret, AppURL):

        self.app_id = cleanup_config(AppID)
        self.app_secret = cleanup_config(AppSecret)
        self.app_url = cleanup_config(AppURL)

class TimerboardOptions:

    def __init__(
        self,
        TimerboardsEnabled,
        ApprovedTimerboardTypes,
        ApprovedTimerboardDomains
    ):
        self.enabled = TimerboardsEnabled
        self.approved_types = cleanup_config(ApprovedTimerboardTypes).replace(" ", "").split(",")
        self.approved_domains = cleanup_config(ApprovedTimerboardDomains).replace(" ", "").split(",")

class Versioning:

    def __init__(
            self,
            AppName,
            AppMajorVersion,
            AppMinorVersion,
            AppPatchVersion,
            AppDelimiter,
            AppGithubLink,
            OverhaulMajorVersion,
            OverhaulMinorVersion,
            OverhaulPatchVersion,
            OverhaulDelimiter,
            OverhaulGithubLink,
            ClientContactInfo
        ):

        self.app_name = cleanup_config(AppName)
        self.app_version = cleanup_config(AppDelimiter).join([
            cleanup_config(AppMajorVersion), 
            cleanup_config(AppMinorVersion), 
            cleanup_config(AppPatchVersion)
        ])
        self.app_github = cleanup_config(AppGithubLink)
        self.overhaul_version = cleanup_config(OverhaulDelimiter).join([
            cleanup_config(OverhaulMajorVersion), 
            cleanup_config(OverhaulMinorVersion), 
            cleanup_config(OverhaulPatchVersion)
        ])
        self.overhaul_github = cleanup_config(OverhaulGithubLink)
        self.client_contact_info = cleanup_config(ClientContactInfo)

class Config:

    def __init__(self):

        config_directory = (Path(__file__).parent / ".." / ".." / ".." / "config") if CONFIG_DIRECTORY is None else Path(CONFIG_DIRECTORY)

        if not config_directory.exists():
            raise Exception("{location} does not exist!".format(location = str(config_directory)))
        
        config_file = config_directory / "config.ini"

        if config_file.exists():

            config = configparser.ConfigParser()
            config.read(str(config_file.resolve(True)))

            self.database = Database(
                server = config["Database"]["DatabaseServer"], 
                port = config["Database"]["DatabasePort"], 
                username = config["Database"]["DatabaseUsername"], 
                password = config["Database"]["DatabasePassword"], 
                name = config["Database"]["DatabaseName"]
            )

            self.eve_auth = EveAuth(
                ClientID = config["Eve Authentication"]["ClientID"], 
                ClientSecret = config["Eve Authentication"]["ClientSecret"], 
                ClientScopes = config["Eve Authentication"]["ClientScopes"], 
                DefaultScopes = config["Eve Authentication"]["DefaultScopes"], 
                ClientRedirect = config["Eve Authentication"]["ClientRedirect"], 
                ClientContactInfo = config["Eve Authentication"]["ClientContactInfo"], 
                AuthType = config["Eve Authentication"]["AuthType"], 
                SuperAdmins = config["Eve Authentication"]["SuperAdmins"]
            )

            self.neucore_auth = NeucoreAuth(
                AppID = config["NeuCore Authentication"]["AppID"], 
                AppSecret = config["NeuCore Authentication"]["AppSecret"], 
                AppURL = config["NeuCore Authentication"]["AppURL"]
            )

            self.timerboard_options = TimerboardOptions(
                TimerboardsEnabled = config["Timerboards"]["TimerboardsEnabled"],
                ApprovedTimerboardTypes = config["Timerboards"]["ApprovedTimerboardTypes"],
                ApprovedTimerboardDomains = config["Timerboards"]["ApprovedTimerboardDomains"]
            )

        else:

            self.database = Database(
                server = os.environ["ENV_WATCHMAN_DATABASE_SERVER"], 
                port = os.environ["ENV_WATCHMAN_DATABASE_PORT"], 
                username = os.environ["ENV_WATCHMAN_DATABASE_USERNAME"], 
                password = os.environ["ENV_WATCHMAN_DATABASE_PASSWORD"], 
                name = os.environ["ENV_WATCHMAN_DATABASE_NAME"]
            )

            self.eve_auth = EveAuth(
                ClientID = os.environ["ENV_WATCHMAN_EVE_CLIENT_ID"], 
                ClientSecret = os.environ["ENV_WATCHMAN_EVE_CLIENT_SECRET"], 
                ClientScopes = os.environ["ENV_WATCHMAN_EVE_CLIENT_SCOPES"] if "ENV_WATCHMAN_EVE_CLIENT_SCOPES" in os.environ else "esi-search.search_structures.v1", 
                DefaultScopes = os.environ["ENV_WATCHMAN_EVE_DEFAULT_SCOPES"] if "ENV_WATCHMAN_EVE_DEFAULT_SCOPES" in os.environ else "esi-search.search_structures.v1", 
                ClientRedirect = os.environ["ENV_WATCHMAN_EVE_CLIENT_REDIRECT"], 
                ClientContactInfo = os.environ["ENV_WATCHMAN_EVE_CLIENT_CONTACT_INFO"], 
                AuthType = os.environ["ENV_WATCHMAN_EVE_AUTH_TYPE"] if "ENV_WATCHMAN_EVE_AUTH_TYPE" in os.environ else "Neucore", 
                SuperAdmins = os.environ["ENV_WATCHMAN_EVE_SUPER_ADMINS"]
            )

            self.neucore_auth = NeucoreAuth(
                AppID = os.environ["ENV_WATCHMAN_NEUCORE_APP_ID"] if "ENV_WATCHMAN_NEUCORE_APP_ID" in os.environ else None, 
                AppSecret = os.environ["ENV_WATCHMAN_NEUCORE_APP_SECRET"] if "ENV_WATCHMAN_NEUCORE_APP_SECRET" in os.environ else None, 
                AppURL = os.environ["ENV_WATCHMAN_NEUCORE_APP_URL"] if "ENV_WATCHMAN_NEUCORE_APP_URL" in os.environ else None
            )

            self.timerboard_options = TimerboardOptions(
                TimerboardsEnabled = os.environ["ENV_WATCHMAN_TIMERBOARDS_ENABLED"] if "ENV_WATCHMAN_TIMERBOARDS_ENABLED" in os.environ else 0,
                ApprovedTimerboardTypes = os.environ["ENV_WATCHMAN_TIMERBOARDS_APPROVED_TYPES"] if "ENV_WATCHMAN_TIMERBOARDS_APPROVED_TYPES" in os.environ else None,
                ApprovedTimerboardDomains = os.environ["ENV_WATCHMAN_TIMERBOARDS_APPROVED_DOMAINS"] if "ENV_WATCHMAN_TIMERBOARDS_APPROVED_DOMAINS" in os.environ else None
            )

        versioning_file = config_directory / "VERSIONING"
        versioning = configparser.ConfigParser()
        versioning.read(str(versioning_file.resolve(True)))

        self.versioning = Versioning(
            AppName = versioning["App"]["app_name"],
            AppMajorVersion = versioning["App"]["major_version"],
            AppMinorVersion = versioning["App"]["minor_version"],
            AppPatchVersion = versioning["App"]["patch_version"],
            AppDelimiter = versioning["App"]["delimiter"],
            AppGithubLink = versioning["App"]["github_link"],
            OverhaulMajorVersion = versioning["Overhaul"]["major_version"],
            OverhaulMinorVersion = versioning["Overhaul"]["minor_version"],
            OverhaulPatchVersion = versioning["Overhaul"]["patch_version"],
            OverhaulDelimiter = versioning["Overhaul"]["delimiter"],
            OverhaulGithubLink = versioning["Overhaul"]["github_link"],
            ClientContactInfo = self.eve_auth.client_contact_info
        )


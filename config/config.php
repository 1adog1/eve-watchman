<?php

    $configVariables = [];
    $versionVariables = [];

    if (file_exists(__DIR__ . "/config.ini")) {

        $configData = parse_ini_file(__DIR__ . "/config.ini", true);

        //EVE AUTHENTICATION CONFIGURATION
        $configVariables["Client ID"] = $configData["Eve Authentication"]["ClientID"];
        $configVariables["Client Secret"] = $configData["Eve Authentication"]["ClientSecret"];
        $configVariables["Client Scopes"] = $configData["Eve Authentication"]["ClientScopes"];
        $configVariables["Default Scopes"] = $configData["Eve Authentication"]["DefaultScopes"];
        $configVariables["Client Redirect"] = $configData["Eve Authentication"]["ClientRedirect"];
        $configVariables["Client Contact Info"] = $configData["Eve Authentication"]["ClientContactInfo"];
        $configVariables["Auth Type"] = $configData["Eve Authentication"]["AuthType"];
        $configVariables["Super Admins"] = explode(",", str_replace(" ", "", $configData["Eve Authentication"]["SuperAdmins"]));

        //NEUCORE AUTHENTICATION CONFIGURATION
        $configVariables["NeuCore ID"] = $configData["NeuCore Authentication"]["AppID"];
        $configVariables["NeuCore Secret"] = $configData["NeuCore Authentication"]["AppSecret"];
        $configVariables["NeuCore URL"] = $configData["NeuCore Authentication"]["AppURL"];

        //DATABASE SERVER CONFIGURATION
        $configVariables["Database Server"] = $configData["Database"]["DatabaseServer"] . ":" . $configData["Database"]["DatabasePort"];
        $configVariables["Database Username"] = $configData["Database"]["DatabaseUsername"];
        $configVariables["Database Password"] = $configData["Database"]["DatabasePassword"];

        //DATABASE NAME CONFIGURATION
        $configVariables["Database Name"] = $configData["Database"]["DatabaseName"];

        //TIMERBOARD CONFIGURATION
        $configVariables["Timerboards Enabled"] = boolval($configData["Timerboards"]["TimerboardsEnabled"]);
        $configVariables["Approved Timerboard Types"] = explode(",", str_replace(" ", "", $configData["Timerboards"]["ApprovedTimerboardTypes"]));
        $configVariables["Approved Timerboard Domains"] = explode(",", str_replace(" ", "", $configData["Timerboards"]["ApprovedTimerboardDomains"]));

        //SITE CONFIGURATION
        $configVariables["Auth Cookie Name"] = $configData["Website"]["AuthCookieName"];
        $configVariables["Session Time"] = $configData["Website"]["SessionTime"];
        $configVariables["Auth Cache Time"] = $configData["Website"]["AuthCacheTime"];
        $configVariables["Store Visitor IPs"] = boolval($configData["Website"]["StoreVisitorIPs"]);

    }
    else {

        //$_ENV doesn't seem to always work, making our own array instead.
        $ENVS = getenv();

        //EVE AUTHENTICATION CONFIGURATION
        $configVariables["Client ID"] = $ENVS["ENV_WATCHMAN_EVE_CLIENT_ID"];
        $configVariables["Client Secret"] = $ENVS["ENV_WATCHMAN_EVE_CLIENT_SECRET"];
        $configVariables["Client Scopes"] = $ENVS["ENV_WATCHMAN_EVE_CLIENT_SCOPES"] ?? "esi-universe.read_structures.v1 esi-characters.read_corporation_roles.v1 esi-characters.read_notifications.v1";
        $configVariables["Default Scopes"] = $ENVS["ENV_WATCHMAN_EVE_DEFAULT_SCOPES"] ?? "esi-search.search_structures.v1";
        $configVariables["Client Redirect"] = $ENVS["ENV_WATCHMAN_EVE_CLIENT_REDIRECT"];
        $configVariables["Client Contact Info"] = $ENVS["ENV_WATCHMAN_EVE_CLIENT_CONTACT_INFO"];
        $configVariables["Auth Type"] = $ENVS["ENV_WATCHMAN_EVE_AUTH_TYPE"] ?? "Eve";
        $configVariables["Super Admins"] = explode(",", str_replace(" ", "", $ENVS["ENV_WATCHMAN_EVE_SUPER_ADMINS"]));

        //NEUCORE AUTHENTICATION CONFIGURATION
        $configVariables["NeuCore ID"] = $ENVS["ENV_WATCHMAN_NEUCORE_APP_ID"] ?? NULL;
        $configVariables["NeuCore Secret"] = $ENVS["ENV_WATCHMAN_NEUCORE_APP_SECRET"] ?? NULL;
        $configVariables["NeuCore URL"] = $ENVS["ENV_WATCHMAN_NEUCORE_APP_URL"] ?? NULL;

        //DATABASE SERVER CONFIGURATION
        $configVariables["Database Server"] = $ENVS["ENV_WATCHMAN_DATABASE_SERVER"] . ":" . $ENVS["ENV_WATCHMAN_DATABASE_PORT"];
        $configVariables["Database Username"] = $ENVS["ENV_WATCHMAN_DATABASE_USERNAME"];
        $configVariables["Database Password"] = $ENVS["ENV_WATCHMAN_DATABASE_PASSWORD"];

        //DATABASE NAME CONFIGURATION
        $configVariables["Database Name"] = $ENVS["ENV_WATCHMAN_DATABASE_NAME"];

        //TIMERBOARD CONFIGURATION
        $configVariables["Timerboards Enabled"] = boolval(($ENVS["ENV_WATCHMAN_TIMERBOARDS_ENABLED"] ?? 0));
        $configVariables["Approved Timerboard Types"] = explode(",", str_replace(" ", "", $ENVS["ENV_WATCHMAN_TIMERBOARDS_APPROVED_TYPES"]));
        $configVariables["Approved Timerboard Domains"] = explode(",", str_replace(" ", "", $ENVS["ENV_WATCHMAN_TIMERBOARDS_APPROVED_DOMAINS"]));

        //SITE CONFIGURATION
        $configVariables["Auth Cookie Name"] = $ENVS["ENV_WATCHMAN_WEBSITE_AUTH_COOKIE"] ?? "WatchmanAuthID";
        $configVariables["Session Time"] = (int)($ENVS["ENV_WATCHMAN_WEBSITE_SESSION_TIME"] ?? 86400);
        $configVariables["Auth Cache Time"] = (int)($ENVS["ENV_WATCHMAN_WEBSITE_AUTH_CACHE_TIME"] ?? 0);
        $configVariables["Store Visitor IPs"] = boolval(($ENVS["ENV_WATCHMAN_WEBSITE_STORE_IPS"] ?? 0));

    }

    $versionData = parse_ini_file(__DIR__ . "/VERSIONING", true);

    $applicationVersionArray = [
        $versionData["App"]["major_version"], 
        $versionData["App"]["minor_version"], 
        $versionData["App"]["patch_version"]
    ];
    $overhaulVersionArray = [
        $versionData["Overhaul"]["major_version"], 
        $versionData["Overhaul"]["minor_version"], 
        $versionData["Overhaul"]["patch_version"]
    ];
    $bootstrapVersionArray = [
        $versionData["Bootstrap"]["major_version"], 
        $versionData["Bootstrap"]["minor_version"], 
        $versionData["Bootstrap"]["patch_version"]
    ];
    $bootstrapIconsVersionArray = [
        $versionData["Bootstrap Icons"]["major_version"], 
        $versionData["Bootstrap Icons"]["minor_version"], 
        $versionData["Bootstrap Icons"]["patch_version"]
    ];
    $jQueryVersionArray = [
        $versionData["jQuery"]["major_version"], 
        $versionData["jQuery"]["minor_version"], 
        $versionData["jQuery"]["patch_version"]
    ];

    $versionVariables["App Name"] = $versionData["App"]["app_name"];
    $versionVariables["App Version"] = implode($versionData["App"]["delimiter"], $applicationVersionArray);
    $versionVariables["App Github"] = $versionData["App"]["github_link"];
    $versionVariables["Overhaul Version"] = implode($versionData["Overhaul"]["delimiter"], $overhaulVersionArray);
    $versionVariables["Overhaul Github"] = $versionData["Overhaul"]["github_link"];
    $versionVariables["Bootstrap Version"] = implode(".", $bootstrapVersionArray);
    $versionVariables["Bootstrap Icons Version"] = implode(".", $bootstrapIconsVersionArray);
    $versionVariables["jQuery Version"] = implode(".", $jQueryVersionArray);
    $versionVariables["Client Contact Info"] = $configVariables["Client Contact Info"];
    
?>

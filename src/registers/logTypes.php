<?php

    declare(strict_types = 1);

    /*
        Define tables to add to the database here.
        
        The $siteLogger->register method accepts the following arguments:
        
            [string] safeName: An HTML Class Safe Name for the option.
            [string] fullName: The full name of the option.
            [string ...] containedTypes: A variable number of log types that will be filtered by the option.
            
        EXAMPLE:
        
            $siteLogger->register(
                "page-control", 
                "Page Control", 
                "Access Granted", 
                "Access Denied", 
                "Page Not Found"
            );
            
    */
    
            $siteLogger->register(
                "relay-character-logins", 
                "Relay Character Logins", 
                "Relay Character Added"
            );
    
            $siteLogger->register(
                "relayed-notifications", 
                "Relayed Notifications", 
                "Relay Sent"
            );
            
            $siteLogger->register(
                "relay-errors", 
                "Relay Errors", 
                "Unknown Relay Error", 
                "Notification Parse Failure"
            );

?>
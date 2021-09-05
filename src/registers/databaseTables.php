<?php

    declare(strict_types = 1);
    
    /*
        Define tables to add to the database here.
        
        The $siteDatabase->register method accepts the following arguments:
        
            A single $tableName string. 
            A variable amount of $tableColumns arrays.
            
        Each $tableColumns array can have the following keys:
        
            [REQUIRED] "Name" - The name of the column.
            [REQUIRED] "Type" - The SQL type of the column. 
            [OPTIONAL] "Special" - Any special modifiers for the column. 
            
        EXAMPLE:
        
            $siteDatabase->register(
                "table_name",
                ["Name" => "special_column", "Type" => "BIGINT", "Special" => "primary key AUTO_INCREMENT"],
                ["Name" => "column_two", "Type" => "TEXT"]
            );
            
    */
    
    $siteDatabase->register(
        "servers",
        ["Name" => "id", "Type" => "TEXT"], 
        ["Name" => "type", "Type" => "TEXT"], 
        ["Name" => "name", "Type" => "TEXT"]
    );
    
    $siteDatabase->register(
        "channels",
        ["Name" => "id", "Type" => "TEXT"], 
        ["Name" => "type", "Type" => "TEXT"], 
        ["Name" => "serverid", "Type" => "TEXT"], 
        ["Name" => "name", "Type" => "TEXT"], 
        ["Name" => "url", "Type" => "TEXT"]
    );
    
    $siteDatabase->register(
        "relaycharacters",
        ["Name" => "id", "Type" => "BIGINT"], 
        ["Name" => "name", "Type" => "TEXT"], 
        ["Name" => "corporationid", "Type" => "BIGINT"], 
        ["Name" => "corporationname", "Type" => "TEXT"], 
        ["Name" => "allianceid", "Type" => "BIGINT"], 
        ["Name" => "alliancename", "Type" => "TEXT"], 
        ["Name" => "roles", "Type" => "LONGTEXT"]
    );
    
    $siteDatabase->register(
        "relays",
        ["Name" => "id", "Type" => "TEXT"], 
        ["Name" => "type", "Type" => "TEXT"], 
        ["Name" => "channelid", "Type" => "TEXT"], 
        ["Name" => "channeltype", "Type" => "TEXT"], 
        ["Name" => "pingtype", "Type" => "TEXT"], 
        ["Name" => "whitelist", "Type" => "LONGTEXT"], 
        ["Name" => "timestamp", "Type" => "BIGINT"], 
        ["Name" => "corporationid", "Type" => "BIGINT"], 
        ["Name" => "corporation", "Type" => "TEXT"]
    );
    
    $siteDatabase->register(
        "notifications",
        ["Name" => "id", "Type" => "BIGINT"], 
        ["Name" => "relayid", "Type" => "TEXT"], 
        ["Name" => "type", "Type" => "TEXT"], 
        ["Name" => "timestamp", "Type" => "BIGINT"]
    );
    
    $siteDatabase->register(
        "staggering",
        ["Name" => "corporationid", "Type" => "BIGINT"], 
        ["Name" => "characters", "Type" => "LONGTEXT"], 
        ["Name" => "frequency", "Type" => "BIGINT"], 
        ["Name" => "lastrun", "Type" => "BIGINT"], 
        ["Name" => "currentposition", "Type" => "BIGINT"]
    );

?>
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
        ["Name" => "id", "Type" => "VARCHAR(32)"],
        ["Name" => "type", "Type" => "ENUM('Discord', 'Slack')"],
        ["Name" => "name", "Type" => "TEXT"],
        ["Name" => "", "Type" => "", "Special" => "CONSTRAINT server_pk PRIMARY KEY (type, id)"]
    );

    $siteDatabase->register(
        "channels",
        ["Name" => "id", "Type" => "VARCHAR(32)"],
        ["Name" => "type", "Type" => "ENUM('Discord', 'Slack')"],
        ["Name" => "serverid", "Type" => "VARCHAR(32)"],
        ["Name" => "name", "Type" => "TEXT"],
        ["Name" => "", "Type" => "", "Special" => "CONSTRAINT channel_pk PRIMARY KEY (type, id, serverid)"]
    );

    $siteDatabase->register(
        "relaycharacters",
        ["Name" => "id", "Type" => "BIGINT", "Special" => "primary key"],
        ["Name" => "name", "Type" => "TEXT"],
        ["Name" => "status", "Type" => "ENUM('Valid', 'Invalid')"],
        ["Name" => "corporationid", "Type" => "BIGINT"],
        ["Name" => "corporationname", "Type" => "TEXT"],
        ["Name" => "allianceid", "Type" => "BIGINT"],
        ["Name" => "alliancename", "Type" => "TEXT"],
        ["Name" => "roles", "Type" => "LONGTEXT"],
        ["Name" => "", "Type" => "", "Special" => "INDEX (corporationid)"],
        ["Name" => "", "Type" => "", "Special" => "INDEX (allianceid)"]
    );

    $siteDatabase->register(
        "relays",
        ["Name" => "id", "Type" => "VARCHAR(64)", "Special" => "primary key"],
        ["Name" => "type", "Type" => "ENUM('Discord', 'Slack')"],
        ["Name" => "channelid", "Type" => "VARCHAR(32)"],
        ["Name" => "serverid", "Type" => "VARCHAR(32)"],
        ["Name" => "url", "Type" => "TEXT"],
        ["Name" => "pingtype", "Type" => "ENUM('everyone', 'channel', 'here', 'none')"],
        ["Name" => "whitelist", "Type" => "LONGTEXT"],
        ["Name" => "timestamp", "Type" => "BIGINT"],
        ["Name" => "corporationid", "Type" => "BIGINT"],
        ["Name" => "corporationname", "Type" => "TEXT"],
        ["Name" => "allianceid", "Type" => "BIGINT"],
        ["Name" => "alliancename", "Type" => "TEXT"],
        ["Name" => "", "Type" => "", "Special" => "INDEX (corporationid)"],
        ["Name" => "", "Type" => "", "Special" => "INDEX (allianceid)"]
    );

    $siteDatabase->register(
        "timerboards",
        ["Name" => "id", "Type" => "VARCHAR(64)", "Special" => "primary key"],
        ["Name" => "type", "Type" => "ENUM('RC2')"],
        ["Name" => "url", "Type" => "TEXT"],
        ["Name" => "token", "Type" => "TEXT"],
        ["Name" => "whitelist", "Type" => "LONGTEXT"],
        ["Name" => "timestamp", "Type" => "BIGINT"],
        ["Name" => "corporationid", "Type" => "BIGINT"],
        ["Name" => "corporationname", "Type" => "TEXT"],
        ["Name" => "allianceid", "Type" => "BIGINT"],
        ["Name" => "alliancename", "Type" => "TEXT"],
        ["Name" => "", "Type" => "", "Special" => "INDEX (corporationid)"],
        ["Name" => "", "Type" => "", "Special" => "INDEX (allianceid)"]
    );

    $siteDatabase->register(
        "timers",
        ["Name" => "id", "Type" => "BIGINT"],
        ["Name" => "timerboardid", "Type" => "VARCHAR(64)"],
        ["Name" => "type", "Type" => "TEXT"],
        ["Name" => "timestamp", "Type" => "BIGINT"],
        ["Name" => "", "Type" => "", "Special" => "CONSTRAINT timer_pk PRIMARY KEY (timerboardid, id)"]
    );

    $siteDatabase->register(
        "notifications",
        ["Name" => "id", "Type" => "BIGINT"],
        ["Name" => "relayid", "Type" => "VARCHAR(64)"],
        ["Name" => "type", "Type" => "TEXT"],
        ["Name" => "timestamp", "Type" => "BIGINT"],
        ["Name" => "", "Type" => "", "Special" => "CONSTRAINT notification_pk PRIMARY KEY (relayid, id)"]
    );

    $siteDatabase->register(
        "staggering",
        ["Name" => "corporationid", "Type" => "BIGINT", "Special" => "primary key"],
        ["Name" => "characters", "Type" => "LONGTEXT"],
        ["Name" => "frequency", "Type" => "BIGINT"],
        ["Name" => "nextrun", "Type" => "BIGINT"],
        ["Name" => "currentposition", "Type" => "BIGINT"],
        ["Name" => "nextcleanup", "Type" => "BIGINT"],
        ["Name" => "", "Type" => "", "Special" => "INDEX (nextrun)"],
        ["Name" => "", "Type" => "", "Special" => "INDEX (nextcleanup)"]
    );

?>

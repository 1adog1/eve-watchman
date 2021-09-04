<?php

    declare(strict_types = 1);

    /*
    
        Define new pages here. The order pages are registered here is the order that they'll appear in the NavBar (Left to Right on Desktop and Top to Bottom on Mobile). 
        
        The $this->registerPage method accepts the following arguments:
        
            [REQUIRED]
                [string] linkToUse: A URL Safe String which will be used to link to the page.
                [string] nameToUse: The name of the page that'll show up in the site's NavBar.
                [string] codeToUse: The unique identifier for the page, and the name that must be used for the folders containing the View, Model, Controller, and API Classes (as applicable). 
            [OPTIONAL]
                [bool] useModel: Specifies if the page has a Model Class. (DEFAULT: false)
                [bool] useController: Specifies if the page has a Controller Class. (DEFAULT: false)
                [bool] useAPI: Specifies if the page has an API Class. (DEFAULT: false)
                [bool] inNav: Specifies if the page appears in the site's NavBar (provided the user has the login status and access roles required to access it). (DEFAULT: true)
                [bool] loginRequired: Specifies if a user is required to be logged in to access the page. (DEFAULT: false)
                [array] accessRoles: A list of access roles that will allow the user to access the page. Having any of the specified roles will allow access. A page with an empty array doesn't require any access roles to access. (DEFAULT: [])
                
        For Examples see the already defined pages below. 
        
    */
    
    //HOME PAGE - DO NOT MODIFY LINK / CODE
    $this->registerPage(
        linkToUse: "home",
        nameToUse: "Homepage",
        codeToUse: "Home",
        inNav: false    //Keep this value false as the navbar branding links back to home already.
    );
    
    //ERROR PAGE - DO NOT MODIFY LINK / CODE
    $this->registerPage(
        linkToUse: "unknown",
        nameToUse: "Page Not Found",
        codeToUse: "Unknown",
        inNav: false
    );
    
    
    
    
    
    //ADMINISTRATON PAGE
    $this->registerPage(
        linkToUse: "admin",
        nameToUse: "Admin",
        codeToUse: "Admin",
        useModel: true,
        useController: true,
        useAPI: true,
        loginRequired: true, 
        accessRoles: ["Super Admin"]
    );
    
    //SITE LOGS PAGE
    $this->registerPage(
        linkToUse: "logs",
        nameToUse: "Site Logs",
        codeToUse: "Logs",
        useModel: true,
        useController: true,
        useAPI: true,
        inNav: true,
        loginRequired: true, 
        accessRoles: ["Super Admin"]
    );

?>
<?php

    declare(strict_types = 1);

    /*
    
        Define Access Roles that can be granted to groups here.
        
        The $this->registerRole method accepts the following argument:
        
            A $newRole string that identifies the role. 
            
        Note that for technical reasons capitalization MUST NOT be used as the sole differentiation between roles. Likewise swapping spaces for underscores (_) or vice versa in otherwise identical roles will cause similar problems. 
        
        This means that pairs of roles such as ("Member" / "member"), ("A Role" / "A_Role"), and ("Some Role" / "some_role") would all be invalid. 
            
        EXAMPLE:
        
            $this->registerRole("Member");
    
    */

    $this->registerRole("Configure Corporation"); 
    $this->registerRole("Configure Alliance"); 
    $this->registerRole("Configure Characters"); 

?>
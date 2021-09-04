<?php

    declare(strict_types = 1);

    /*
        Define custom methods for handling specific errors here.
        
        The $errorHandler->register method accepts the following arguments:
        
            [int] errorCode: A custom or predefined error code.
            [string] methodName: The name of the method you'll be using to handle errors with the aforementioned code.
        
        All methods registered here should be added to the /src/core/Errors/ErrorMethods class with the protected visibility, and accept the same 4 parameters as PHP's user-defined error_handler function.

        EXAMPLE:
        
            $errorHandler->register(E_CORE_ERROR, "coreErrorHandler");
    */
?>
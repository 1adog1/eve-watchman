<!DOCTYPE html>
<html>
    <head>
    
        <link rel="icon" href="/resources/images/favicon.ico">
        
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <script src="https://code.jquery.com/jquery-3.5.1.js" integrity="sha256-QWo7LDvxbWT2tbbQ97B53yJnYU3WhH/C8ycbRAkjPDc=" crossorigin="anonymous"></script>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-+0n0xVW2eSR5OomGNYDnhzAbDsOXxcvSN1TPprVMTNDbiYZCxYbOOl7+AMvyTG2x" crossorigin="anonymous">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-gtEjrD/SeCtmISkJkNUaaKMoLD0//ElJ19smozuHV6z3Iehds+3Ulb9Bn9Plx0x4" crossorigin="anonymous"></script>

        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css">
        
        <?php 
            
            $this->renderCSRFToken();
            $this->renderMeta(); 
            
        ?>

    </head>
    <style>
        .background {
            bottom: 0%;
            left: 0;
            margin-bottom: 5vh;
            background-color: #333;
            height: 100%;
            width: 100%;
            overflow-y: auto;
        }
        
        .login-button {
            height: 32px;
        }
        
        <?php
        
            $this->renderStyle(); 
            
        ?>
        
    </style>
    <nav class="navbar navbar-expand-xl bg-dark navbar-dark sticky-top shadow">
        
        <div class="container-fluid">
        
            <a class="navbar-brand mb-1" href="/">App Name</a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContents" aria-controls="navbarContents" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarContents">
            
                <ul class="navbar-nav">
    
                    <?php 
                    
                        $this->renderNav(); 
                        
                    ?>
                
                </ul>
                
                <ul class="navbar-nav ms-auto">
                
                    <li class="nav-item me-4 mt-2 mb-2 border-start border-secondary">
                    </li>
                
                    <?php 
                    
                        $this->renderAuth(); 
                        
                    ?>
                
                </ul>
                
            </div>
        
        </div>
        
    </nav>
    <body class="background">
    
        <div class="container-fluid">
            <br>
            
            <?php 
            
                $this->renderContent(); 
                
            ?>
            
        </div>
    
    </body>
</html>
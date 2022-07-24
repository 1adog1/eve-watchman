<?php

function generateNavLink($pageNumber, $text, $activeStatus = false, $disabledStatus = false) {
    
    echo "
    <li class='page-item" . (($activeStatus) ? " active" : "") . (($disabledStatus) ? " disabled" : "") . "'>
        <button class='page-link bg-dark text-white' type='submit' name='page' value='$pageNumber'>$text</button>
    </li>";
    
}

function generatePageNav() {
    
    $currentPage = getPageNumber();
    
    if (is_numeric($currentPage)) {
        
        $currentPage = (int)$currentPage;
        
    }
    else {
        
        $currentPage = 1;
        
    }
    
    generateNavLink(($currentPage - 1), "Previous", false, ($currentPage === 1));
    
    $tempCounter = 0;
    $tempPage = ($currentPage - 2);
    while ($tempCounter < 5) {
        
        if ($tempPage >= 1) {
            
            generateNavLink($tempPage, $tempPage, ($tempPage === $currentPage));
            $tempCounter++;
            
        }
        
        $tempPage++;
        
    }
    
    generateNavLink(($currentPage + 1), "Next");
    
}

function getLogArray() {

    $logArray = [];
    
    $pageOffset = getPageOffset();
    $whereStatement = checkFilter();
        
    $toPull = $GLOBALS['MainDatabase']->prepare("SELECT * FROM logs" . $whereStatement["Prepare"] . " ORDER BY timestamp DESC LIMIT 100 OFFSET :offset");
    $toPull->bindParam(":offset", $pageOffset, PDO::PARAM_INT);
    
    foreach ($whereStatement["Arguments"] as $eachArg) {
        $toPull->bindValue($eachArg["Placeholder"], $eachArg["Value"], $eachArg["Type"]);
    }
    
    $toPull->execute();
    
    $pulledArray = $toPull->fetchAll();
    
    foreach ($pulledArray as $throwaway => $arrayData) {
        $logArray[] = $arrayData;
    }
    
    return $logArray;
    
}

function displayLogs($logArray, $maxTableRows) {

    $maxCounter = 0;

    foreach($logArray as $throwaway => $logDetails) {
        
        if ($maxCounter < $maxTableRows) {
            
            $maxCounter += 1;
            
            echo "
            <tr>
                <td align='center'>
                    " . date("F jS, Y <br> H:i:s e", $logDetails["timestamp"]) . "
                </td>
                <td align='center'>
                    " . $logDetails["type"] . "
                </td>
                <td align='center'>
                    " . $logDetails["page"] . "
                </td>
                <td align='center'>
                    " . $logDetails["actor"] . "
                </td>
                <td align='center'>
                    " . $logDetails["details"] . "
                </td>
                <td align='center'>
                    " . $logDetails["trueip"] . "
                </td>
                <td align='center'>
                    " . $logDetails["forwardip"] . "
                </td>                                                                    
            </tr>
            ";
            
        }
        else {
            
            break;
            
        }
    }
}

?>
<?php
/**
 * Takes the operator and a rest string to add or subtract to the rolled value
 * so far with subsequent rolls. 
 */
function continueRoll($value, $operator, $restString){
    if($operator){
       switch($operator){
           case "+": $value += rollDice($restString); break;
           case "-": $value -= rollDice($restString); break;
           default: ;
       } 
    }
    
    return $value;
}
/**
 * Rolls a multitude of dice and drops either the highest or the lowest value
 * from the result.
 * May continue with additional rolls. Dropping only effects the roll directly
 * before the denotation -L/-H
 */
function rollSimpleWithDrop($matches){
    $numRolls = $matches[1] ? $matches[1] : 1;
        $numSides = $matches[2];
        
        $highest = 1;
        $lowest = $numSides;
        
        $result = 0;
        
        if($numSides > 0){
           for($i = 0; $i < $numRolls; $i++){
               
                $roll = random_int(1, $numSides);
                if($roll > $highest){
                    $highest = $roll;   
                }
                if($roll < $lowest){
                    $lowest = $roll;
                }
                $result += $roll;
            } 
            switch($matches[3]){
                case "L": $result -= $lowest; break;
                case "H": $result -= $highest; break;
                default: break;
            }
        
            $result = continueRoll($result, $matches[4], $matches[5]);    

        }
        return $result;
}

/**
 * Rolls a number of dice and adds the values together. 
 * May continue with additonal rolls
 */
function rollSimple($matches){
    $numRolls = $matches[1] ? $matches[1] : 1;
        $numSides = $matches[2];
        
        $result = 0;
        
        if($numSides > 0){
            for($i = 0; $i < $numRolls; $i++){
                $result += random_int(1, $numSides);
            }
        }
        
        $result = continueRoll($result, $matches[3], $matches[4]);
        
        return $result;
}
function rollDice($rollString){
    
    if(empty(trim($rollString))){
        return 0;
    }
    
    // possible format: Number
    $regexNumber = '/^\s*(\d+)\s*$/m';
    preg_match($regexNumber, $rollString, $matches);
    if($matches){
        
        return intval($matches[1]);
    }
    // possible format: XdY-[LH][+-]Z
    $regexDropHighLow = '/^(\d*)[dD](\d+)\s*-([LH])\s*([\+-])?\s*(.*)$/m';
    preg_match($regexDropHighLow, $rollString, $matches);
    if($matches){
        return rollSimpleWithDrop($matches);
    }
    
    // possible format: XdY[+-]Z
    $regexDice= '/^(\d*)[dD](\d+)\s*([\+-])?\s*(.*)$/m';
    preg_match($regexDice, $rollString, $matches);
    if($matches){
        return rollSimple($matches);
    }
    
    return 0;
}

// roll a simple dice
$dice = "d10";
echo("$dice:\t ". rollDice($dice) ."\n");

// roll a dice multiple times
$dice = "50d4";
echo("$dice:\t ". rollDice($dice) ."\n");

// add a static number to a dice roll result
$dice = "d10+5";
echo("$dice:\t ". rollDice($dice) ."\n");

//subtract a static number to a dice roll result
$dice = "2d4-2";
echo("$dice:\t ". rollDice($dice) ."\n");

// add two dice rolls together
$dice = "2d4 +15d10";
echo("$dice:\t ". rollDice($dice) ."\n");

// Chain rolls and number additions as you like
$dice = "2d4 +3d100 +1000";
echo("$dice:\t ". rollDice($dice) ."\n");

// Non-numeric entries are ignored and return 0
$dice = "XdY";
echo("$dice:\t ". rollDice($dice) ."\n");

// Everything after a wrong operator is ignored
$dice = "1d100 * 5 + 100";
echo("$dice:\t ". rollDice($dice) ."\n");

// Dropping the lowest rolled number once
$dice = "20d1-L";
echo("$dice:\t ". rollDice($dice) ."\n");

// Dropping the highest rolled number once
$dice = "20d1-L";
echo("$dice:\t ". rollDice($dice) ."\n");

// Dropping the highest rolled number once with added dice
$dice = "20d1-L + 50d1 + 5"; // expect 19 + 50 + 5 = 74
echo("$dice:\t ". rollDice($dice) ."\n");

?>
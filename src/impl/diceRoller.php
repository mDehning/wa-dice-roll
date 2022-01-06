<?php

    interface DiceRollerIF{

        /**
         * will perform a roll of dice according to the rules of the input string. Will return the total sum of all valid rolls.
         */

        function rollDice(String $input);

        function rollDiceComplex(String $input): DiceRollerResult;
    }

    /**
     * This is the result Object you can expect from the complexDiceRoll Method
     */
    class DiceRollerResult {

        // The original String used as input for the roll
        private $rollString;

        // The diceArray in print form, with marks for successes (if applicable)
        private $diceString;
        
        // The sum of all rolls, while respecting certain rules like Drop X Lowest rolls
        private $diceSum;
        
        private $successes;

        // Each single Roll Result in a flat array. 
        // Exploding Dice are summed together instead of displayed as a single die (so rolling a d4 twice will show as 5 not 4 and 1)
        private $diceArray;
        
        public function __construct(String $input){
            $this->rollString = $input;
            $this->diceString = "";
            $this->diceSum = 0;
            $this->diceArray = [];
        }

        // Getter & Setter
        function getRollString(): String{
            return $this->rollString;
        }

        function setDiceString(String $diceString){
            $this->diceString = $diceString;
        }
        function getDiceString(): String{
            return $this -> diceString;
        }

        function setDiceSum(int $diceSum){
            $this->diceSum = $diceSum;
        }
        function getDiceSum() : int{
            return $this -> diceSum;
        }

        function setDiceArray(array $diceArray){
            $this->diceArray = $diceArray;
        }
        function getDiceArray(): array{
            return $this->diceArray;
        }

        function setSuccesses(int $successes){
            $this->successes = $successes;
        }
        function getSuccesses(): int{
            return $this->successes;
        }
    }
    class DiceRollerPartialResult {
        public $sign = 1; // can be -1 if added with negative value
        public $mod = 0; // to represent a simple, constant modifier
        public $rolls = []; // the array of (valid) roll results of this particular substring
        public $nextSign = 1; // The sign for the upcoming unprocessed suffix that was not processed for this partial Result
        public $nextString = ""; // the String suffix that was not processed for this partial Result
        public $targetOp = null;
        public $targetNum = null;
    }

    class DiceRoller implements DiceRollerIF{
 
        private $regexMatches = [
            // possible format: Number Modifer at the end of String 
            ['/^\s*(\d+)\s*$/m', 'partialRollModifier'],

            // possible format: XdY [>=|>|=|<|<=]T ![>=|>|=|<|<=]R-[LH][+-]Z
            // R is the Reroll Target, T is the Success Target
            ['/^(\d*)[dD](\d+)\s*(>=|>|<|<=|=)\s*(\d+)\s*!(>=|>|<|<=|=)\s*(\d+)\s*-([LH])\s*([\+-])?\s*(.*)$/m', 'partialRollTargetExplodingWithDrop'],

            // possible format: XdY ![>=|>|=|<|<=]R-[LH][+-]Z
            // R is the Reroll Target
            ['/^(\d*)[dD](\d+)\s*!(>=|>|<|<=|=)\s*(\d+)\s*-([LH])\s*([\+-])?\s*(.*)$/m', 'partialRollExplodingWithDrop'],

            // possible format: XdY [>=|>|=|<|<=]T -[LH][+-]Z
            // T is the Success Target
            ['/^(\d*)[dD](\d+)\s*(>=|>|<|<=|=)\s*(\d+)\s*-([LH])\s*([\+-])?\s*(.*)$/m', 'partialRollTargetWithDrop'],

            // (>=|>|<|<=|=)\s*(\d+)\s*
            // possible format: XdY-[LH][+-]Z
            ['/^(\d*)[dD](\d+)\s*-([LH])\s*([\+-])?\s*(.*)$/m', 'partialRollSimpleWithDrop'],

            // possible format: XdY [>=|>|=|<|<=]T ![>=|>|=|<|<=]R[+-]Z
            // R is the Reroll Target, T is the Success Target
            ['/^(\d*)[dD](\d+)\s*(>=|>|<|<=|=)\s*(\d+)\s*!(>=|>|<|<=|=)\s*(\d+)\s*([\+-])?\s*(.*)$/m', 'partialTargetRollExploding'],

            // possible format: XdY ![>=|>|=|<|<=]R[+-]Z
            // R is the Reroll Target, T is the Success Target
            ['/^(\d*)[dD](\d+)\s*!(>=|>|<|<=|=)\s*(\d+)\s*([\+-])?\s*(.*)$/m', 'partialRollExploding'],

            // possible format: XdY [>=|>|=|<|<=]T [+-]Z
            // R is the Reroll Target, T is the Success Target
            ['/^(\d*)[dD](\d+)\s*(>=|>|<|<=|=)\s*(\d+)\s*([\+-])?\s*(.*)$/m', 'partialTargetRoll'],

            // possible format: XdY[+-]Z
            ['/^(\d*)[dD](\d+)\s*([\+-])?\s*(.*)$/m', 'partialRollSimple']
        ];

        final function rollDiceComplex(String $rollString): DiceRollerResult{
            $result = new DiceRollerResult($rollString);
            if(empty(trim($rollString))){
                return $result;
            }

            // Main part: Matching all those strings and using the correct function to create a partial result
            // Until the string is empty / has no more matches
            $partialResults = [];
            $nextString = $rollString;
            $nextResult;
            $nextSign = 1;
            do{
                $nextResult = null;
                
                foreach($this->regexMatches as $regexMatcher){
                    preg_match($regexMatcher[0], $nextString, $matches);
                    if($matches){
                        $function = $regexMatcher[1];
                        $nextResult = $this->$function($matches);
                        $nextResult->sign = $nextSign;

                        $nextSign = $nextResult->nextSign;
                        $nextString = $nextResult->nextString;
    
                        array_push($partialResults, $nextResult);
                        break;
                    }
                }
                
            }while($nextResult != null && !empty(trim($nextString)));

            // After this all Partial Results need to be interpreted for the full result
            // This can be as simple as adding values together but also contains the merging of all dice arrays into a flat array and creating the print version

            $diceSum = 0;
            $diceMod = 0;
            $diceArray = [];
            $diceString = "";
            $successes = 0;
            foreach($partialResults as $partial){
                // add the mod to the result, with respect to the sign
                $diceSum += $partial->sign * $partial->mod;
                $diceMod += $partial->sign * $partial->mod;

                // add the sum of the roll to the result, with respect to the sign
                $diceSum += $partial-> sign * array_sum($partial->rolls);

                // adds each entry to the String after checking for a potential target
                foreach($partial->rolls as $singleRoll){
                    $diceString .= $singleRoll;
                    if($this->fulfillsTarget($singleRoll, $partial->targetOp, $partial->targetNum)){
                        $successes++;
                        $diceString .="*";
                    }
                    $diceString .= ", ";
                }

                // add the array to the result array
                $diceArray = array_merge($diceArray, $partial->rolls);
            }
            if(!empty($diceString)){
                $diceString = substr($diceString, 0, -2);
            }
            $diceString = "[$diceString]";

            $result->setDiceArray($diceArray);
            $result->setDiceSum($diceSum);
            $result->setDiceString($diceString);
            $result->setSuccesses($successes);

           // $result->setDiceString($this->implodeRolls($diceArray, $diceMod));
            return $result;
        }

        final function fulfillsTarget($val, $op, $target): bool{
            if(!($op && $target)){
                return false;
            }
            switch($op){
                case ">=": return $val >= $target;
                case ">": return $val > $target;
                case "=": return $val == $target;
                case "<=": return $val <= $target;
                case "<": return $val < $target;
                default:;
            }
            return false;
        }
        final function implodeRolls(array $rolls, int $mod): String {
            $result = "[" . implode(", ", $rolls) . "]";
            if($mod && $mod != 0){
                $suffix = $mod > 0 ? " +$mod" : " $mod";
                $result .= $suffix;
            }
            return $result;
        }
       
        final function rollDice(String $rollString): int{
           return $this->rollDiceComplex($rollString)->getDiceSum();
        }
  
        private function getSign(String $operator): int{
            if($operator){
                switch($operator){
                    case "+": return 1;
                    case "-": return -1;
                    default: ;
                }
            }
            return 1;
        }

        private function partialRollModifier($matches): DiceRollerPartialResult{
            $partialResult = new DiceRollerPartialResult();
            $partialResult->mod = intval($matches[1]);
            return $partialResult;
        }

        private function roll($numSides): int{
            return $this->rollWithExplode($numSides);
        }
        /**
         * Rolls the number of dice and rerolls them, if the explode condition is met (and given in teh first place)
         * Checks and denies conditions that would result in an infinite loop
         * Returns the sum of all rolls
         */
        private function rollWithExplode($numSides, $explOperator = null, $explTarget = null): int{
            if(!($explOperator && $explTarget)){
                return random_int(1, $numSides);
            }
            // Catch Values that would lead to infinite rerolls
            $infiniteExplosion = false;
            switch($explOperator){
                case ">=": $infiniteExplosion = $explTarget <= 1; break;
                case ">": $infiniteExplosion = $explTarget <= 0; break;
                case "<=": $infiniteExplosion = $explTarget >= $numSides; break;
                case "<": $infiniteExplosion = $explTarget > $numSides; break;
                default:;
            }
            if($infiniteExplosion){
                random_int(1, $numSides);
            }
            $result = 0;
            do{
                $nextRoll = random_int(1, $numSides);
                $result += $nextRoll;

                $explodes = false;
                switch($explOperator){
                    case ">=": $explodes = $nextRoll >= $explTarget; break;
                    case ">": $explodes = $nextRoll > $explTarget; break;
                    case "=": $explodes = $nextRoll == $explTarget; break;
                    case "<": $explodes = $nextRoll < $explTarget; break;
                    case "<=": $explodes = $nextRoll <= $explTarget; break;
                    default: ;
                }
            } while($explodes);
            return $result;
        }
        /**
         * Central Method for Rolls with a drop - Rolls may "explode" as in repeated and added up when a certain condition is met
         */
        private function partialRollWithDrop($numRolls, $numSides, $dropOperator, $sign, $nextString, $explodeOperator = null, $explodeTarget = null): DiceRollerPartialResult{
            $partialResult = new DiceRollerPartialResult();

            $numRolls = $numRolls ? $numRolls : 1;
            $numSides = $numSides ? $numSides : 0;
            
            $result = [];
            if($numSides > 0){
                $highest = 0;
                $highestIndex = 0;
                $lowest = $numSides + 1;
                $lowestIndex = 0;
                for($i = 0; $i < $numRolls; $i++){
                    $roll = $this->rollWithExplode($numSides, $explodeOperator, $explodeTarget);
                    array_push($result, $roll);
                    if($roll > $highest){
                        $highest = $roll;
                        $highestIndex = $i;
                    }
                    if($roll < $lowest){
                        $lowest = $roll;
                        $lowestIndex = $i;
                    }
                }

                $indexToDrop = 0;
                switch($dropOperator){
                    case "L": $indexToDrop = $lowestIndex; break;
                    case "H": $indexToDrop = $highestIndex; break;
                    default: break;
                }
                if($indexToDrop >= 0){
                    $result[$indexToDrop] = 0;
                }
            }

            $partialResult->rolls = $result;
            $partialResult->nextSign = $this->getSign($sign);
            $partialResult->nextString = $nextString;
            return $partialResult;
        }
        /**
         * Rolls a multitude of dice and drops either the highest or the lowest value
         * from the result.
         * May continue with additional rolls. Dropping only effects the roll directly
         * before the denotation -L/-H
         */
        private function partialRollSimpleWithDrop($matches): DiceRollerPartialResult{
            $numRolls = $matches[1] ? $matches[1] : 1;
            $numSides = $matches[2];
            $dropOperator = $matches[3];
            return $this->partialRollWithDrop($numRolls, $numSides, $dropOperator, $matches[4], $matches[5]);
        }

        private function partialRollTargetWithDrop($matches): DiceRollerPartialResult{
            $numRolls = $matches[1] ? $matches[1] : 1;
            $numSides = $matches[2];
            $dropOperator = $matches[5];
            $result = $this->partialRollWithDrop($numRolls, $numSides, $dropOperator, $matches[6], $matches[7]);
            $result->targetOp = $matches[3];
            $result->targetNum = $matches[4];
            return $result;
        }
          /**
         * Rolls a multitude of dice and drops either the highest or the lowest value
         * from the result. Individual rolls may explode based on the given operator and target.
         * Drop will be etermined after the rerolls resulting from explosions.
         * May continue with additional rolls. Dropping only effects the roll directly
         * before the denotation -L/-H
         */
        private function partialRollExplodingWithDrop($matches): DiceRollerPartialResult{
            $numRolls = $matches[1] ? $matches[1] : 1;
            $numSides = $matches[2];
            $explodingOperator = $matches[3];
            $explodingTarget = $matches[4];
            $dropOperator = $matches[5];
            return $this->partialRollWithDrop($numRolls, $numSides, $dropOperator, $matches[6], $matches[7], $explodingOperator, $explodingTarget);
        }

        private function partialRollTargetExplodingWithDrop($matches): DiceRollerPartialResult{
            $numRolls = $matches[1] ? $matches[1] : 1;
            $numSides = $matches[2];
            $explodingOperator = $matches[5];
            $explodingTarget = $matches[6];
            $dropOperator = $matches[7];
            $result = $this->partialRollWithDrop($numRolls, $numSides, $dropOperator, $matches[8], $matches[9], $explodingOperator, $explodingTarget);
            $result->targetOp = $matches[3];
            $result->targetNum = $matches[4];
            return $result;
        }
        /**
         * Central internal method for simple rolls. Rolls may "explode" as in repeated and added up when a certain condition is met
         */
        private function partialRoll($numRolls, $numSides, $nextSign, $restString, $explodeOperator = null, $explodeTarget = null){
            $partialResult = new DiceRollerPartialResult();
            $numRolls = $numRolls ? $numRolls : 1;
            $numSides = $numSides ? $numSides : 0;

            $result = [];

            if($numSides > 0){
                for($i = 0; $i < $numRolls; $i++){
                    array_push($result, $this->rollWithExplode($numSides, $explodeOperator, $explodeTarget));
                }
            }
            $partialResult->rolls = $result;
            $partialResult->nextSign = $this->getSign($nextSign);
            $partialResult->nextString = $restString;
            return $partialResult;
        }
        /**
         * Rolls a number of dice and adds the values together. 
         * May continue with additonal rolls
         */
        private function partialRollSimple(array $matches): DiceRollerPartialResult{
            $numRolls = $matches[1] ? $matches[1] : 1;
            $numSides = $matches[2];
            $result = $this->partialRoll($numRolls, $numSides, $matches[3], $matches[4]);

            return $result;
        }

        private function partialRollExploding(array $matches): DiceRollerPartialResult{
            $numRolls = $matches[1] ? $matches[1] : 1;
            $numSides = $matches[2];
            $explodingOperator = $matches[3];
            $explodingTarget = $matches[4];
            $result = $this->partialRoll($numRolls, $numSides, $matches[5], $matches[6], $explodingOperator, $explodingTarget);

            return $result;
        }

        private function partialTargetRoll(array $matches): DiceRollerPartialResult{
            $numRolls = $matches[1] ? $matches[1] : 1;
            $numSides = $matches[2];
            $result = $this->partialRoll($numRolls, $numSides, $matches[5], $matches[6]);
            $result->targetOp = $matches[3];
            $result->targetNum = $matches[4];
            return $result;
        }

        private function partialTargetRollExploding(array $matches): DiceRollerPartialResult{
            $numRolls = $matches[1] ? $matches[1] : 1;
            $numSides = $matches[2];
            $explodingOperator = $matches[5];
            $explodingTarget = $matches[6];
            $result = $this->partialRoll($numRolls, $numSides, $matches[7], $matches[8], $explodingOperator, $explodingTarget);
            $result->targetOp = $matches[3];
            $result->targetNum = $matches[4];
            return $result;
        }
    }
?>
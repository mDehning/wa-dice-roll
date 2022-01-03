<?php
    
    interface DiceRollerIF{

        /**
         * will perform a roll of dice according to the rules of the input string. Will return the total sum of all valid rolls.
         */

        public function rollDice($input);
    }

    class DiceRoller implements DiceRollerIF{

        final function rollDice($rollString){
    
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
                return $this->rollSimpleWithDrop($matches);
            }
            
            // possible format: XdY[+-]Z
            $regexDice= '/^(\d*)[dD](\d+)\s*([\+-])?\s*(.*)$/m';
            preg_match($regexDice, $rollString, $matches);
            if($matches){
                return $this->rollSimple($matches);
            }
            
            return 0;
        }
    

        /**
         * Takes the operator and a rest string to add or subtract to the rolled value
         * so far with subsequent rolls. 
         */
        private function continueRoll($value, $operator, $restString){
            if($operator){
            switch($operator){
                case "+": $value += $this->rollDice($restString); break;
                case "-": $value -= $this->rollDice($restString); break;
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
        private function rollSimpleWithDrop($matches){
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
                
                    $result = $this->continueRoll($result, $matches[4], $matches[5]);    

                }
                return $result;
        }

        /**
         * Rolls a number of dice and adds the values together. 
         * May continue with additonal rolls
         */
        private function rollSimple($matches){
            $numRolls = $matches[1] ? $matches[1] : 1;
                $numSides = $matches[2];
                
                $result = 0;
                
                if($numSides > 0){
                    for($i = 0; $i < $numRolls; $i++){
                        $result += random_int(1, $numSides);
                    }
                }
                
                $result = $this->continueRoll($result, $matches[3], $matches[4]);
                
                return $result;
        }
    }
?>
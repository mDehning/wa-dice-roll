<?php
    include 'src/impl/diceRoller.php';

    $diceRoller = new DiceRoller();

    $examples = [
        ["d10", "Roll a simple dice"],
        ["50d4", "Roll a dice multiple times"],
        // Chaining Rolls
        ["d10+5", "Add a static Number to a dice roll result"],
        ["2d4-2", "Subtract a static number from a dice roll result"],
        ["2d4 + 15d10", "Add two dice rolls together"],
        ["2d4 + 3d100 + 1000", "Chain rolls and number additions as you like"],
        // Handling unparseable input
        ["XdY", "Non-numeric entries are ignored and return 0"],
        ["1d100 * 5 + 100", "Everything after a wrong operator is ignored"],
        // Exploding Simple Rolls
        ["5d13!>=9", "Roll a dice multiple times, exploding Dice when roll of 9 or 10"],
        ["5d12!>9", "Roll a dice multiple times, exploding Dice when roll of 10"],
        ["5d8!=7", "Roll a dice multiple times, exploding Dice when roll of 7"],
        ["5d10!>=8 + 3d100!>75", "Roll multiple dice with different target rules"],
        // Targets
        ["5d8>7", "Success at 8"],
        ["5d8>7!>=6", "Success at 8 or higher, Explode at 6 or higher"],
        ["10d8>7!>=6-H", "Dropping the highest rolled number once, success at 8 or higher, Explodes at 6"],
        ["10d8>7-H", "Dropping the highest rolled number once, success at 8"],
        // Rolls with Drop Mechanic
        ["20d1-L", "Dropping the lowest rolled number once"],
        ["20d1-H", "Dropping the highest rolled number once"],
        ["10d4!>=3-H", "Dropping the highest rolled number once, Exploding Dice when Roll of 3 or 4"],
        ["10d4!>3-H", "Dropping the highest rolled number once, Exploding Dice when Roll of 4"],
        ["10d7!<=4-H", "Dropping the highest rolled number once, Exploding Dice when Roll of 4 or less"],
        ["10d7!<4-H", "Dropping the highest rolled number once, Exploding Dice when Roll of 3 or less"],
        ["10d10!=2-H", "Dropping the highest rolled number once, Exploding Dice when Roll exactly 2"],
        ["20d1-L + 50d1 + 5", "Dropping the highest rolled number once with added dice"]
    ];

    echo("<h2>Simple Rolls with Sum Result</h2>");
    foreach($examples as $flatRoll){
        $dice = $flatRoll[0];
        $desc = $flatRoll[1];
        echo("<p>$desc<br />$dice:\t ". $diceRoller->rollDice($dice) ."</p>\n");
    }

    echo("<h2>Demonstrating complex roll result</h2>");

    foreach($examples as $complexRoll){
        $result = $diceRoller->rollDiceComplex($complexRoll[0]);
        echo("<div><p>");
        echo("<table>\n");
        echo("<tr><td colspan=2>". $complexRoll[1] ."</td></tr>\n");
        echo("<tr><td>Input:</td><td>". $result->getRollString() ."</td></tr>\n");
        echo("<tr><td>Sum:</td><td>". $result->getDiceSum() ."</td></tr>\n");
        echo("<tr><td>Successes:</td><td>". $result->getSuccesses() ."</td></tr>\n");
        echo("<tr><td>Rolled Dice:</td><td>". $result->getDiceString() ."</td></tr>\n");
        echo("</table>");
        echo("</p></div>\n\n");
    }


?>
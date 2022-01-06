<?php
    include 'src/impl/diceRoller.php';

    $diceRoller = new DiceRoller();

    $examples = [
        ["d10", "Roll a simple dice"],
        ["50d4", "Roll a dice multiple times"],
        ["d10+5", "Add a static Number to a dice roll result"],
        ["2d4-2", "Subtract a static number from a dice roll result"],
        ["2d4 + 15d10", "Add two dice rolls together"],
        ["2d4 + 3d100 + 1000", "Chain rolls and number additions as you like"],
        ["XdY", "Non-numeric entries are ignored and return 0"],
        ["1d100 * 5 + 100", "Everything after a wrong operator is ignored"],
        ["20d1-L", "Dropping the lowest rolled number once"],
        ["20d1-H", "Dropping the highest rolled number once"],
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
        echo("<table>");
        echo("<tr><td colspan=2>". $complexRoll[1] ."</td></tr>");
        echo("<tr><td>Input:</td><td>". $result->getRollString() ."</td></tr>");
        echo("<tr><td>Sum:</td><td>". $result->getdiceSum() ."</td></tr>");
        echo("<tr><td>Rolled Dice:</td><td>". $result->getDiceString() ."</td></tr>");
        echo("</table>\n\n");
        echo("</p></div>");
    }
    // Showing the Result Array of a roll
    $dice = "4d10 + 3d20 + 5";
    $result = $diceRoller->rollDiceComplex($dice);
    echo($result->getRollString() . ":\t ". $result->getDiceString() . " = " . $result->getDiceSum() . "<br />\n");

?>
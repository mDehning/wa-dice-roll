<?php
    include 'src/impl/diceRoller.php';

    $diceRoller = new DiceRoller();

    // roll a simple dice
    $dice = "d10";
    echo("$dice:\t ". $diceRoller->rollDice($dice) ."<br />\n");

    // roll a dice multiple times
    $dice = "50d4";
    echo("$dice:\t ". $diceRoller->rollDice($dice) ."<br />\n");

    // add a static number to a dice roll result
    $dice = "d10+5";
    echo("$dice:\t ". $diceRoller->rollDice($dice) ."<br />\n");

    //subtract a static number to a dice roll result
    $dice = "2d4-2";
    echo("$dice:\t ". $diceRoller->rollDice($dice) ."<br />\n");

    // add two dice rolls together
    $dice = "2d4 +15d10";
    echo("$dice:\t ". $diceRoller->rollDice($dice) ."<br />\n");

    // Chain rolls and number additions as you like
    $dice = "2d4 +3d100 +1000";
    echo("$dice:\t ". $diceRoller->rollDice($dice) ."<br />\n");

    // Non-numeric entries are ignored and return 0
    $dice = "XdY";
    echo("$dice:\t ". $diceRoller->rollDice($dice) ."<br />\n");

    // Everything after a wrong operator is ignored
    $dice = "1d100 * 5 + 100";
    echo("$dice:\t ". $diceRoller->rollDice($dice) ."<br />\n");

    // Dropping the lowest rolled number once
    $dice = "20d1-L";
    echo("$dice:\t ". $diceRoller->rollDice($dice) ."<br />\n");

    // Dropping the highest rolled number once
    $dice = "20d1-H";
    echo("$dice:\t ". $diceRoller->rollDice($dice) ."<br />\n");

    // Dropping the highest rolled number once with added dice
    $dice = "20d1-L + 50d1 + 5"; // expect 19 + 50 + 5 = 74
    echo("$dice:\t ". $diceRoller->rollDice($dice) ."<br />\n");

    // Showing the Result Array of a roll
    $dice = "4d10 + 3d20 + 5";
    $result = $diceRoller->rollDiceComplex($dice);
    echo($result->getRollString() . ":\t ". $result->getDiceString() . " = " . $result->getDiceSum() . "<br />\n");

?>
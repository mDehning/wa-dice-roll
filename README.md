# wa-dice-roll
 
This script allows to pass a String identifying a dice roll mechanic and returns the result of a possible roll, as denoted by the input.

Currently, the script returns the sum of values rolled by the specified dice or the number of successes rolled with the full dice pool.

# How to use

Include the file `src/impl/diceRoller.php` and intialize a new DiceRoller Object. The file contains the interface as well - the only method `rollDice($input)` will parse a String and return a number result of the random roll.

The method `rollDiceComplex($input)` will parse the input and return an extensive result, including the sum, number of successes and a Stringified version of the result with individual roles marked as successes, if applicable.

## Dice Notation

### Simple Roll
```
Example => Possible Result:
d10   => [5] = 5
```
Rolls a die with the specified number of sides (in this case 10) and returns the value. Any postive, non-null integer is possible here. Results will be an integer between 1 and N (including)

### Dice Pool of equal dice
```
Example => Possible Result:
5d10   => [1, 2, 10, 3, 3] = 19
```
Rolls a number of identical dice and sums up the values. In this example, 5 die with ten sides will be rolled and added up.

Omitting a number before the d will always be interpreted as a 1

### Roll Modifiers
```
Examples => Possible Result:
d10 + 5    => [4] + 5 = 9
d10 - 2    => [4] - 2 = 2
```
Modifies a roll result with the given integer *after* the roll was taken. The modifiere can be added or subtracted to the value and can be any positive integer. In these examples, the first roll of a d10 gets 5 added to the result, while the second roll of a d10 gets 2 subtracted from the result (*Note: This can cause results to become negative*)



### Mixed Dice Pools: 2d10 + 1d4
```
Example => Possible Result:
2d10 + 1d4    =>[8, 2, 3] = 13
```

Multiple rolls can be combined together into a sum. In this example, two ten-sided dice are rolled and an additional four-sided die, the results will be added together.

### Target Numbers
```
Examples => Possible Result:
5d10 >=7    => [2, 3, 5, 7*, 8*] = 2 
5d10 >7     => [2, 3, 5, 7, 8*] = 1
5d10 =5     => [2, 3, 5*, 7, 8] = 1
5d10 <3     => [2*, 3, 5, 7, 8] = 1
5d10 <=3    => [2*, 3*, 5, 7, 8] = 2
```
Will count the number of rolls which fulfill the given target number. 

__Note__: If simpleRoll is used, the number of successes will be returned instead of the sum. 

Mixing multiple dicepools where some are not using targets will result in Success Counting taking precedence.

Using Modifiers will directly affect the total number of successes, not the individual rols

### Exploding Dice / Reroll on Target
```
Examples => Possible Result:
2d10 !>=7
4d10 !>7
4d10 !=5
4d10 !<3
4d10 !<=2
```
When a roll of a single die of a dicepool fullfills the targetcondition, it will be repeated and added together. Conditions that would lead to infinite rerolls are ignored completely.
If in the first example a roll like `[7, 3]` occurs, the first roll will be repeated, like `[7 + 4, 3]` resulting in `[11, 3]`. This process can be repeatedy multiple times, meaning a roll like `[7 + 8 + 10 + 9 + 7, 3]` is entirely possible.

Exploding Dice can be combined with Target Numbers, the Success will be determined after all rerolls have been executed.

### Drop Lowest or Highest
```
Examples => Possible Result:
5d10 -L
5d10 -H
```
The Lowest or Highest value of the dice pool will be dropped once and only once from the result. 

In the first example above when a roll like `[4, 7, 2, 8, 2]` occurs, `2` is the lowest roll and will be dropped from the result once (even though it occurs twice). The Resul will be `4 + 7 + 2 + 8 = 21`.

Accordingly in the second example the same roll would drop the `8` as the highest roll, yielding the result of `4 + 7 + 2 + 2 = 15`

Can be combined with Target Numbers.
```
5d10 >=7 -H
```
Can be Combined with Exploding Dice.
```
5d10 !>=7 -H
```
Or Both - Target Number comes before the Exploding Target Number
```
5d10 >=9 !>=8 -H
```

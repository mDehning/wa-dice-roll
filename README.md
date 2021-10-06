# wa-dice-roll
 
This script allows to pass a String identifying a dice roll mechanic and returns the result of a possible roll, as denoted by the input.

Currently, the script returns the sum of values rolled by the specified dice.

## Dice Notation

### Simple Roll
```
Example:
d10
```
Rolls a die with the specified number of sides (in this case 10) and returns the value. Any postive, non-null integer is possible here. Results will be an integer between 1 and N (including)

### Dice Pool of equal dice
```
Example:
5d10
```
Rolls a number of identical dice and sums up the values. In this example, 5 die with ten sides will be rolled and added up.

Omitting a number before the d will always be interpreted as a 1

### Roll Modifiers
```
Examples:
d10 + 5
d10 - 2
```
Modifies a roll result with the given integer *after* the roll was taken. The modifiere can be added or subtracted to the value and can be any positive integer. In these examples, the first roll of a d10 gets 5 added to the result, while the second roll of a d10 gets 2 subtracted from the result (*Note: This can cause results to become negative*)

### Mixed Dice Pools: 2d10 + 1d4
```
Example:
2d10 + 1d4
```

Multiple rolls can be combined together into a sum. In this example, two ten-sided dice are rolled and an additional four-sided die, the results will be added together.

### Drop Lowest or Highest
```
Examples:
5d10 -L
5d10 -H
```
The Lowest or Highest value of the dice pool will be dropped once and only once from the result. 

In the first example above when a roll like `[4, 7, 2, 8, 2]` occurs, `2` is the lowest roll and will be dropped from the result once (even though it occurs twice). The Resul will be `4 + 7 + 2 + 8 = 21`.

Accordingly in the second example the same roll would drop the `8` as the highest roll, yielding the result of `4 + 7 + 2 + 2 = 15`
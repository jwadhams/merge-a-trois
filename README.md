# Merge à Trois - Three-way merge for PHP array data

I have a PHP-backended application that serves up a large JSON-encoded unstructured blob of data to JavaScript clients.  The clients manipulate bits inside that big object, then return the whole altered object to the back end, possibly minutes later.

It's possible that two people can be collaborating on the same big object roughly-synchronously (typically on different bits inside the large object), so the backend needs a way to merge those changes.

The goal of this library is to silently merge changes and end up with one authoritative server-stored object that as much as possible honors *all* collaborators.  Unlike software version control merges, no user is in a place to arbitrate conflicts, so the algorithm *always* returns an answer, and we keep audit trails outside of this library.

When in doubt, the most recent change wins.

## Why three-way merge?

Each client is first pulling a known-good version of the object. The three-way merge technique lets us use a common ancestor to the potentially conflicting changes, and is especially useful in noticing deleted content.



## Examples

Here's a basic merge where the common ancestor is a blank slate, and both descendants make additive, non-conflicting changes:

```php
$a = $b = $original = [];
$a['a'] = 'apple';
$b['b'] = 'banana';

JWadhams\MergeATrois::merge($original, $a, $b);
//['a'=>'apple', 'b'=>'banana']
```

If both children make conflicting changes, the second change wins.

```php
$a = $b = $original = [];
$a['a'] = 'apple';
$b['a'] = 'avocado';

JWadhams\MergeATrois::merge($original, $a, $b);
//['a'=>'avocado']
```

Changes within complex associative arrays are merged recursively.

```php
$a = $b = $original = [
  'person' => ['first_name' => 'Marge', 'last_name' => 'Bouvier'],
  'hobby' => ['type' => 'bowling', 'rank' => 'novice'],
];
$a['person']['last_name'] = 'Simpson';
$b['hobby']['rank'] = 'champion';

JWadhams\MergeATrois::merge($original, $a, $b);
/*[
  "person" => [
    "first_name" => "Marge",
    "last_name" => "Simpson",
  ],
  "hobby" => [
    "type" => "bowling",
    "rank" => "champion",
  ],
]*/
```


When merging numeric arrays, the algorithm looks for unique content, and keys are ignored. Here, a and b both introduce new content in index 0, the algorithm keeps both contributions.

```php
$a = $b = $original = [];
$a[] = 'apple';
$b[] = 'banana';

JWadhams\MergeATrois::merge($original, $a, $b);
//['apple', 'banana']
```

In this example, the algorithm internally acts as if the string 'apple' was deleted, and a new string 'APPLE' was added.

```php
$a = $b = $original = ['apple'];
$a[0] = 'APPLE';
$b[] = 'banana';

JWadhams\MergeATrois::merge($original, $a, $b);
//['APPLE', 'banana']
```

A cardinal rule of this library is that when in doubt, the most recent change wins. So the returned numeric array will follow B's sequence, then append new content from A.

In this example, we prepend content to B, and the merge result honor's B's order.

```php
$a = $b = $original = ['apple'];
array_unshift($b, 'banana');
// $b now equals ['banana', 'apple']

JWadhams\MergeATrois::merge($original, $a, $b);
//['banana', 'apple']
```

In this example, the content is prepended to A. The merge result honors B's order, except to notice A's deletion.  Then the merge appends new content in A that B is unaware of.

```php
$a = $b = $original = ['zucchini'];
unset($a[0]); //$a equals []
array_unshift($a, 'apple'); //$a equals ['apple']
array_unshift($b, 'banana'); //$b equals ['banana', 'zucchini']

JWadhams\MergeATrois::merge($original, $a, $b);
//['banana', 'apple']
```


<b>Note</b> the merge algorithm defers to the PHP `json_encode` method to decide what is a numeric array: if `json_encode` uses JSON's `[]` representation, we treat the array as keys-don't-matter.  If your array is encoded like `{}`, it will be processed as an associative array, and indices *will* be preserved.

(`json_encode` appears to look for sequential numeric zero-indexed keys with no gaps.)

```php
$a = $b = $original = [2 => 'banana', 26 => 'zucchini'];
JWadhams\MergeATrois::merge($original, $a, $b);
//[2 => 'banana', 26 => 'zucchini']
```


## Installation

The best way to install this library is via [Composer](https://getcomposer.org/):

```bash
composer require jwadhams/merge-a-trois
```

If that doesn't suit you, and you want to manage updates yourself, the entire library is self-contained in `src/JWadhams/MergeATrois.php` and you can download it straight into your project as you see fit.

```bash
curl -O https://raw.githubusercontent.com/jwadhams/merge-a-trois/master/src/JWadhams/MergeATrois.php
```

## Special Thanks

Thanks to [Lukas Benes](https://github.com/falsecz) &mdash; This library started life as a port of your excellent CoffeeScript library [3-way-merge](https://github.com/falsecz/3-way-merge)

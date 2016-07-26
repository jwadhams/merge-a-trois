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


When merging numeric arrays, the algorithm looks for unique content. In this example, the algorithm internally acts as if the string 'apple' was deleted, and a new string 'APPLE' was added.

```php
$a = $b = $original = ['apple'];
$a[0] = 'APPLE';
$b[] = 'banana';

JWadhams\MergeATrois::merge($original, $a, $b);
//['APPLE', 'banana']
```

The merge assumes elements in numeric arrays *do not* need to keep their index key. (The algorithm preserves order, and the contents of A are sequenced before additions to B.)

```php
$a = $b = $original = ['apple'];
array_unshift($b, 'banana');
// $b now equals ['banana', 'apple']

JWadhams\MergeATrois::merge($original, $a, $b);
//['apple', 'banana']
```

Arrays with zero-or-positive integer indices are processed as numeric arrays, and the old indices will not be preserved, even if A and B are unchanged.

```php
$a = $b = $original = [42=>'banana', 69=>'peach'];
JWadhams\MergeATrois::merge($original, $a, $b);
//[ 'banana', 'peach' ]
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

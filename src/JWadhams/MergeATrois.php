<?php

namespace JWadhams;

class MergeATrois
{
    public static function merge(array $original, array $a, array $b)
    {
        $result = [];

        /*
        We use a JSON representation to figure out if something is a numeric array.
        We can use that same string to figure out if things are identical without writing our own deep inspection.
        (This is a really stringent "identical" e.g., associative arrays with reordered properties will fail even if otherwise we don't care)
        Note the Marge Simpson example in the docs, at the first level of recursion it'll notice that A didn't change "hobby" and B didn't change "person" -- it can take advantage of this shortcut
        */
        $a_json = json_encode($a);
        $b_json = json_encode($b);
        $o_json = json_encode($original);

        if ($a_json === $o_json) {
            return $b;
        } //No changes in A, return B
        if ($b_json === $o_json) {
            return $a;
        } //No changes in B, return A

        //When merging numeric-indexed arrays, ignore the indexes and just merge the contents.
        if (self::is_numeric_array($a_json) and self::is_numeric_array($b_json)) {
            //In the case of confusion, $b wins, including numeric array order
            //Everything in $b, unless it was known in $original and deleted in $a
            foreach ($b as $item) {
                if (! (in_array($item, $original) && !in_array($item, $a))) {
                    array_push($result, $item);
                }
            }

            //Everything in $a, that's new to BOTH $b and original
            foreach ($a as $item) {
                if (!in_array($item, $original) && !in_array($item, $b)) {
                    array_push($result, $item);
                }
            }
        } else {
            /*
            For associative arrays:

            For every thing on A:
            Exists on B, is complex : recursion
            Exists on B, B differs from Original : B
            Exists on B, B is Original : A
            Doesn't exist on B, doesn't exist on Original : A
            Doesn't exist on B, does exist on Original : skip

            For every thing on B:
            Doesn't exist on A or Original : B
            */


            foreach ($a as $key => $value) {

                //Does it exist on B?
                if (array_key_exists($key, $b)) {

                    // We've had problems in recursion where there's an object in the middle of the tree
                    if (gettype($original[$key]) === 'object') {
                        $original[$key] = json_decode(json_encode($original[$key]), true);
                    }
                    if (gettype($a[$key]) === 'object') {
                        $a[$key] = json_decode(json_encode($a[$key]), true);
                    }
                    if (gettype($b[$key]) === 'object') {
                        $b[$key] = json_decode(json_encode($b[$key]), true);
                    }

                    //and is an array (numeric or associative), use recursion
                    if (is_array($a[$key]) and is_array($b[$key])) {
                        //It could be new on both, or a primitive on original:
                        $recur_orig = (array_key_exists($key, $original) and is_array($original[$key])) ? $original[$key] : [];

                        $result[$key] = self::merge($recur_orig, $a[$key], $b[$key]);

                    //Exists on A and B, B the same as origin : use A
                    } elseif (array_key_exists($key, $original) and $original[$key] === $b[$key]) {
                        $result[$key] = $a[$key];

                    //Exists on A and B, B differs from Origin (or is new) : B always wins
                    } else {
                        $result[$key] = $b[$key];
                    }


                    //Does not exist on B, does not exist on origin, use A
                } elseif (! array_key_exists($key, $original)) {
                    $result[$key] = $a[$key];

                //Does not exist on B, did exist on original (deleted) skip
                } else {
                }
            }

            //Now find data inserted on $b that $a doesn't know about
            foreach ($b as $key => $value) {
                if (! array_key_exists($key, $original) and !array_key_exists($key, $a)) {
                    $result[$key] = $value;
                }
            }
        }
        return $result;
    }

    /*
    It's kind of awkward to figure out if a PHP array is numeric or associative.
    So we don't.
    Instead we defer to "if the built in JSON encoder puts a [ in front it's a numeric indexes-don't-matter array"
    */
    public static function is_numeric_array($json)
    {
        if (! is_string($json)) {
            $json = json_encode($json);
        }
        return substr($json, 0, 1) == '[';
    }
}

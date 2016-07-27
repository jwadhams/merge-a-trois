<?php

namespace JWadhams;
class MergeATrois{

  public static function merge(array $original, array $a, array $b){

    $result = [];

    //When merging numeric-indexed arrays, ignore the indexes and just merge the contents.
    //Act as if Arrays can only contain primitives
    if( self::is_numeric_array($a) and self::is_numeric_array($b)){
      //Everything in $a, unless it was known in $original and deleted in $b
      foreach($a as $item){
        if(! (in_array($item, $original) && !in_array($item, $b))){
          array_push($result, $item);
        }
      }

      foreach($b as $item){
        //Everything in $b, that's new to BOTH A and original
        if( !in_array($item, $original) && !in_array($item, $a) ){
          array_push($result, $item);
        }
      }

    }else{
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


      foreach($a as $key => $value){

        //Does it exist on B?
        if(array_key_exists( $key, $b ) ){
          //and is an array (numeric or associative), use recursion
          if(is_array($a[$key]) and is_array($b[$key]) ){
            //It could be new on both, or a primitive on original:
            $recur_orig = (array_key_exists( $key, $original ) and is_array($original[$key]) ) ? $original[$key] : [];

            $result[$key] = self::merge($recur_orig, $a[$key], $b[$key]);

            //Exists on A and B, B the same as origin : use A
          }elseif( array_key_exists( $key, $original ) and $original[$key] === $b[$key]){
            $result[$key] = $a[$key];

            //Exists on A and B, B differs from Origin (or is new) : B always wins
          }else{
            $result[$key] = $b[$key];
          }


        //Does not exist on B, does not exist on origin, use A
        }elseif( ! array_key_exists( $key, $original )){
          $result[$key] = $a[$key];

        //Does not exist on B, did exist on original (deleted) skip
        }else{
        }
      }

      //Now find data inserted on $b that $a doesn't know about
      foreach($b as $key => $value){
        if( ! array_key_exists($key, $original) and !array_key_exists($key, $a) ){
          $result[$key] = $value;
        }
      }
    }
  	return $result;
  }

  public static function is_numeric_array($array){
    return substr(json_encode($array), 0, 1) == '[';
  }

}

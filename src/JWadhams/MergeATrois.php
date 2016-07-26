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
      //For associative arrays:
      foreach($b as $key => $value){
        //All of these exist on B

        //Does it exist on the original?
        if( array_key_exists( $key, $original) ){

          //Does it exist on all three?
          if(array_key_exists( $key, $a ) ){
            //and is an array (numeric or associative), use recursion
            if(is_array($a[$key]) and is_array($original[$key]) and is_array($value)){
              $result[$key] = self::merge($original[$key], $a[$key], $b[$key]);

              //Primitive, and same in Original and B, return A (same or improved)
            }elseif($original[$key] === $value){
              $result[$key] = $a[$key];

              //Otherwise (A changed and B changed, or A unchanged and B changed): B wins.
            }else{
              $result[$key] = $value;
            }


          //Yes original, but deleted from A
          }else{
            //Do nothing
          }

        //New to original, B wins (regardless of A)
        }else{
              $result[$key] = $value;
        }
      }

      //Now find data inserted on $a that $b doesn't know about
      foreach($a as $key => $value){
        if( ! array_key_exists($key, $original) and !array_key_exists($key, $b) ){
          $result[$key] = $value;

        }
      }
    }
  	// if isArray
  	// 	a = [] if not Array.isArray a
  	// 	o = [] if not Array.isArray o
    //
  	// 	for k of a
  	// 		unless a[k] not in b and a[k] in o
  	// 			result.push a[k]
    //
  	// 	for k of b
  	// 		if not k of a
  	// 			result.push b[k]
  	// 		else if typeof a[k] is 'object' and typeof b[k] is 'object'
  	// 			ov = if k of o and typeof o[k] is 'object' then o[k] else {}
  	// 			result[k] = merge ov, a[k], b[k]
  	// 		else if b[k] not in a
  	// 			result.push b[k]
    //
  	// else
  	// 	a = {} if Array.isArray a
    //
  	// 	for k of b
  	// 		result[k] = b[k]
    //
  	// 	for k of a
  	// 		if not k of result
  	// 			result[k] = a[k]
  	// 		else if a[k] isnt result[k]
  	// 			if typeof a[k] is 'object' and typeof b?[k] is 'object'
  	// 				ov = if o? and k of o and typeof o[k] is 'object' then o[k] else {}
  	// 				result[k] = merge ov, a[k], b[k]
  	// 			else if b?[k] is o?[k]
  	// 				result[k] = a[k]
    //
  	return $result;
  }

  public static function is_numeric_array($array){
    if(!is_array($array)) return false;

    return array_reduce(
      array_keys($array),
      function($carry, $index){
        return is_integer($index) and $index >= 0 and $carry;
      },
      true
    );
  }

}

<?php

class JsonLogicTest extends PHPUnit_Framework_TestCase{


	/**
	* @expectedException TypeError
	*/
	public function testInvalidArgs() {
		JWadhams\MergeATrois::merge(['a'], ['b'], 'c');
	}


	/**
 * @dataProvider numericArrayProvider
 */
	public function testNumericArray($array, $is_numeric){
		$this->assertEquals(
			$is_numeric,
			JWadhams\MergeATrois::is_numeric_array($array)
		);
	}

	public function numericArrayProvider(){
		return [
			[ ['a', 'b', 'c'], true ],
			[ [0=>'a', 2=>'c'], true ],
			[ [], true ],
			[ [0=>'a', 'b'=>'banana'], false ],
			[ ['a'=>'apple', 'b'=>'banana'], false ],
			[ 'string', false],
			[ 666, false],
		];
	}


	/**
 * @dataProvider mergeProvider
 */
	public function testMerge($original, $a, $b, $result){
		$this->assertEquals(
			$result,
			JWadhams\MergeATrois::merge($original, $a, $b)
		);
	}



	public function mergeProvider(){
		return [
			// ====== No changes ======
			[
				[], [], [],
				[]
			],
			[
				['apple'], ['apple'], ['apple'],
				['apple']
			],
			[
				['a'=>'apple'], ['a'=>'apple'], ['a'=>'apple'],
				['a'=>'apple']
			],
			[
				['fruit'=>['a'=>'apple']], ['fruit'=>['a'=>'apple']], ['fruit'=>['a'=>'apple']],
				['fruit'=>['a'=>'apple']]
			],

			//====== Simple element inserts ======
			[
				[], ['apple'], [],
				['apple']
			],
			[
				[], [], ['apple'],
				['apple']
			],
			[ //Both add
				[], ['apple'], ['apple'],
				['apple']
			],
			[
				[], ['apple'], ['banana'],
				['apple','banana']
			],
			[
				['apple'], ['apple'], ['apple', 'banana'],
				['apple','banana']
			],

			[
				[], ['a'=>'apple'], [],
				['a'=>'apple']
			],
			[
				[], [], ['a'=>'apple'],
				['a'=>'apple']
			],
			[
				['a'=>'apple'], ['a'=>'apple'], ['a'=>'apple', 'b'=>'banana'],
				['a'=>'apple', 'b'=>'banana']
			],


			// ====== Simple element deletion ======
			[
				['apple'], [], ['apple'],
				[]
			],
			[
				['apple'], ['apple'], [],
				[]
			],
			[ //Both delete
				['apple'], [], [],
				[]
			],
			[
				['apple', 'banana'], ['apple'], ['apple', 'banana'],
				['apple']
			],
			[
				['a'=>'apple'], [], ['a'=>'apple'],
				[]
			],
			[
				['a'=>'apple'], ['a'=>'apple'], [],
				[]
			],
			[
				['a'=>'apple'], [], [],
				[]
			],
			[
				['a'=>'apple', 'b'=>'banana'], ['a'=>'apple'], ['a'=>'apple', 'b'=>'banana'],
				['a'=>'apple']
			],


			// ====== Changes in numeric arrays ======
			[
				['apple'], ['apple'], ['APPLE'],
				['APPLE']
			],

			//====== Changes in associative arrays ======
			[
				['a'=>'apple'], ['a'=>'apple'], ['a'=>'avocado'],
				['a'=>'avocado']
			],
			[
				['a'=>'apple'], ['a'=>'avocado'], ['a'=>'apple'],
				['a'=>'avocado']
			],
			[ //B wins in a conflict
				['a'=>'apple'], ['a'=>'anise'], ['a'=>'avocado'],
				['a'=>'avocado']
			],


			// ====== Deeper objects ======
			[ //Two levels deep changed primitive
				['fruit'=>['a'=>'apple']], ['fruit'=>['a'=>'apple']], ['fruit'=>['a'=>'avocado']],
				['fruit'=>['a'=>'avocado']]
			],
			[
				['fruit'=>['a'=>'apple']], ['fruit'=>['a'=>'avocado']], ['fruit'=>['a'=>'apple']],
				['fruit'=>['a'=>'avocado']]
			],
			[ //Two levels deep, collision change primitive
				['fruit'=>['a'=>'apple']], ['fruit'=>['a'=>'anise']], ['fruit'=>['a'=>'avocado']],
				['fruit'=>['a'=>'avocado']]
			],
			[ //Two levels deep, added to numeric array
				['fruits'=>['apple']], ['fruits'=>['apple']], ['fruits'=>['apple', 'banana']],
				['fruits'=>['apple', 'banana']]
			],
			[ //Two levels deep, a and b BOTH add to numeric array
				['fruits'=>['apple']], ['fruits'=>['apple','banana']], ['fruits'=>['apple', 'cucumber']],
				['fruits'=>['apple', 'banana', 'cucumber']]
			],
			[
				['fruits'=>['apple']], ['fruits'=>['apple','banana']], ['fruits'=>['apple'],'vegetables'=>['celery']],
				['fruits'=>['apple', 'banana'],'vegetables'=>['celery']]
			],

			// 'replace simple key with nested object'
			[
				[], ['a'=>'apple', 'b'=>'banana'], ['a'=>['fruit'=>'apple', 'vegetable'=>'asparagus'], 'b'=>'banana'],
				['a'=>['fruit'=>'apple', 'vegetable'=>'asparagus'], 'b'=>'banana']
			],
			// 'replace nested object with simple key
			[
				['a'=>['fruit'=>'apple', 'vegetable'=>'asparagus']],
				['a'=>['fruit'=>'apple', 'vegetable'=>'asparagus']],
				['a'=>'apple'],
				['a'=>'apple'],
			],



			// 'delete in array and collision add', ->
			[
				['one', 'two'], ['one', 'two', 'three'], ['one'],
				['one', 'three']
			],

			// 'null values in object'
			[
				['a'=>null], ['a'=>['b'=>null]], ['a'=>null],
				['a'=>['b'=>null]]
			]




		];
	}




}

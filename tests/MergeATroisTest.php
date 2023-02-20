<?php

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class MergeATroisTest extends TestCase {

    public function testInvalidArgs() {
        $this->expectException(TypeError::class);
        JWadhams\MergeATrois::merge(['a'], ['b'], 'c');
    }


    #[DataProvider('isNumericArrayProvider')]
    public function testIsNumericArray($array, $is_numeric){
        self::assertEquals(
            $is_numeric,
            JWadhams\MergeATrois::is_numeric_array($array)
        );
    }

    public static function isNumericArrayProvider(): array {
        return [
            [ ['a', 'b', 'c'], true ],
            [ [0=>'a', 1=>'b'], true ],
            [ [0=>'a', 2=>'c'], false ],
            [ ['0'=>'a', '1'=>'b'], true ],
            [ [], true ],
            [ [0=>'a', 'b'=>'banana'], false ],
            [ ['a'=>'apple', 'b'=>'banana'], false ],
            [ 'string', false],
            [ 666, false],
        ];
    }

    public function testPreservesSemiNumeric(){
        $original = $a = $b = $expected_result = [42=>'banana', 69=>'peach'];
        self::assertEquals(
            $expected_result,
            JWadhams\MergeATrois::merge($original, $a, $b)
        );
    }


    #[DataProvider('provideMergeNumericArrays')]
    public function testMerge($original, $a, $b, $result){
        self::assertEquals(
            $result,
            JWadhams\MergeATrois::merge($original, $a, $b)
        );
    }

    public static function provideMergeNumericArrays(): array
    {
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

            //====== Simple numeric array inserts ======
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
            [ //Each adds something different, B order then A order
                [], ['apple'], ['banana'],
                ['banana','apple']
            ],
            [
                ['apple'], ['apple'], ['apple', 'banana'],
                ['apple','banana']
            ],


            // ====== Simple numeric array element deletion ======
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
            [ // One deletes, but result array is not empty
                ['apple', 'banana'], ['apple'], ['apple', 'banana'],
                ['apple']
            ],


            // ====== Changes in numeric arrays ======
            [
                ['apple'], ['apple'], ['APPLE'],
                ['APPLE']
            ],


            // ====== Deeper objects ======
            [ //Two levels deep, added to numeric array
                ['fruits'=>['apple']], ['fruits'=>['apple']], ['fruits'=>['apple', 'banana']],
                ['fruits'=>['apple', 'banana']]
            ],
            [ //Two levels deep, a and b BOTH add to numeric array. B's order wins
                ['fruits'=>['apple']], ['fruits'=>['apple','banana']], ['fruits'=>['apple', 'cucumber']],
                ['fruits'=>['apple', 'cucumber', 'banana']]
            ],
            [
                ['fruits'=>['apple']], ['fruits'=>['apple','banana']], ['fruits'=>['apple'],'vegetables'=>['celery']],
                ['fruits'=>['apple', 'banana'],'vegetables'=>['celery']]
            ],



            // 'delete in array and collision add', ->
            [
                ['one', 'two'], ['one', 'two', 'three'], ['one'],
                ['one', 'three']
            ],


            [ //A and B both add content to numeric array, B order wins
                [], ['a' => ['apple']], ['a'=>['asparagus']],
                ['a' => ['asparagus', 'apple']]
            ],


            //If either A or B has no changes (including in a recursive call inside an associative property) just return the other. This can save us some heartache in the order of numeric array elements, and should also be a hair faster on really big merges
            [
                [1,2,3], [1,2,3], [3,2,1],
                [3,2,1]
            ],
            [
                [1,2,3], [3,2,1], [1,2,3],
                [3,2,1]
            ]
        ];
    }

    public static function provideAssociativeData(): array
    {
        return [
            // No changes
            [
                ['a'=>'apple'], ['a'=>'apple'], ['a'=>'apple'],
                ['a'=>'apple']
            ],
            [
                ['fruit'=>['a'=>'apple']], ['fruit'=>['a'=>'apple']], ['fruit'=>['a'=>'apple']],
                ['fruit'=>['a'=>'apple']]
            ],

            // Simple associative inserts
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

            // Associative property deletions
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

            // 'null values in object'
            [
                ['a'=>null], ['a'=>['b'=>null]], ['a'=>null],
                ['a'=>['b'=>null]]
            ],

            // JSON has a way to differentiate empty object from empty array, but PHP has no way to differentiate empty associative array from empty numeric array. If an item changes from empty array to object, we certainly want the object to win
            [
                json_decode('{"finance":{}}', true),
                json_decode('{"finance":{}}', true),
                json_decode('{"finance":{"pay":"cash"}}', true),
                json_decode('{"finance":{"pay":"cash"}}', true),
            ],
            [
                json_decode('{"finance":[]}', true),
                json_decode('{"finance":[]}', true),
                json_decode('{"finance":{"pay":"cash"}}', true),
                json_decode('{"finance":{"pay":"cash"}}', true),
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

            // New complex children need merging
            [
                [], ['a'=>['fruit'=>'apple']], ['a'=>['vegetable'=>'asparagus']],
                ['a'=>['fruit'=>'apple', 'vegetable'=>'asparagus']]
            ],
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
        ];
    }

    /**
     * The goal of this test is to cover merge behavior for associative data,
     * AND to prove that associative behavior is never impacted by the new $shouldMergeNumericArrays flag
     */
    #[DataProvider('provideAssociativeData')]
    public function testAssociativeMerge($original, $a, $b, $result): void
    {
        self::assertEquals(
            $result,
            JWadhams\MergeATrois::merge($original, $a, $b, shouldMergeNumericArrays: true)
        );
        self::assertEquals(
            $result,
            JWadhams\MergeATrois::merge($original, $a, $b, shouldMergeNumericArrays: false)
        );
    }

    #[DataProvider('provideShouldNotMergeNumericArrays')]
    public function testShouldNotMergeNumericArrays($original, $a, $b, $result): void
    {
        self::assertEquals(
            $result,
            JWadhams\MergeATrois::merge($original, $a, $b, shouldMergeNumericArrays: false)
        );
    }

    public static function provideShouldNotMergeNumericArrays(): array
    {
        return [
            // ====== No changes ======
            'empty arrays' => [
                [], [], [],
                []
            ],
            'array content not changed' => [
                ['apple'], ['apple'], ['apple'],
                ['apple']
            ],

            //====== Simple numeric array inserts ======
            'a inserts, b is identified as "no change"' => [
                [], ['apple'], [],
                ['apple']
            ],
            'b inserts' => [
                [], [], ['apple'],
                ['apple']
            ],
            'a and b insert same content' => [
                [], ['apple'], ['apple'],
                ['apple']
            ],

            'a and b insert different content, b wins' => [
                [], ['apple'], ['banana'],
                ['banana']
            ],
            'original has content, b inserts more' => [
                ['apple'], ['apple'], ['apple', 'banana'],
                ['apple','banana']
            ],


            // ====== Simple numeric array element deletion ======
            'a deletes content' => [
                ['apple'], [], ['apple'],
                []
            ],
            'b deletes content' => [
                ['apple'], ['apple'], [],
                []
            ],
            'Both delete content' => [
                ['apple'], [], [],
                []
            ],
            'B deletes, but result array is not empty' => [
                ['apple', 'banana'], ['apple', 'banana'], ['apple'],
                ['apple']
            ],


            // ====== Changes in numeric arrays ======
            'B replaces an element' => [
                ['apple'], ['apple'], ['APPLE'],
                ['APPLE']
            ],


            // ====== Deeper objects ======
            'within associative object, a adds to numeric array, B is no-change' => [
                ['fruits'=>['apple']], ['fruits'=>['apple', 'banana']], ['fruits'=>['apple']],
                ['fruits'=>['apple', 'banana']]
            ],
            'within associative object, B adds to numeric array' => [
                ['fruits'=>['apple']], ['fruits'=>['apple']], ['fruits'=>['apple', 'banana']],
                ['fruits'=>['apple', 'banana']]
            ],
            'within associative object, both add to numeric array, only B wins' => [
                ['fruits'=>['apple']], ['fruits'=>['apple','banana']], ['fruits'=>['apple', 'cucumber']],
                ['fruits'=>['apple', 'cucumber']]
            ],
            'mixture of associative inserts and numeric inserts, B.fruits is identified as no change so A.fruits wins' => [
                ['fruits'=>['apple']], ['fruits'=>['apple','banana']], ['fruits'=>['apple'],'vegetables'=>['celery']],
                ['fruits'=>['apple','banana'],'vegetables'=>['celery']]
            ],

            'Adds and deletes in A are lost, only B is preserved' => [
                ['one', 'two'], ['one', 'two', 'three'], ['one'],
                ['one']
            ],

            //If either A or B has no changes just return the other. This can save us some heartache in the order of numeric array elements, and should also be a hair faster on really big merges
            [
                [1,2,3], [1,2,3], [3,2,1],
                [3,2,1]
            ],
            [
                [1,2,3], [3,2,1], [1,2,3],
                [3,2,1]
            ]
        ];
    }

}

<?php

use Vinelab\Minion\Dictionary;

/**
 * @author Abed Halawi <abed.halawi@vinelab.com>
 */
class DictionaryTest extends PHPUnit\Framework\TestCase
{
    public function test_initializing_dictionary()
    {
        $dict = Dictionary::make(['nick' => 'cave', 'back' => 'seeds']);

        $this->assertEquals('cave', $dict->nick);
        $this->assertEquals('seeds', $dict->back);
    }

    public function test_initializing_with_object()
    {
        $data = new StdClass();
        $data->into = 'my arms';
        $data->believe = 'angels';

        $dict = Dictionary::make($data);

        $this->assertEquals('my arms', $data->into);
        $this->assertEquals('angels', $data->believe);
    }

    public function test_array_representation()
    {
        $flying = new StdClass();
        $flying->wings = 2;
        $flying->doors = 10;

        $data = [
            'people' => 'aint',
            'no' => 'good',
            'ticktok' => true,
            'blabla' => 123,
            'floating' => 03.12,
            'parteeeyyy',
            'something' => [
                'goes' => 'really',
                'deep',
            ],
            'flying' => $flying,
        ];

        $dict = Dictionary::make($data);

        $expected = $data;
        $expected['flying'] = (array) $data['flying'];

        $this->assertEquals($expected, $dict->toArray());
    }

    public function test_allows_checking_attributes()
    {
        $data = ['lime' => 'tree', 'cherry'];
        $dict = Dictionary::make($data);

        $this->assertTrue(isset($dict->lime));
        $this->assertFalse(isset($dict->nooooppeee));
        $this->assertTrue(empty($dict->nogood));
        $this->assertFalse(empty($dict->lime));
        $this->assertNull($dict->nogood);
        $this->assertNull($dict->cherry);
    }
}

<?php

declare(strict_types=1);

namespace AppUtilsTests\TestSuites;

use AppUtils\ArrayDataCollection;
use TestClasses\BaseTestCase;
use function AppUtils\parseVariable;

class ArrayDataCollectionTest extends BaseTestCase
{
    // region: _Tests

    public function test_getString() : void
    {
        $tests = array(
            'bool' => array(
                'value' => true,
                'expected' => ''
            ),
            'string' => array(
                'value' => 'string',
                'expected' => 'string'
            ),
            'null' => array(
                'value' => null,
                'expected' => ''
            ),
            'int' => array(
                'value' => 42,
                'expected' => '42'
            ),
            'float' => array(
                'value' => 14.78,
                'expected' => '14.78'
            ),
            'array' => array(
                'value' => array('data' => 'here'),
                'expected' => ''
            )
        );

        $collection = $this->create($tests);

        foreach($tests as $name => $test)
        {
            $this->assertSame(
                $test['expected'],
                $collection->getString($name),
                $this->renderMessage($name, $test)
            );
        }
    }

    public function test_getBool() : void
    {
        $tests = array(
            // FALSE values
            'string' => array(
                'value' => 'string',
                'expected' => false
            ),
            'null' => array(
                'value' => null,
                'expected' => false
            ),
            'int' => array(
                'value' => 42,
                'expected' => false
            ),
            'float' => array(
                'value' => 14.78,
                'expected' => false
            ),
            'array' => array(
                'value' => array('data' => 'here'),
                'expected' => false
            ),

            // TRUE values
            'integer_one' => array(
                'value' => 1,
                'expected' => true
            ),
            'bool' => array(
                'value' => true,
                'expected' => true
            ),
            'yes' => array(
                'value' => 'yes',
                'expected' => true
            ),
            'true' => array(
                'value' => 'true',
                'expected' => true
            )
        );

        $collection = $this->create($tests);

        foreach($tests as $name => $test)
        {
            $this->assertSame(
                $test['expected'],
                $collection->getBool($name),
                $this->renderMessage($name, $test)
            );
        }
    }

    public function test_getInt() : void
    {
        $tests = array(
            'bool' => array(
                'value' => true,
                'expected' => 0
            ),
            'null' => array(
                'value' => null,
                'expected' => 0
            ),
            'array' => array(
                'value' => array('data' => 'here'),
                'expected' => 0
            ),
            'int' => array(
                'value' => 42,
                'expected' => 42
            ),
            'int-string' => array(
                'value' => '42',
                'expected' => 42
            ),
            'float' => array(
                'value' => 14.78,
                'expected' => 14
            ),
            'float-string' => array(
                'value' => '25.493',
                'expected' => 25
            )
        );

        $collection = $this->create($tests);

        foreach($tests as $name => $test)
        {
            $this->assertSame(
                $test['expected'],
                $collection->getInt($name),
                $this->renderMessage($name, $test)
            );
        }
    }

    public function test_getFloat() : void
    {
        $tests = array(
            'bool' => array(
                'value' => true,
                'expected' => 0.0
            ),
            'null' => array(
                'value' => null,
                'expected' => 0.0
            ),
            'int' => array(
                'value' => 42,
                'expected' => 42.0
            ),
            'int-string' => array(
                'value' => '42',
                'expected' => 42.0
            ),
            'float' => array(
                'value' => 14.78,
                'expected' => 14.78
            ),
            'float-string' => array(
                'value' => '78.456',
                'expected' => 78.456
            ),
            'array' => array(
                'value' => array('data' => 'here'),
                'expected' => 0.0
            )
        );

        $collection = $this->create($tests);

        foreach($tests as $name => $test)
        {
            $this->assertSame(
                $test['expected'],
                $collection->getFloat($name),
                $this->renderMessage($name, $test)
            );
        }
    }

    public function test_getArray() : void
    {
        $tests = array(
            'null' => array(
                'value' => null,
                'expected' => array()
            ),
            'array' => array(
                'value' => array('data' => 'here'),
                'expected' => array('data' => 'here')
            ),
            'float' => array(
                'value' => 14.78,
                'expected' => array()
            )
        );

        $collection = $this->create($tests);

        foreach($tests as $name => $test)
        {
            $this->assertSame(
                $test['expected'],
                $collection->getArray($name),
                $this->renderMessage($name, $test)
            );
        }
    }

    public function test_getJSON() : void
    {
        $tests = array(
            'null' => array(
                'value' => null,
                'expected' => array()
            ),
            'boolean' => array(
                'value' => 'true',
                'expected' => array()
            ),
            'json' => array(
                'value' => json_encode(array('data' => 'here'), JSON_THROW_ON_ERROR),
                'expected' => array('data' => 'here')
            )
        );

        $collection = $this->create($tests);

        foreach($tests as $name => $test)
        {
            $this->assertSame(
                $test['expected'],
                $collection->getJSONArray($name),
                $this->renderMessage($name, $test)
            );
        }
    }

    public function test_getJSON_Exception() : void
    {
        $collection = ArrayDataCollection::create(array('json' => 'not valid JSON'));

        $this->expectExceptionCode(ArrayDataCollection::ERROR_JSON_DECODE_FAILED);

        $collection->getJSONArray('json');
    }

    public function test_setKey() : void
    {
        $collection = ArrayDataCollection::create();

        $this->assertNull($collection->getKey('foo'));

        $collection->setKey('foo', 'string');

        $this->assertSame('string', $collection->getKey('foo'));
    }

    public function test_setKey_overwrite() : void
    {
        $collection = ArrayDataCollection::create(array('foo' => 'bar'));

        $this->assertSame('bar', $collection->getKey('foo'));

        $collection->setKey('foo', 'overwritten');

        $this->assertSame('overwritten', $collection->getKey('foo'));
    }

    public function test_setKeys() : void
    {
        $collection = ArrayDataCollection::create(array(
            'existing' => 'value'
        ));

        $collection->setKeys(array(
            'foo' => 'bar',
            'existing' => 'overwritten'
        ));

        $this->assertSame('bar', $collection->getKey('foo'));
        $this->assertSame('overwritten', $collection->getKey('existing'));
    }

    public function test_combine() : void
    {
        $collectionA = ArrayDataCollection::create()
            ->setKey('foo', 'bar_A')
            ->setKey('a_only', 'value_A');

        $collectionB = ArrayDataCollection::create()
            ->setKey('foo','bar_B')
            ->setKey('b_only', 'value_B');

        $collectionC = $collectionA->combine($collectionB);

        $this->assertSame('bar_B', $collectionC->getKey('foo'));
        $this->assertSame('value_A', $collectionC->getKey('a_only'));
        $this->assertSame('value_B', $collectionC->getKey('b_only'));
    }

    public function test_mergeWith() : void
    {
        $collectionA = ArrayDataCollection::create()
            ->setKey('foo', 'bar_A')
            ->setKey('a_only', 'value_A');

        $collectionB = ArrayDataCollection::create()
            ->setKey('foo','bar_B')
            ->setKey('b_only', 'value_B');

        $collectionA->mergeWith($collectionB);

        $this->assertSame('bar_B', $collectionA->getKey('foo'));
        $this->assertSame('value_A', $collectionA->getKey('a_only'));
        $this->assertSame('value_B', $collectionA->getKey('b_only'));
    }

    // endregion

    // region: Support methods

    private function renderMessage(string $name, array $test) : string
    {
        return
            '['.$name.'] did not match expected value:'.PHP_EOL.
            parseVariable($test['expected'])->enableType()->toString();
    }

    private function create(array $tests) : ArrayDataCollection
    {
         return ArrayDataCollection::create($this->compileData( $tests));
    }

    private function compileData(array $tests) : array
    {
        $data = array();

        foreach($tests as $name => $test)
        {
            $data[$name] = $test['value'];
        }

        return $data;
    }

    // endregion
}
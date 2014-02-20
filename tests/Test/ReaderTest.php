<?php

namespace Indigo\Csv\Test;

use Indigo\Csv\Reader;
use Indigo\Csv\CsvFileObject;

class ReaderTest extends \PHPUnit_Framework_TestCase
{
    protected $reader;

    public function setUp()
    {
        $this->reader = new Reader(realpath(__DIR__ . '/../') . '/test.csv', array('header' => true));
    }

    public function testFile()
    {
        $this->assertInstanceOf('Indigo\\Csv\\CsvFileObject', $file = $this->reader->getFile());
        $this->assertInstanceOf('Indigo\\Csv\\Csv', $this->reader->setFile($file));
    }

    public function testReader()
    {
        $test = array(
            array(
                'name' => 'John Doe',
                'age' => 'Unknown',
            ),
            array(
                'name' => 'Jane Doe',
                'age' => 'Unknown',
            ),
        );

        $this->assertEquals($this->reader->parse(), $test);
    }

    public function testNoneStrict()
    {
        $csvFile = new CsvFileObject(realpath(__DIR__ . '/../') . '/inconsistent.csv', 'rb');
        $reader = new Reader($csvFile, array('strict' => false, 'header' => true));

        $test = array(
            array(
                'name' => 'John Doe',
                'age' => 'Unknown',
            ),
            array('name' => 'Jane Doe'),
        );

        $this->assertEquals($reader->parse(), $test);
    }

    public function testSettings()
    {
        $csvFile = new CsvFileObject(realpath(__DIR__ . '/../') . '/settings.csv', 'rb');
        $reader = new Reader($csvFile, array(
            'delimiter' => ';',
            'enclosure' => "'",
            'header' => true,
        ));

        $test = array(
            array(
                'name' => 'John Doe',
                'age' => 'Unknown',
            ),
            array(
                'name' => 'Jane Doe',
                'age' => 'Unknown',
            ),
        );

        $this->assertEquals($reader->parse(), $test);
    }

    /**
     * @expectedException UnexpectedValueException
     */
    public function testInconsistent()
    {
        $csvFile = new CsvFileObject(realpath(__DIR__ . '/../') . '/inconsistent.csv', 'rb');
        $reader = new Reader($csvFile);
        $reader->parse();
    }
}

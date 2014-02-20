<?php

namespace Indigo\Csv\Test;

use Indigo\Csv\Writer;
use Indigo\Csv\CsvFileObject;

class WriterTest extends \PHPUnit_Framework_TestCase
{
    protected $writer;

    protected $header = array('name', 'age');

    protected $test = array(
        array(
            'name' => 'John Doe',
            'age' => 'Unknown',
        ),
        array(
            'name' => 'Jane Doe',
            'age' => 'Unknown',
        ),
    );

    protected $inconsistent = array(
        array(
            'name' => 'John Doe',
            'age' => 'Unknown',
        ),
        array(
            'name' => 'Jane Doe',
        ),
    );

    public function setUp()
    {
        $csvFile = new CsvFileObject(realpath(__DIR__ . '/../') . '/writer.csv', 'w+');
        $this->writer = new Writer($csvFile);
    }

    public function tearDown()
    {
        unlink(realpath(__DIR__ . '/../') . '/writer.csv');
    }

    public function testWriter()
    {
        $this->assertGreaterThan(0, $this->writer->writeHeader($this->header));
        $this->assertTrue($this->writer->writeLines($this->test));
    }

    public function testWriteLine()
    {
        $this->assertGreaterThan(0, $this->writer->writeLine($this->header));
    }

    public function testWriteAssoc()
    {
        $this->assertTrue($this->writer->writeAssoc($this->test));
    }

    public function testWriteAdvanced()
    {
        $this->writer->setOptions(array(
            'delimiter' => ';',
            'newline' => "\r\n"
        ));

        $this->assertTrue($this->writer->writeAssoc($this->test));
    }

    public function testWriteNonStrict()
    {
        $this->writer->setOptions(array('strict' => false));

        $this->writer->writeAssoc($this->inconsistent);
    }

    /**
     * @expectedException UnexpectedValueException
     */
    public function testWriteInconsistent()
    {
        $this->writer->writeAssoc($this->inconsistent);
    }

    /**
     * @expectedException LogicException
     */
    public function testWriteFrozen()
    {
        $this->writer->writeAssoc($this->test);

        $this->writer->writeHeader($this->header);
    }

    /**
     * @expectedException LogicException
     */
    public function testFrozenOptions()
    {
        $this->writer->writeHeader($this->header);

        $this->writer->setOptions(array('delimiter' => '|'));
    }

    public function testFrozen()
    {
        $this->assertFalse($this->writer->isFrozen());
    }

    public function testReset()
    {
        $this->assertTrue($this->writer->reset());
    }

    public function testRowConsistency()
    {
        $this->writer->writeHeader($this->header);

        $this->assertTrue($this->writer->checkRowConsistency($this->inconsistent[0]));
        $this->assertFalse($this->writer->checkRowConsistency($this->inconsistent[1]));
    }
}

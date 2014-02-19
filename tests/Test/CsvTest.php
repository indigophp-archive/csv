<?php

namespace Indigo\Csv\Test;

use Indigo\Csv\Csv;
use Indigo\Csv\CsvFileObject;

abstract class CsvTest extends \PHPUnit_Framework_TestCase
{
    protected $csv;

    public function setUp()
    {
        $csvFile = new CsvFileObject(realpath(__DIR__ . '/../') . '/test.csv', 'w+');
        $csvFile->setCsvControl(':');
        $this->csv = new Csv($csvFile);
    }

    public function atestFile()
    {
        $this->assertInstanceOf('Indigo\\Csv\\CsvFileObject', $file = $this->csv->getFile());
        $this->assertInstanceOf('Indigo\\Csv\\Csv', $this->csv->setFile($file));
    }

    public function atestSimple()
    {
        $this->csv->setHeader(array('name', 'age'))
            ->setDelimiter(',')
            ->setEnclosure('"')
            ->setNewline("\n");
        $this->assertEquals($this->csv->getHeader(), array('name', 'age'));

        $test = array(
            'name' => 'John Doe',
            'age' => 'Unknown',
        );

        $this->csv->writeLine($test);

        $this->assertEquals($this->csv->parse(), array($test));

        $this->csv->getFile()->rewind();

        $test = array(
            $test,
            array(
                'name' => 'Jane Doe',
                'age' => 'Unknown',
            )
        );

        $this->csv->writeLines($test);
        $this->assertEquals($this->csv->parse(), $test);

        // var_dump($this->csv->parse()); exit;
    }
}

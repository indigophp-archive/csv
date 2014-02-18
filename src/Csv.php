<?php

/*
 * This file is part of the Indigo Csv package.
 *
 * (c) Indigo Development Team
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Indigo\Csv;

use InvalidArgumentException;
use UnexpectedValueException;
use BadMethodCallException;

/**
 * Csv class
 *
 * Csv object which operates on a CsvFileObject
 *
 * @author Márk Sági-Kazár <mark.sagikazar@gmail.com>
 */
class Csv
{
    /**
     * Csv file
     *
     * @var CsvFileObject
     */
    protected $file;

    protected $header = array();

    protected $strict = true;

    protected $delimiter = ',';
    protected $enclosure = '"';
    protected $newline = "\n";
    protected $escape = '\\';
    protected $fileMode;

    public function __construct($file)
    {
        if (is_string($file)) {
            $file = new CsvFileObject($file, 'w+');
        }

        if (!$file instanceof CsvFileObject) {
            throw new InvalidArgumentException('Invalid CsvFileObject');
        }

        $this->file = $file;
    }

    public function setNewline($newline)
    {
        $this->newline = $newline;

        $this->file->setNewline($newline);

        return $this;
    }

    protected function ensureHeader()
    {
        static $written = false;

        if ($written === false) {
            $written = true;

            if (!empty($this->header)) {
                // Ensure headers are written at the beginning of the file
                $this->file->rewind();

                $this->writeLine($this->header);
            }
        }

        return $written;
    }

    public function checkRowConsistency($line)
    {
        if ($this->strict !== true) {
            return true;
        }

        static $columnCount;

        if (is_null($columnCount)) {
            if (!empty($this->header)) {
                $columnCount = count($this->header);
            } else {
                $columnCount = count($line);
            }
        }

        return count($line) === $columnCount;
    }

    public function writeLine($line)
    {
        $this->ensureHeader();

        if (true !== $this->checkRowConsistency($line)) {
            throw new UnexpectedValueException('Given line is inconsistent with the document.');
        }

        return $this->file->fputcsv($line, $this->delimiter, $this->enclosure);
    }

    public function writeLines($lines)
    {
        foreach ($lines as $line) {
            $this->writeLine($line);
        }
    }

    public function parse()
    {
        $lines = array();

        $this->file->rewind();

        while ($this->file->valid()) {
            $line = $this->file->fgetcsv();

            if (empty($lines) and $line === $this->header or $line === array(null)) {
                continue;
            }

            $lines[] = $this->parseLine($line);
        }

        return $lines;
    }

    protected function parseLine(array $line)
    {
        if (!empty($this->header)) {
            $header = $this->header;

            if (true !== $this->checkRowConsistency($line)) {
                throw new UnexpectedValueException('Given line is inconsistent with the document.');
            }

            $header = array_slice($header, 0, count($line));

            $line = array_combine($header, $line);
        }

        return $line;
    }

    public function __call($method, $arguments)
    {
        if (strpos($method, 'set') === 0 and property_exists($this, $property = lcfirst(substr($method, 3)))) {
            $value = reset($arguments);
            isset($this->{$property}) and $type = gettype($this->{$property});

            if (isset($type) and $type !== gettype($value)) {
                throw new InvalidArgumentException('Property ' . $property . ' should be of type ' . $type);
            }

            $this->{$property} = $value;

            return $this;
        } elseif (strpos($method, 'get') === 0 and property_exists($this, $property = lcfirst(substr($method, 3)))) {
            return $this->{$property};
        } else {
            throw new BadMethodCallException('Method ' . $method . ' does not exists.');
        }
    }
}

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

use SplFileObject;

/**
 * Csv File Object
 *
 * @author Márk Sági-Kazár <mark.sagikazar@gmail.com>
 */
class CsvFileObject extends SplFileObject
{
    /**
     * New line character
     *
     * @var string
     */
    protected $newline = "\n";

    /**
     * Set new line character(s)
     *
     * @param string $newline
     */
    public function setNewline($newline)
    {
        $this->newline = $newline;

        return $this;
    }

    /**
     * Check whether temp should be used when writting Csv
     *
     * @param  string $delimiter
     * @param  string $enclosure
     * @return boolean
     */
    public function isSpecial($delimiter, $enclosure)
    {
        return PHP_VERSION_ID < 50400 or $this->newline !== "\n" or strlen($delimiter) > 1 or strlen($enclosure) > 1;
    }

    /**
     * Writes the fields array to the file as a CSV line.
     *
     * @param  array   $fields
     * @param  string  $delimiter
     * @param  string  $enclosure
     * @return integer|false
     */
    public function fputcsv($fields, $delimiter = null, $enclosure = null)
    {
        $this->defaultCsvControl($delimiter, $enclosure);

        if ($this->isSpecial($delimiter, $enclosure)) {
            $line = $this->getTempLine($fields, $delimiter, $enclosure);

            // fputcsv() hardcodes "\n" as a new line character
            if ($this->newline !== "\n") {
                $line = rtrim($line, "\n") . $this->newline;
            }

            return $this->fwrite($line);
        }

        return parent::fputcsv($fields, $delimiter, $enclosure);
    }

    /**
     * Temporary output a line to memory to get the line as string
     *
     * @param  array  $fields
     * @param  string $delimiter
     * @param  string $enclosure
     * @return string CSV line
     */
    protected function getTempLine($fields, $delimiter, $enclosure)
    {
        $fp = fopen('php://temp', 'w+');
        fputcsv($fp, $fields, $delimiter, $enclosure);

        rewind($fp);

        $line = '';

        while (feof($fp) === false) {
            $line .= fgets($fp);
        }

        fclose($fp);

        return $line;
    }

    /**
     * Set default CSV controls
     *
     * @param mixed $delimiter Null for default
     * @param mixed $enclosure Null for default
     */
    protected function defaultCsvControl(& $delimiter, & $enclosure)
    {
        $csv = $this->getCsvControl();

        if (is_null($delimiter)) {
            $delimiter = $csv[0];
        }

        if (is_null($enclosure)) {
            $enclosure = $csv[1];
        }
    }
}

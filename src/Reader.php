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

use UnexpectedValueException;

/**
 * Csv Reader class
 *
 * Parse CSV files, convert encoding
 *
 * @author Márk Sági-Kazár <mark.sagikazar@gmail.com>
 */
class Reader extends Csv
{
    public function setHeader($header)
    {
        $this->header = (bool) $header;

        return $this;
    }

    public function parse()
    {
        $lines = array();
        $header = null;

        $this->file->rewind();

        while ($this->file->valid()) {
            $line = $this->file->fgetcsv(
                $this->options['delimiter'],
                $this->options['enclosure'],
                $this->options['escape']
            );

            if ($line === array(null)) {
                continue;
            }

            if ($this->header === true and is_null($header)) {
                $header = $line;
                $this->columnCount = count($line);
                continue;
            }

            $lines[] = $this->parseLine($line, $header);
        }

        return $lines;
    }

    protected function parseLine(array $line, array $keys = null)
    {
        if (true !== $this->checkRowConsistency($line)) {
            throw new UnexpectedValueException('Given line is inconsistent with the document.');
        }

        if (!empty($keys)) {
            $keys = array_slice($keys, 0, count($line));

            $line = array_combine($keys, $line);
        }

        return $line;
    }
}
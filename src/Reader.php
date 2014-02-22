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

use Symfony\Component\OptionsResolver\OptionsResolverInterface;
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
    protected function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        parent::setDefaultOptions($resolver);

        $resolver->setDefaults(array('header' => false));
        $resolver->setAllowedTypes(array('header' => 'bool'));
    }

    public function parse()
    {
        $lines = array();
        $header = null;

        $this->reset();

        while ($this->file->valid()) {
            $line = $this->file->fgetcsv(
                $this->options['delimiter'],
                $this->options['enclosure'],
                $this->options['escape']
            );

            if ($line === array(null)) {
                continue;
            }

            if ($this->options['header'] === true and is_null($header)) {
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
        if ($this->checkRowConsistency($line) === false) {
            throw new UnexpectedValueException('Given line is inconsistent with the document.');
        }

        if (!empty($keys)) {
            $keys = array_slice($keys, 0, count($line));

            $line = array_combine($keys, $line);
        }

        return $line;
    }
}

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
 * Csv Writer class
 *
 * Write data to CSV files
 *
 * @author Márk Sági-Kazár <mark.sagikazar@gmail.com>
 */
class Write extends Csv
{
    protected function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        parent::setDefaultOptions($resolver);

        $resolver->setDefaults(array('header' => array()));
        $resolver->setAllowedTypes(array('header' => 'array'));
    }

    public function setOptions(array $options)
    {
        parent::setOptions($options);

        if (empty($options['header'])) {
            $this->columnCount = null;
        } else {
            $this->columnCount = count($options['header']);
        }

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
}

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

use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\OptionsResolver\Options;
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
     * File URI
     *
     * @var string
     */
    protected $file;

    /**
     * Csv file resource
     *
     * @var resource
     */
    protected $resource;

    /**
     * Options
     *
     * @var Options
     */
    protected $options = array();

    /**
     * File header
     *
     * @var array
     */
    protected $header = array();

    /**
     * Column count checked for row consistency
     *
     * @var integer
     */
    protected $columnCount;

    /**
     * Default options
     *
     * @var array
     */
    protected static $defaultOptions = array(
        'delimiter' => ',',
        'enclosure' => '"',
        'newline' => "\n",
        'escape' => '\\',
        'encoding' => 'UTF-8',
        'strict' => true,
    );

    /**
     * File open mode
     *
     * @var string
     */
    protected static $fileMode = 'r+';

    public function __construct($file, array $options = array())
    {
        if (is_string($file)) {
            $file = new CsvFileObject($file, static::$fileMode);
        }

        if (!$file instanceof CsvFileObject) {
            throw new InvalidArgumentException('Invalid CsvFileObject');
        }

        // Not sure where the pointer is
        $file->rewind();

        $this->file = $file;
        $this->setOptions($options);
    }

    protected function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(static::$defaultOptions);

        $types = array_fill_keys(array_keys(static::$defaultOptions), 'string');
        $types['strict'] = 'bool';
        $resolver->setAllowedTypes($types);
    }

    public function setOptions(array $options)
    {
        static $resolver;

        if (is_null($resolver)) {
            $resolver = new OptionsResolver();
            $this->setDefaultOptions($resolver);
        } else {
            $resolver->setDefaults($this->options);
        }

        $this->options = $resolver->resolve($options);

        return $this;
    }

    public function getHeader()
    {
        return $this->header;
    }

    public function setHeader($header)
    {
        $this->header = $header;

        $this->columnCount = count($header);

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
        if ($this->options['strict'] !== true) {
            return true;
        }

        if (is_null($this->columnCount)) {
            if (!empty($this->header) and is_array($this->header)) {
                $this->columnCount = count($this->header);
            } else {
                $this->columnCount = count($line);
            }
        }

        return count($line) === $this->columnCount;
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

    public function __call($method, $arguments)
    {
        if (strpos($method, 'set') === 0 and $property = lcfirst(substr($method, 3))) {
            $value = reset($arguments);

            if (property_exists($this, $property)) {
                isset($this->{$property}) and $type = gettype($this->{$property});

                if (isset($type) and $type !== gettype($value)) {
                    throw new InvalidArgumentException('Property ' . $property . ' should be of type ' . $type);
                }

                $this->{$property} = $value;
            } elseif (array_key_exists($property, $this->options)) {
                $this->options[$property] = $value;
            }

            return $this;
        } elseif (strpos($method, 'get') === 0 and $property = lcfirst(substr($method, 3))) {
            if (property_exists($this, $property)) {
                return $this->{$property};
            } elseif (array_key_exists($property, $this->options)) {
                return $this->options[$property];
            }
        } else {
            throw new BadMethodCallException('Method ' . $method . ' does not exists.');
        }
    }
}

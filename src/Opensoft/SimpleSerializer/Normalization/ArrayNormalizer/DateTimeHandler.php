<?php

/**
 * This file is part of the Simple Serializer.
 *
 * Copyright (c) 2014 Farheap Solutions (http://www.farheap.com)
 *
 * For the full copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */

namespace Opensoft\SimpleSerializer\Normalization\ArrayNormalizer;

use DateTime;
use Opensoft\SimpleSerializer\Exception\InvalidArgumentException;
use Opensoft\SimpleSerializer\Normalization\ArrayNormalizer;

/**
 * @author Dmitry Petrov <dmitry.petrov@opensoftdev.ru>
 * @author Anton Konovalov <anton.konovalov@opensoftdev.ru>
 */
class DateTimeHandler
{
    /**
     *
     * @param mixed $value
     * @param string $type
     * @param int $direct
     * @return mixed
     */
    public function handle($value, $type, $direct)
    {
        if ($direct == ArrayNormalizer::DIRECTION_SERIALIZE) {
            return $this->serializationHandle($value,$type);
        } elseif ($direct == ArrayNormalizer::DIRECTION_UNSERIALIZE) {
            return $this->unserializationHandle($value, $type);
        }
    }

    /**
     * @param DateTime $value
     * @param string $type
     * @return string
     */
    public function serializationHandle(DateTime $value, $type)
    {
        $dateTimeFormat = $this->extractDateTimeFormat($type, DateTime::ISO8601);

        return $value->format($dateTimeFormat);
    }

    /**
     * Convert serialized value to DateTime object
     * @param string $value
     * @param string $type
     * @return DateTime
     * @throws InvalidArgumentException
     */
    public function unserializationHandle($value, $type)
    {
        // we should not allow empty string as date time argument.
        //It can lead us to unexpected results
        //Only 'null' is possible empty value
        if ($originalValue = trim($value)) {
            $dateTimeFormat = $this->extractDateTimeFormat($type);
            try {
                $value = new DateTime($value);
            } catch (\Exception $e) {
                throw new InvalidArgumentException(sprintf('Invalid DateTime argument "%s"', $value), $e->getCode(), $e);
            }
            // if format was specified in metadata - format and compare parsed DateTime object with original string
            if (isset($dateTimeFormat) && $value->format($dateTimeFormat) !== $originalValue) {
                throw new InvalidArgumentException(sprintf('Invalid DateTime argument "%s"', $originalValue));
            }

            return $value;
        }
        else {
            throw new InvalidArgumentException('DateTime argument should be well formed string');
        }
    }


    /**
     * Extracts specified date time format from given source
     * If source does not contain any format - returns default value
     *
     * @param string $source
     * @param string|null $defaultValue
     * @return string|null
     */
    private function extractDateTimeFormat($source, $defaultValue = null)
    {
        $dateTimeFormat = $defaultValue;

        if (preg_match('/DateTime<(?<type>[a-zA-Z0-9\,\.\s\-\:\/\\\]+)>/', $source, $matches)) {
            $dateTimeFormat = $matches['type'];
            if (defined('\DateTime::' . $dateTimeFormat)) {
                $dateTimeFormat = constant('\DateTime::' . $dateTimeFormat);
            }
        }

        return $dateTimeFormat;
    }
} 
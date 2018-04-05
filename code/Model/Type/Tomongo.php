<?php

/**
 * Common data type conversion from PHP values to Mongo values
 */
class Cm_Mongo_Model_Type_Tomongo
{

    public function any($mapping, $value)
    {
        return $value;
    }

    public function int($mapping, $value)
    {
        return (int)$value;
    }

    public function string($mapping, $value)
    {
        return (string)$value;
    }

    public function float($mapping, $value)
    {
        if ($mapping->precision) {
            if ($mapping->mode) {
                return round((float)$value, (int)$mapping->precision, (int)$mapping->mode);
            } else {
                return round((float)$value, (int)$mapping->precision);
            }
        }
        return (float)$value;
    }

    public function bool($mapping, $value)
    {
        return (bool)$value;
    }

    public function MongoId($mapping, $value)
    {
        if ($value instanceof \MongoDB\BSON\ObjectId) {
            return $value;
        }
        if (is_string($value)) {
            return new \MongoDB\BSON\ObjectId($value);
        }
        return NULL;
    }

    public function MongoDate($mapping, $value)
    {
        if ($value instanceof \MongoDB\BSON\UTCDateTime) {
            return $value;
        } else if (is_array($value) && isset($value['sec'])) {
            return new \MongoDB\BSON\UTCDateTime($value['sec'], isset($value['usec']) ? $value['usec'] : 0);
        }

        $value = $this->timestamp($mapping, $value);
        if ($value === NULL) {
            return NULL;
        }
        return new \MongoDB\BSON\UTCDateTime((int)$value);
    }

    public function timestamp($mapping, $value)
    {
        if ($value instanceof \MongoDB\BSON\UTCDateTime) {
            return $value->sec;
        } else if ($value === NULL) {
            return NULL;
        } else if (is_int($value) || is_float($value)) {
            return (int)$value;
        } else if ($value instanceof Zend_Date) {
            return $value->getTimestamp();
        } else if (is_array($value) && isset($value['sec'])) {
            return $value['sec'];
        } else if (!strlen($value)) {
            return NULL;
        } else if (ctype_digit($value)) {
            return intval($value);
        } else if (($time = strtotime($value)) !== false) {
            return $time;
        }
        return NULL;
    }

    public function datestring($mapping, $value)
    {
        if (is_string($value)) {
            return $value;
        } else if ($value instanceof Zend_Date) {
            return $value->toString(Varien_Date::DATE_INTERNAL_FORMAT);
        } else if (is_int($value)) {
            $date = new Zend_Date($value);
            return $date->toString(Varien_Date::DATE_INTERNAL_FORMAT);
        } else if ($value instanceof \MongoDB\BSON\UTCDateTime) {
            $date = new Zend_Date($value->sec);
            return $date->toString(Varien_Date::DATE_INTERNAL_FORMAT);
        } else {
            return (string)$value;
        }
    }

    public function set($mapping, $value)
    {
        if (is_string($value) && isset($mapping->split)) {
            $regex = ($mapping->split == 'newline' ? '/[ \t]*[\r\n]+[ \t]*/' : (string)$mapping->split);
            $value = preg_split($regex, trim($value), null, PREG_SPLIT_NO_EMPTY);
        } else if (!is_array($value) || key($value) != 0) {
            $value = array_values((array)$value);
        }
        if ($mapping->subtype) {
            $subtype = (string)$mapping->subtype;
            foreach ($value as &$val) {
                $val = $this->$subtype($mapping, $val);
            }
        }
        return $value;
    }

    public function hash($mapping, $value)
    {
        if (!count($value)) {
            return new ArrayObject;
        }
        if ($mapping->subtype) {
            $subtype = (string)$mapping->subtype;
            foreach ($value as $key => $val) {
                $value[$key] = $this->$subtype($mapping, $val);
            }
        }
        return (array)$value;
    }

    public function enum($mapping, $value)
    {
        return isset($mapping->options->$value) ? (string)$value : NULL;
    }

    public function enumSet($mapping, $value)
    {
        if ($value instanceof Mage_Core_Model_Config_Element) {
            $value = array_keys($value->asCanonicalArray());
        }

        if (!is_array($value) || !count($value)) {
            return array();
        }
        $value = array_unique($value);
        $rejects = array();
        foreach ($value as $val) {
            if (!$this->enum($mapping, $val)) {
                $rejects[] = $val;
            }
        }
        if ($rejects) {
            return array_diff($value, $rejects);
        }
        return $value;
    }

    public function reference($mapping, $value)
    {
        $value = ($value instanceof Varien_Object ? $value->getId() : $value);
        return Mage::getResourceSingleton((string)$mapping->model)->castToMongo('_id', $value);
    }

    public function referenceSet($mapping, $value)
    {
        $ids = array();
        foreach ($value as $item) {
            $id = $item instanceof Varien_Object ? $item->getId() : $item;
            $ids[] = Mage::getResourceSingleton((string)$mapping->model)->castToMongo('_id', $id);
        }
        return $ids;
    }

    public function referenceHash($mapping, $value)
    {
        $items = array();
        $idField = (string)$mapping->id_field;
        if (!$idField) {
            throw new Exception('Cannot cast value to referenceHash, no id_field defined.');
        }
        foreach ($value as $item) {
            $data = $item instanceof Varien_Object ? $item->getData() : $item;
            if (!empty($data[$idField])) {
                $data[$idField] = Mage::getResourceSingleton((string)$mapping->model)->castToMongo('_id', $data[$idField]);
                $items[] = $data;
            }
        }
        return $items;
    }

    public function __call($name, $args)
    {
        return Mage::getSingleton($name)->toMongo($args[0], $args[1]);
    }

}

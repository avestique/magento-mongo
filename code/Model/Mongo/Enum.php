<?php

/**
 * Enum Resource Model
 */
class Cm_Mongo_Model_Mongo_Enum extends Cm_Mongo_Model_Resource_Abstract
{

    public function _construct()
    {
        $this->_init('mongo/enum');
    }

    public function getDefaultLoadFields($object)
    {
        $fields = array(
            'name' => 1,
            'defaults' => 1
        );
        if ($object->getStoreId()) {
            $fields['stores.' . Mage::app()->getStore($object->getStoreId())->getCode()] = 1;
        }
        return $fields;
    }

    /**
     * Load an object
     *
     * @param Mage_Core_Model_Abstract $object
     * @param mixed $value
     * @param string $field field to load by (defaults to model id)
     * @return Mage_Core_Model_Resource_Db_Abstract
     */
    public function load(Mage_Core_Model_Abstract $object, $value, $field = null)
    {
        if (is_null($field)) {
            $field = $this->getIdFieldName();
        }

        $read = $this->_getReadAdapter();
        if ($read && !is_null($value)) {
            echo '<pre><br/>';
            var_dump($field, $value, $object);
            die();
            $select = $this->_getLoadSelect($field, $value, $object);
            $data = $read->fetchRow($select);

            if ($data) {
                $object->setData($data);
            }
        }

        $this->unserializeFields($object);
        $this->_afterLoad($object);

        return $this;
    }

}

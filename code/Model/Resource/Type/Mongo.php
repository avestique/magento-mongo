<?php

/**
 * Loads the database driver
 */
class Cm_Mongo_Model_Resource_Type_Mongo extends Mage_Core_Model_Resource_Type_Db
{
    /**
     * Get the Mongo database adapter
     *
     * @param array|Mage_Core_Model_Config_Element $config Connection config
     * @return Mongo_Database
     */
    public function getConnection($config)
    {
        $configArr = (array)$config;
        $configArr['profiler'] = !empty($configArr['profiler']) && $configArr['profiler']!=='false';

        $conn = $this->_getDbAdapterInstance($configArr);

        /*if (!empty($configArr['initStatements']) && $conn) {
            $conn->query($configArr['initStatements']);
        }*/

        return $conn;

        $conn = ($config instanceof Mage_Core_Model_Config_Element)
            ? Cm_Mongo_Model_Resource_Type_Shim::instance((string)$config->config, $config->asCanonicalArray())
            : Cm_Mongo_Model_Resource_Type_Shim::instance($config['config'], $config);

        // Set profiler
        $conn->set_profiler(array($this, 'start_profiler'), array($this, 'stop_profiler'));

        return $conn;
    }

    /**
     * Create and return DB adapter object instance
     *
     * @param array $configArr Connection config
     * @return Varien_Db_Adapter_Pdo_Mysql
     */
    protected function _getDbAdapterInstance($configArr)
    {
        $className = $this->_getDbAdapterClassName();
        $adapter = new $className($configArr);
        return $adapter;
    }

    /**
     * Retrieve DB adapter class name
     *
     * @return string
     */
    protected function _getDbAdapterClassName()
    {
        return 'Cm_Mongo_Model_Resource_Type_Shim';
        return 'Magento_Db_Adapter_Pdo_Mysql';
    }

    /**
     * @param string $group
     * @param string $query
     * @return string
     */
    public function start_profiler($group, $query)
    {
        $key = "$group::$query";
        Cm_Mongo_Profiler::start($key);
        return $key;
    }

    /**
     * @param string $key
     */
    public function stop_profiler($key)
    {
        Cm_Mongo_Profiler::stop($key);
    }

}

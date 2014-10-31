<?php
/**
 * Created by PhpStorm.
 * User: manuele
 * Date: 31/10/14
 * Time: 16:57
 */

class Webgriffe_Config_Model_Config extends Mage_Core_Model_Config
{
    const OVERRIDE_FILENAME = 'config-override.xml';

    public function loadDb()
    {
        parent::loadDb();
        $this->_loadOverride();
        return $this;
    }

    protected function _loadOverride()
    {
        $etcDir = $this->getOptions()->getEtcDir();
        $file = $etcDir . DS . self::OVERRIDE_FILENAME;
        $merge = clone $this->_prototype;
        $merge->loadFile($file);
        $this->extend($merge);
    }
} 

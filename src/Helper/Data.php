<?php
/**
 * Created by PhpStorm.
 * User: manuele
 * Date: 31/10/14
 * Time: 16:38
 */ 
class Webgriffe_Config_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * @param $websiteCode
     * @param $storeCode
     * @return string
     */
    public function computePathPrefix($websiteCode, $storeCode)
    {
        if (empty($websiteCode) && empty($storeCode)) {
            $pathPrefix = 'default';
            return $pathPrefix;
        } elseif (!empty($websiteCode) && empty($storeCode)) {
            $pathPrefix = 'websites/' . $websiteCode;
            return $pathPrefix;
        } else { // !empty($websiteCode) && !empty($storeCode)
            $pathPrefix = 'stores/' . $storeCode;
            return $pathPrefix;
        }
    }
}

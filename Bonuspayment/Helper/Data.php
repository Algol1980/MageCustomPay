<?php
/**
 * Created by PhpStorm.
 * User: dev01
 * Date: 26.10.16
 * Time: 10:46
 */

class Stage_Bonuspayment_Helper_Data extends Mage_Core_Helper_Abstract
{

    public function getEntityGroup ($name)
    {
        $sDefaultAttributeSetId = Mage::getSingleton('eav/config')
            ->getEntityType('customer')
            ->getDefaultAttributeSetId();
        $coll = Mage::getResourceModel('eav/entity_attribute_group_collection')
        ->addFieldToFilter('attribute_set_id', $sDefaultAttributeSetId)
        ->addFieldToFilter('attribute_group_name', $name)
        ->getFirstItem();
        return $coll;
    }

    /**
     * @param string $groupId
     * @return mixed
     */
    public function getAttributesForGroup($groupId = 'Bonus Account') {
        $group = $this->getEntityGroup($groupId);
        $groupColl = Mage::getResourceModel('customer/attribute_collection');
        return $groupColl->setAttributeGroupFilter($group->getId());
    }
}
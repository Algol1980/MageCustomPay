<?php
/**
 * Created by PhpStorm.
 * User: dev01
 * Date: 25.10.16
 * Time: 17:35
 */
$installer = new Mage_Customer_Model_Entity_Setup('core_setup');
$installer->startSetup();
//
$groupName = 'Bonus Account';


$installer->addAttribute('customer', 'bonus_points', array(
    'input'         => 'text',
    'type'          => 'int',
    'label'         => 'Bonus points',
    'visible'       => 1,
    'required'      => 0,
    'user_defined'  => 1,
    'default'       => 0,
    'group'         => $groupName,
));


$installer->endSetup();
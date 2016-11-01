<?php
/**
 * Created by PhpStorm.
 * User: dev01
 * Date: 26.10.16
 * Time: 17:18
 */
class Stage_Bonuspayment_Model_Observer {

    public function adminhtml_customer_prepare_save ($observer) {
        $customer = $observer->getEvent()->getCustomer();
        $request = $observer->getEvent()->getRequest();
        $groupColl = Mage::helper('bonuspayment')->getAttributesForGroup();
        foreach ($groupColl as $attribute) {
            $code = $attribute->getAttributeCode();
            $customer->setData($code, $request->getPost($code));
        }
    }

    public function sales_order_place_after ($observer) {
        $order = $observer->getEvent()->getOrder();
        $orderId = $order->getRealOrderId();
        $payment_method = $order->getPayment()->getMethodInstance()->getTitle();
        $bonusPayment = Mage::getModel('bonuspayment/bonuspayment')->getTitle();

        if ($payment_method == $bonusPayment ) {

            $total = $order->getGrandTotal();
            $customer = $order->getCustomer();
            $customerId = $customer->getId();
            $bonusPoints = $customer->getBonusPoints() - $total;

            $order = Mage::getModel('sales/order')->loadByIncrementId($orderId);
            $order->setState(Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW, true, 'Payment Success');
            $order->save();

            $customer = Mage::getModel('customer/customer')->load($customerId);
            $customer->addData(array(
                'bonus_points' => $bonusPoints
            ));
            $customer->save();
        }


    }
}
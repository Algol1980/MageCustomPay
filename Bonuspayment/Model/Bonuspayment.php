<?php

/**
 * Created by PhpStorm.
 * User: dev01
 * Date: 25.10.16
 * Time: 16:41
 */
class Stage_Bonuspayment_Model_Bonuspayment extends Mage_Payment_Model_Method_Abstract
{

    protected $_code = 'bonuspayment';
    protected $_canCapture = true;
    protected $_canRefund = true;
    protected $_canAuthorize = true;
    protected $_canRefundInvoicePartial = true;
//    protected $_isInitializeNeeded = true;




    public function isApplicableToQuote($quote, $checksBitMask)
    {
        $bonusPoints = $quote->getCustomer()->getBonusPoints();
        $total = $quote->getBaseGrandTotal();
        if ($checksBitMask) {
            if (empty($bonusPoints) || $bonusPoints < $total) {
                return false;
            }
        }
        return true;
    }

    public function capture(Varien_Object $payment, $amount)
    {
        $order = $payment->getOrder();
        $result = $this->beginPayment($payment, $amount, 'authorize');
        if ($result === false) {
            $errorCode = 'Invalid Data';
            $errorMsg = $this->_getHelper()->__('Error Processing the request');
        } else {
            Mage::log($result, null, $this->getCode() . '.log');

            if ($result['status'] == 1) {
                $payment->setTransactionId($result['transaction_id']);
                $payment->setIsTransactionClosed(1);
            } else {
                Mage::throwException($errorMsg);
            }
        }
        if ($errorMsg) {
            Mage::throwException($errorMsg);
        }

        return $this;
    }

    protected function beginPayment($payment, $amount, $operation)
    {
        $order = $payment->getOrder();
        $orderId = $order->getRealOrderId();
        $payment_method = $order->getPayment()->getMethodInstance()->getTitle();
        $bonusPayment = Mage::getModel('bonuspayment/bonuspayment')->getTitle();

        if ($payment_method == $bonusPayment) {

            $customer = $order->getCustomer();
            $customerId = $customer->getId();
            $bonusPoints = $customer->getBonusPoints();
            $bonusPoints -= $amount;

            $order = Mage::getModel('sales/order')->loadByIncrementId($orderId);
            $order->setState(Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW, true, 'Payment Success');
            $order->save();

            $customer = Mage::getModel('customer/customer')->load($customerId);
            $customer->addData(array(
                'bonus_points' => $bonusPoints
            ));
            $customer->save();
            return array('status' => 1, 'transaction_id' => time());
        } else {
            return false;
        }
    }

    public function refund(Varien_Object $payment, $amount)
    {
        $order = $payment->getOrder();
        $result = $this->returnPayment($payment, $amount);
        if ($result === false) {
            $errorCode = 'Invalid Data';
            $errorMsg = $this->_getHelper()->__('Error Processing the request');
            Mage::throwException($errorMsg);
        }
        return $this;
    }

    protected function returnPayment($payment, $amount)
    {
        $order = $payment->getOrder();
        $customer = Mage::getModel('customer/customer')->load($order->getCustomerId());
        try {
            $bonusPoints = $customer->getBonusPoints();
            $bonusPoints += $amount;

            $customer->addData(array(
                'bonus_points' => $bonusPoints
            ));
            $customer->save();
            return array('status' => 1);
        }
        catch (Exception $exception) {
            return false;
        }


    }
}
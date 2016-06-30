<?php
/**
 * @copyright Copyright (c) 2015 www.magebuzz.com
 */

class Magebuzz_Pricematch_Model_Pricematch extends Mage_Core_Model_Abstract
{
  protected function _construct()
  {
    parent::_construct();
    $this->_init('pricematch/pricematch');
  }

  public function generateCoupon($length = null)
  {
    $rndId = crypt(uniqid(rand(), 1));
    $rndId = strip_tags(stripslashes($rndId));
    $rndId = str_replace(array(".", "$"), "", $rndId);
    $rndId = strrev(str_replace("/", "", $rndId));
    if (!is_null($rndId)) {
      return strtoupper(substr($rndId, 0, $length));
    }
    return strtoupper($rndId);
  }

  public function createCouponCode($discountAmount,$productSku)
  {
    $usesPerCustomer = 1;
    $discountType    = 2;
    $couponExpireIn  = Mage::getStoreConfig('pricematch/general/expired_coupon');
    $usesPerCoupon   = 1;
    $couponLength    = 8;
    $simpleAction    = Mage_SalesRule_Model_Rule::BY_FIXED_ACTION;
    $fromDate = date("Y-m-d H:i:s", Mage::getModel('core/date')->timestamp(time()));
    $toDate   = Mage::helper('pricematch')->getDateToRemoveCoupon($fromDate, $couponExpireIn);

    $customerGroups = Mage::getResourceModel('customer/group_collection')->load()->toOptionArray();
    $found = FALSE;

    foreach ($customerGroups as $group) {
      if ($group['value'] == 0) {
        $found = TRUE;
      }
    }
    if (!$found) {
      array_unshift($customerGroups, array(
          'value' => 0,
          'label' => Mage::helper('salesrule')->__('NOT LOGGED IN'))
      );
    }

    $customerGroupIds = Mage::getResourceModel('customer/group_collection')->toOptionArray();
    $group = array();

    foreach ($customerGroupIds as $cusGroup) {
      $group[] = $cusGroup['value'];
    }

    $model = Mage::getModel('salesrule/rule');
    $couponCode = $this->generateCoupon($couponLength);

    $conditionProduct = Mage::getModel('salesrule/rule_condition_product')
                      ->setType('salesrule/rule_condition_product')
                      ->setAttribute('sku')
                      ->setOperator('==')
                      ->setValue($productSku);

    /** @var Mage_SalesRule_Model_Rule_Condition_Product_Found $conditionProductFound */
    $conditionProductFound = Mage::getModel('salesrule/rule_condition_product_found')
                            ->setConditions(array($conditionProduct));

    /** @var Mage_SalesRule_Model_Rule_Condition_Combine $condition */
    $condition = Mage::getModel('salesrule/rule_condition_combine')
                  ->setConditions(array($conditionProductFound));

    $data = array(
            'name'               => $couponCode,
            'description'        => 'Discount coupon for price match.',
            'is_active'          => 1,
            'website_ids'        => array(1),
            'customer_group_ids' => $group,
            'coupon_type'        => Mage_SalesRule_Model_Rule::COUPON_TYPE_SPECIFIC,
            'coupon_code'        => $couponCode,
            'uses_per_coupon'    => $usesPerCoupon,
            'uses_per_customer'  => $usesPerCustomer,
            'from_date'          => $fromDate,
            'to_date'            => $toDate,
            'is_rss'             => 0,
            'simple_action'      => $simpleAction,
            'conditions_serialized' => serialize($condition->asArray()),
            'conditions'            => $condition,
            'discount_amount'       => $discountAmount,
            'discount_qty'          => 1,
            'discount_step'         => 0,
            'apply_to_shipping'     => 0,
            'simple_free_shipping'  => 0,
            'stop_rules_processing' => 0,
            );
    $model->setData($data);

    $couponArray = array(
      'couponCode'  => $couponCode,
      'expired'   => $toDate
    );
    try {
      $model->save();
    } catch (Exception $e) {
      Mage::logException($e);
      Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
    }
    return $couponArray;
  }
}
<?php
/**
 * @copyright Copyright (c) 2015 www.magebuzz.com
 */

class Magebuzz_Pricematch_Model_System_Email
{
  public function toOptionArray()
  {
    $paramsArray = array(
      'both'          => 'Both Customer & Admin',
      'admin_only'    => 'Admin only',
      'customer_only' => 'Customer only',
      'disable'       => 'Disable'
    );
    return $paramsArray;
  }
}

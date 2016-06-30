<?php
/**
 * @copyright Copyright (c) 2015 www.magebuzz.com
 */

class Magebuzz_Pricematch_IndexController extends Mage_Core_Controller_Front_Action
{
  const STATUS_APPROVED = 1;
  const STATUS_REJECTED = 2;
  const STATUS_PENDING  = 3;

  const XML_PATH_EMAIL_RECIPIENT = 'pricematch/email/send_email_to'; //email admin's
  const XML_PATH_EMAIL_SENDER = 'pricematch/email/email_sender';
  const XML_PATH_EMAIL_TEMPLATE_SENT_TO_ADMIN_REQUEST = 'pricematch/email/email_template_request';
  const XML_PATH_EMAIL_CONFIRMATION_TEMPLATE_SENT_TO_CLIENT_CONFIRM = 'pricematch/email/email_template_confirm';

  protected function _initProduct()
  {
    Mage::dispatchEvent('review_controller_product_init_before', array('controller_action' => $this));
    $categoryId = (int)$this->getRequest()->getParam('category', FALSE);
    $productId = (int)$this->getRequest()->getParam('id');

    $product = $this->_loadProduct($productId);

    if ($categoryId) {
      $category = Mage::getModel('catalog/category')->load($categoryId);
      Mage::register('current_category', $category);
    }

    try {
      Mage::dispatchEvent('review_controller_product_init', array('product' => $product));
      Mage::dispatchEvent('review_controller_product_init_after', array('product' => $product, 'controller_action' => $this));
    }
    catch (Mage_Core_Exception $e) {
      Mage::logException($e);
      return FALSE;
    }

    return $product;
  }

  protected function _loadProduct($productId)
  {
    if (!$productId) {
      return FALSE;
    }

    $product = Mage::getModel('catalog/product')
      ->setStoreId(Mage::app()->getStore()->getId())
      ->load($productId);
    /* @var $product Mage_Catalog_Model_Product */
    if (!$product->getId() || !$product->isVisibleInCatalog() || !$product->isVisibleInSiteVisibility()) {
      return FALSE;
    }

    Mage::register('current_product', $product);
    Mage::register('product', $product);

    return $product;
  }


  public function _getSession()
  {
    return Mage::getSingleton('pricematch/session');
  }

  public function postAction()
  {
    $params = $this->getRequest()->getParams();
    $_product = Mage::getModel('catalog/product')->load($params['product_id']);
    $productPrice = $_product->getPrice();
    $competitorPrice = $this->convertCurrentCurrencyToBaseCurrency($params['competitor_price']);//save price follow base current currency

    $discountedPrice = $_product->getFinalPrice();
    if ($discountedPrice) {
      $productPrice = $discountedPrice;
    }

    $specialPrice = $_product->getSpecialPrice();
    if ($specialPrice) {
      $productPrice = $specialPrice;
    }

    if (Mage::getStoreConfig('pricematch/appearance/enable_captcha')) {
      $code = $this->_getSession()->getCaptchaCode();
      $captcha_code = $params['captcha_code'];

      if ($code != $captcha_code) {
        $result['error'] = TRUE;
        $result['message'] = Mage::helper('pricematch')->__('The security code entered was incorrect. Please try again!');
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
        return;
      }
    }

    if($productPrice == 0){
      $result['error'] = TRUE;
      $result['message'] = Mage::helper('pricematch')->__('The product is being updated. Please back again!');
      $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
      return;
    }

    if(is_numeric($competitorPrice) && $productPrice > $competitorPrice) {
      if ($params) {
        try {
          $model = Mage::getModel('pricematch/pricematch');
          $model->setData($params);

          $discountAmount = $productPrice - $competitorPrice;
          $model->setData('discount_amount', $discountAmount);
          $model->setData('status', self::STATUS_PENDING);

          $now = Mage::getModel('core/date')->gmtTimestamp(now());
          $model->setDate(date('Y-m-d H:i:s', $now));

          $store_data = Mage::app()->getStore()->getData();
          $model->setStoreId($store_data['store_id']);
          $model->setCompetitorPrice($competitorPrice);

          $email_config = $this->getEmailConfig();
          if ($email_config != 'disable') {
            //send email with price is base current currency
            $product_url = $_product->getProductUrl();
            $customerName = $params['customer_name'];
            $symbolBaseCurrency = Mage::app()->getLocale()->currency(Mage::app()->getStore()->getBaseCurrencyCode())->getSymbol();
            $sender = Mage::getStoreConfig(self::XML_PATH_EMAIL_SENDER);
            $dataArray = array(
                        'competitor_price'  => $symbolBaseCurrency.round($competitorPrice, 2),
                        'product_price'     => $symbolBaseCurrency.round($productPrice, 2),
                        'product_name'      => $_product->getName(),
                        'customer_name'     => $customerName,
                        'product_url'       => $product_url,
                        'product_image'     => Mage::helper('catalog/image')->init($_product,'small_image')->resize(150));

            //set for customer
            $postObject_customer = new Varien_Object();
            $postObject_customer->setData($dataArray);
            $template_customer = Mage::getStoreConfig(self::XML_PATH_EMAIL_CONFIRMATION_TEMPLATE_SENT_TO_CLIENT_CONFIRM);
            $recipient_customer = $params['customer_email'];

            //set for admin
            $admin_url = Mage::helper('adminhtml')->getUrl('/adminhtml_pricematch/index/');
            $postObject_admin = new Varien_Object();
            $postObject_admin->setData($dataArray);
            $postObject_admin->setCustomerName($params['customer_name']);
            $postObject_admin->setAdminUrl($admin_url);
            $template_admin = Mage::getStoreConfig(self::XML_PATH_EMAIL_TEMPLATE_SENT_TO_ADMIN_REQUEST);
            $recipient_admin = Mage::getStoreConfig(self::XML_PATH_EMAIL_RECIPIENT);

            if ($email_config == "both") {
              $replyto = Mage::getStoreConfig(self::XML_PATH_EMAIL_SENDER);
              //send to customer
              $this->sendEmail($replyto, $template_customer, $sender, $recipient_customer, $postObject_customer);

              //send to admin
              $this->sendEmail($replyto, $template_admin, $sender, $recipient_admin, $postObject_admin);
            }

            elseif ($email_config == "admin_only") {
              //send to admin
              $replyto = $recipient_customer;
              $this->sendEmail($replyto, $template_admin, $sender, $recipient_admin, $postObject_admin);
            }

            elseif ($email_config == "customer_only") {
              //send to customer
              $replyto = $recipient_admin;
              $this->sendEmail($replyto, $template_customer, $sender, $recipient_customer, $postObject_customer);
            }
          }
          $model->save();
          $result['error'] = FALSE;
          $result['message'] = Mage::helper('pricematch')->__('Your request has been submitted. Thank  you!');
          $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
        }
        catch (Exception $e) {
          $result['error'] = TRUE;
          $result['message'] = Mage::helper('pricematch')->__('An error occured. Please try again later');
          $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
          Mage::logException($e);
          return;
        }
      }
    }
    else {
      $result['error'] = TRUE;
      $result['message'] = Mage::helper('pricematch')->__('Please check new price. It must be number and lower than old one!');
      $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
      return;
    }
  }

  public function getEmailConfig() {
    return Mage::getStoreConfig('pricematch/email/email_recipient_configuration', Mage::app()->getStore());
  }

  public function convertCurrentCurrencyToBaseCurrency($price){
    $baseCurrencyCode = Mage::app()->getStore()->getBaseCurrencyCode();
    $currentCurrencyCode = Mage::app()->getStore()->getCurrentCurrencyCode();
    $oneBaseCurrencyChangeToCurrentCurrency = Mage::helper('directory')->currencyConvert(1, $baseCurrencyCode, $currentCurrencyCode);
    $competitorPriceConvertToBaseCureency = (1/$oneBaseCurrencyChangeToCurrentCurrency)*$price;
    return $competitorPriceConvertToBaseCureency;
  }

  public function sendEmail($replyto, $template, $sender, $recipient, $postObject ){
    $translate = Mage::getSingleton('core/translate');
    $translate->setTranslateInline(FALSE);
    $mailTemplate = Mage::getModel('core/email_template')
      ->setDesignConfig(array('area' => 'frontend'));
    $mailTemplate->setReplyTo($replyto)
      ->sendTransactional(
        $template,
        $sender,
        $recipient,
        null,
        array ('data' =>$postObject,)
      );
    $translate->setTranslateInline(TRUE);
  }

  public function captchaAction()
  {
    require_once(Mage::getBaseDir('lib') . DS . 'captcha' . DS . 'class.simplecaptcha.php');
    //Background Image
    $config['BackgroundImage'] = Mage::getBaseDir('lib') . DS . 'captcha' . DS . "white.png";

    //Background Color- HEX
    $config['BackgroundColor'] = "FFFC00";

    //image height - same as background image
    $config['Height'] = 30;

    //image width - same as background image
    $config['Width'] = 100;

    //text font size
    $config['Font_Size'] = 20;

    //text font style
    $config['Font'] = Mage::getBaseDir('lib') . DS . 'captcha' . DS . "ARLRDBD.TTF";

    //text angle to the left
    $config['TextMinimumAngle'] = 15;

    //text angle to the right
    $config['TextMaximumAngle'] = 45;

    //Text Color - HEX
    $config['TextColor'] = '000000';

    //Number of Captcha Code Character
    $config['TextLength'] = 6;

    //Background Image Transparency
    // 0 - Not Visible, 100 - Fully Visible
    $config['Transparency'] = 70;

    $captcha = new SimpleCaptcha($config);
    //$_SESSION['captcha_code'] = $captcha->Code;
    Mage::getSingleton('pricematch/session')->setData('captcha_code', $captcha->Code);
  }

  public function formAction()
  {
    $this->loadLayout();
    $this->renderLayout();
  }

  public function accountpricematchAction(){
    $this->loadLayout();
    $this->getLayout()->getBlock('head')->setTitle($this->__('My Price Match Requests'));
    $this->renderLayout();
  }
}
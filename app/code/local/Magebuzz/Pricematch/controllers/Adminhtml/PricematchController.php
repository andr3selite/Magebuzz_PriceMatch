<?php
/**
 * @copyright Copyright (c) 2015 www.magebuzz.com
 */

class Magebuzz_Pricematch_Adminhtml_PricematchController extends Mage_Adminhtml_Controller_Action
{
  const XML_PATH_EMAIL_RECIPIENT = 'pricematch/email/send_email_to';
  const XML_PATH_EMAIL_SENDER = 'pricematch/email/email_sender';
  const XML_PATH_EMAIL_TEMPLATE_COUPON_CODE = 'pricematch/email/email_coupon_code_template';
  const XML_PATH_EMAIL_TEMPLATE_PRICEMATCH_REJECTED = 'pricematch/email/email_template_rejected';

  protected function _initAction()
  {
    $this->loadLayout()
      ->_setActiveMenu('pricematch/items')
      ->_addBreadcrumb(Mage::helper('adminhtml')->__('Items Manager'), Mage::helper('adminhtml')->__('Item Manager'));

    return $this;
  }

  public function indexAction()
  {
    $this->_initAction()->renderLayout();
  }

  public function editAction()
  {
    $id          = $this->getRequest()->getParam('id');
    $model       = Mage::getModel('pricematch/pricematch')->load($id);
    $productData = $model->getData();
    $_product    = Mage::getModel('catalog/product')->load($productData['product_id']);
    $model->setData('product_name', $_product->getName());

    $_taxHelper   = Mage::helper('tax');
    $currentPrice = $_taxHelper->getPrice($_product, $_product->getPrice());

    $specialPrice = $_product->getSpecialPrice();
    if ($specialPrice) {
        $currentPrice = $specialPrice;
    }

    $model->setData('current_price', $currentPrice);

    $store_model = Mage::getModel('core/store')->load($productData['store_id']);
    $store_name = $store_model->getName();
    $model->setData('store_name', $store_name);

    if ($model->getId() || $id == 0) {
      $data = Mage::getSingleton('adminhtml/session')->getFormData(TRUE);
      if (!empty($data)) {
        $model->setData($data);
      }

      Mage::register('pricematch_data', $model);

      $this->loadLayout();
      $this->_setActiveMenu('pricematch/items');

      $this->getLayout()->getBlock('head')->setCanLoadExtJs(TRUE);
      $this->getLayout()->getBlock('head')->setCanLoadTinyMce(TRUE);

      $this->_addContent($this->getLayout()->createBlock('pricematch/adminhtml_pricematch_edit'))
        ->_addLeft($this->getLayout()->createBlock('pricematch/adminhtml_pricematch_edit_tabs'));

      $this->renderLayout();
    } else {
      Mage::getSingleton('adminhtml/session')->addError(Mage::helper('pricematch')->__('Item does not exist'));
      $this->_redirect('*/*/');
    }
  }

  public function newAction()
  {
    $this->_forward('edit');
  }

  public function gridAction()
  {
    $this->loadLayout();
    $this->getResponse()->setBody($this->getLayout()->createBlock('pricematch/adminhtml_pricematch_grid')->toHtml());
  }

  public function saveAction()
  {
    if ($data = $this->getRequest()->getPost()) {
      $symbolBaseCurrency = Mage::app()->getLocale()->currency(Mage::app()->getStore()->getBaseCurrencyCode())->getSymbol();
      $updated_status  = $data['status'];

      $id              = $this->getRequest()->getParam('id');
      $pricematchModel = Mage::getModel('pricematch/pricematch');
      $row             = $pricematchModel->load($id);

      $before_status   = $row['status'];
      $competitor_link = $row['competitor_link'];
      $_product = Mage::getModel('catalog/product')->load($row['product_id']);

      $message = Mage::getStoreConfig('pricematch/email/message_reply', Mage::app()->getStore());
      $additional_message = $data['information'];
      if ($additional_message != null)
      {
        $additional_message = '( '.$additional_message.' )';
      }
      $currentPrice    = $_product['price'];
      $competitorPrice = $data['competitor_price'];
      $couponCode   = '';
      $expired      = '';
      $customerName = $row['customer_name'];

      //send email
      $replyto = Mage::getStoreConfig(self::XML_PATH_EMAIL_RECIPIENT);
      $sender  = Mage::getStoreConfig(self::XML_PATH_EMAIL_SENDER);
      $recipient = $data['customer_email'];
      $postObject = new Varien_Object();

      //send email when approved
      if (Mage::getStoreConfig('pricematch/email/send_email_after_approve_request', Mage::app()->getStore()) == 1
          && $updated_status == 1
          && $before_status != 1)
      {

        //create couponcode
        if(is_numeric($competitorPrice) && $currentPrice > $competitorPrice)
        {
          $discountAmount = $row['discount_amount'];
          $productSku  = $_product->getSku();
          $couponArray = Mage::getModel('pricematch/pricematch')->createCouponCode($discountAmount,$productSku);
          $couponCode  = $couponArray['couponCode'];
          $expired     = $couponArray['expired'];
        }

        $template = Mage::getStoreConfig(self::XML_PATH_EMAIL_TEMPLATE_COUPON_CODE);
        $dataArray = array(
                    'competitor_price'  => $symbolBaseCurrency.round($competitorPrice, 2),
                    'product_price'     => $symbolBaseCurrency.round($currentPrice, 2),
                    'product_name'      => $_product->getName(),
                    'couponcode'        => $couponCode,
                    'customer_name'     => $customerName,
                    'expired'           => $expired,
                    'additional_message'=> $additional_message,
                    'product_url'       => Mage::getBaseUrl() . $_product->getUrlPath(),
                    'product_image'     => Mage::helper('catalog/image')->init($_product,'small_image')->resize(150)
                    );

        $postObject->setData($dataArray);
        $this->sendEmail($replyto, $template, $sender, $recipient, $postObject);
      }

      //send email when rejected
      if (Mage::getStoreConfig('pricematch/email/send_email_after_rejected_request', Mage::app()->getStore()) == 1
        && $updated_status == 2
        && $before_status != 2)
      {
        $template = Mage::getStoreConfig(self::XML_PATH_EMAIL_TEMPLATE_PRICEMATCH_REJECTED);
        $postObject->setData(array(
          'customer_name'     => $customerName,
          'additional_message'=> $additional_message
        ));
        $this->sendEmail($replyto, $template, $sender, $recipient, $postObject);
      }

      //save edit
      $pricematchModel->setData($data)
        ->setId($this->getRequest()->getParam('id'));
      $pricematchModel->couponCode = $couponCode;
      try
      {
        if ($pricematchModel->getCreatedTime == NULL || $pricematchModel->getUpdateTime() == NULL)
        {
          $pricematchModel->setCreatedTime(now())
            ->setUpdateTime(now());
        } else
        {
          $pricematchModel->setUpdateTime(now());
        }

        $pricematchModel->save();
        Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('pricematch')->__('Item was successfully saved'));
        Mage::getSingleton('adminhtml/session')->setFormData(FALSE);

        if ($this->getRequest()->getParam('back')) {
          $this->_redirect('*/*/edit', array('id' => $pricematchModel->getId()));
          return;
        }

        $this->_redirect('*/*/');
        return;
      } catch (Exception $e)
      {
        Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        Mage::getSingleton('adminhtml/session')->setFormData($data);
        $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
        return;
      }
    }

    Mage::getSingleton('adminhtml/session')->addError(Mage::helper('pricematch')->__('Unable to find item to save'));
    $this->_redirect('*/*/');
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

  public function deleteAction()
  {
    if ($this->getRequest()->getParam('id') > 0) {
      try {
        $model = Mage::getModel('pricematch/pricematch');
        $model->setId($this->getRequest()->getParam('id'))->delete();

        Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Item was successfully deleted'));
        $this->_redirect('*/*/');
        return;
      } catch (Exception $e) {
        Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
        return;
      }
    }
    $this->_redirect('*/*/');
  }

  public function massDeleteAction()
  {
    $pricematchIds = $this->getRequest()->getParam('pricematch');
    if (!is_array($pricematchIds)) {
      Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select item(s)'));
    } else {
      try {
        foreach ($pricematchIds as $pricematchId) {
          $pricematch = Mage::getModel('pricematch/pricematch')->load($pricematchId);
          $pricematch->delete();
        }
        Mage::getSingleton('adminhtml/session')->addSuccess(
          Mage::helper('adminhtml')->__(
            'Total of %d record(s) were successfully deleted', count($pricematchIds)
          )
        );
      } catch (Exception $e) {
        Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
      }
    }
    $this->_redirect('*/*/index');
  }

  public function massStatusAction()
  {
    $pricematchIds = $this->getRequest()->getParam('pricematch');
    if (!is_array($pricematchIds)) {
      Mage::getSingleton('adminhtml/session')->addError($this->__('Please select item(s)'));
    } else {
      try {
        foreach ($pricematchIds as $pricematchId) {
          $pricematch = Mage::getSingleton('pricematch/pricematch')->load($pricematchId);
          $before_status = $pricematch['status'];

          $pricematch->setStatus($this->getRequest()->getParam('status'))
                     ->setIsMassupdate(TRUE)
                     ->save();

          $symbolBaseCurrency = Mage::app()->getLocale()->currency(Mage::app()->getStore()->getBaseCurrencyCode())->getSymbol();
          $updated_status = $this->getRequest()->getParam('status');
          $data     = Mage::getSingleton('pricematch/pricematch')->load($pricematchId)->getData();
          $_product = Mage::getModel('catalog/product')->load($data['product_id']);
          $message  = Mage::getStoreConfig('pricematch/email/message_reply', Mage::app()->getStore());

          $currentPrice    = $_product['price'];
          $competitorPrice = $data['competitor_price'];
          $customerName    = $data['customer_name'];

          $couponCode = '';
          $expired = '';

          //send email
          $replyto = Mage::getStoreConfig(self::XML_PATH_EMAIL_RECIPIENT);
          $sender  = Mage::getStoreConfig(self::XML_PATH_EMAIL_SENDER);
          $recipient  = $data['customer_email'];
          $postObject = new Varien_Object();

          //send email when approved
          if (Mage::getStoreConfig('pricematch/email/send_email_after_approve_request', Mage::app()->getStore()) == 1 && $updated_status == 1
             && $before_status != 1) {

            //create couponcode
            if(is_numeric($competitorPrice) && $currentPrice > $competitorPrice){
              $discountAmount = $data['discount_amount'];
              $productSku = $_product->getSku();
              $couponArray = Mage::getModel('pricematch/pricematch')->createCouponCode($discountAmount,$productSku);
              $couponCode = $couponArray['couponCode'];
              $expired = $couponArray['expired'];
            }

            $template = Mage::getStoreConfig(self::XML_PATH_EMAIL_TEMPLATE_COUPON_CODE);
            $dataArray = array(
                    'competitor_price'  => $symbolBaseCurrency.round($competitorPrice, 2),
                    'product_price'     => $symbolBaseCurrency.round($currentPrice, 2),
                    'product_name'      => $_product->getName(),
                    'couponcode'        => $couponCode,
                    'customer_name'     => $customerName,
                    'expired'           => $expired,
                    'product_url'       => Mage::getBaseUrl() . $_product->getUrlPath(),
                    'product_image'     => Mage::helper('catalog/image')->init($_product,'small_image')->resize(150));
            $postObject->setData($dataArray);
            $this->sendEmail($replyto, $template, $sender, $recipient, $postObject);
          }

          //send email when rejected
          if (Mage::getStoreConfig('pricematch/email/send_email_after_rejected_request', Mage::app()->getStore()) == 1
            && $updated_status == 2
            && $before_status != 2) {
            $template = Mage::getStoreConfig(self::XML_PATH_EMAIL_TEMPLATE_PRICEMATCH_REJECTED);
            $postObject->setData(array(
              'customer_name' => $customerName
            ));
            $this->sendEmail($replyto, $template, $sender, $recipient, $postObject);
          }

          //save couponCode in grid
          $model = Mage::getSingleton('pricematch/pricematch');
          $model->setData($data)->setId($pricematchId);
          $model->couponCode = $couponCode;
          $model->save();
        }
        $this->_getSession()->addSuccess(
          $this->__('Total of %d request(s) were successfully updated', count($pricematchIds))
        );
      } catch (Exception $e) {
        $this->_getSession()->addError($e->getMessage());
      }
    }
    $this->_redirect('*/*/index');
  }

  public function exportCsvAction()
  {
    $fileName = 'pricematch.csv';
    $content = $this->getLayout()->createBlock('pricematch/adminhtml_pricematch_grid')
      ->getCsv();

    $this->_sendUploadResponse($fileName, $content);
  }

  public function exportXmlAction()
  {
    $fileName = 'pricematch.xml';
    $content = $this->getLayout()->createBlock('pricematch/adminhtml_pricematch_grid')
      ->getXml();

    $this->_sendUploadResponse($fileName, $content);
  }

  protected function _sendUploadResponse($fileName, $content, $contentType = 'application/octet-stream')
  {
    $response = $this->getResponse();
    $response->setHeader('HTTP/1.1 200 OK', '');
    $response->setHeader('Pragma', 'public', TRUE);
    $response->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', TRUE);
    $response->setHeader('Content-Disposition', 'attachment; filename=' . $fileName);
    $response->setHeader('Last-Modified', date('r'));
    $response->setHeader('Accept-Ranges', 'bytes');
    $response->setHeader('Content-Length', strlen($content));
    $response->setHeader('Content-type', $contentType);
    $response->setBody($content);
    $response->sendResponse();
    die;
  }
}
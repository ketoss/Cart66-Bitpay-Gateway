<?php

require 'bitpay/bp_lib.php';

class Cart66Bitpay {


  protected $_purchase_data;
  public $secret;
  public $gatewayUrl;
  public $fields = array();
  
  public function getCreditCardTypes() {
    // Bitpay does not use credit cards
    return array();
  }

  public function doSale() {
    // 2Checkout has a multi-step sale process and is implemented apart from this function
    return false;
  }

  public function getTransactionResponseDescription() {
    // 2Checkout handles errors in a way that is implemented without this function.
    return '';
  }

  
  public function setPayment($p) {
    $this->_payment['phone'] = isset($p['phone']) ? $p['phone'] : '';
    $this->_payment['email'] = isset($p['email']) ? $p['email'] : '';
    
    // For subscription accounts
    if(isset($p['password'])) {
      if($p['password'] != $p['password2']) {
        $this->_errors['Password'] = __('Passwords do not match', 'cart66');
        $this->_jqErrors[] = 'payment-password';
      }
    }
  }
  
  public function initCheckout($total, $override=false) {
    if((isset($_POST['cart66-task']) && $_POST['cart66-task'] == 'Bitpay') || ($override)) {
      $pendingOrderId = $this->storePendingOrder();
      $order = new Cart66Order($pendingOrderId);
      Cart66Session::set('Cart66PendingOUID', $order->ouid);

      $invoice = bpCreateInvoice($order->ouid, $order->total, $order->ouid);

      if (!is_array($invoice))
      {
        echo 'error';
      }
      else
      {
        header('Location: '.$invoice['url']);
      }

      exit;
    }
  }

  public function getTaxRate() {
    return $this->_taxRate->rate;
  }

  public function getCardNumberTail($length=4) {
    $tail = false;
    if(isset($this->_payment['cardNumber']) && strlen($this->_payment['cardNumber']) >= $length) {
      $tail = substr($this->_payment['cardNumber'], -1 * $length);
    }
    return $tail;
  }
  
  public function validateCartForCheckout() {
    $isValid = true;
    $itemCount = Cart66Session::get('Cart66Cart')->countItems();
    if($itemCount < 1) {
      $this->_errors['Invalid Cart'] = __('There must be at least one item in the cart.','cart66');
      $isValid = false;
    }
    return $isValid;
  }

  public function setBilling($b) {
    if(is_array($b)) {
      if(!(isset($b['state']) && !empty($b['state']))) {
        if(isset($b['state_text'])) {
          $b['state'] = trim($b['state_text']);
        }
      }
      unset($b['state_text']);

      $this->_billing = $b;
      $skip = array('address2', 'billing-state_text');
      $custom_billing_fields = apply_filters('cart66_after_billing_form', '');
      if(is_array($custom_billing_fields)) {
        foreach($custom_billing_fields as $key => $billing_field) {
          if(!$billing_field['required']) {
            $skip[] = $billing_field['slug'];
          }
          if(isset($billing_field['validator']) && $billing_field['validator'] != '') {
            if(function_exists($billing_field['validator'])) {
              $skip[] = $billing_field['slug'];
              $data_to_validate = isset($b[$billing_field['slug']]) ? $b[$billing_field['slug']] : '';
              $validated = call_user_func($billing_field['validator'], $data_to_validate);
              if(!$validated['valid']) {
                foreach($validated['errors'] as $key => $error) {
                  $this->_errors['Billing ' . $billing_field['slug'] . $key] = $error;
                  $this->_jqErrors[] = 'billing-' . $billing_field['slug'];
                }
              }
            }
          }
        }
      }
      foreach($b as $key => $value) {
        if(!in_array($key, $skip)) {
          $value = trim($value);
          if($value == '') {
            $keyName = ucwords(preg_replace('/([A-Z])/', " $1", $key));
            $this->_errors['Billing ' . $keyName] = __('Billing ','cart66') . $keyName . __(' required','cart66');
            $this->_jqErrors[] = "billing-$key";
          }
        }
      }
    }
  } 

  public function setShipping($s, $billing_fields=false) {
    if(is_array($s)) {
      if(!(isset($s['state']) && !empty($s['state']))) {
        $s['state'] = trim($s['state_text']);
      }
      unset($s['state_text']);

      $this->_shipping = $s;
      $skip = array('address2', 'shipping-state_text');
      $custom_shipping_fields = apply_filters('cart66_after_shipping_form', '');
      if(is_array($custom_shipping_fields)) {
        foreach($custom_shipping_fields as $key => $shipping_field) {
          if(!$shipping_field['required']) {
            $skip[] = $shipping_field['slug'];
          }
          if(isset($shipping_field['validator']) && $shipping_field['validator'] != '') {
            if(function_exists($shipping_field['validator'])) {
              $skip[] = $shipping_field['slug'];
              $data_to_validate = isset($s[$shipping_field['slug']]) ? $s[$shipping_field['slug']] : '';
              $validated = call_user_func($shipping_field['validator'], $data_to_validate);
              if(!$validated['valid']) {
                foreach($validated['errors'] as $key => $error) {
                  $this->_errors['Shipping ' . $shipping_field['slug'] . $key] = $error;
                  $this->_jqErrors[] = 'shipping-' . $shipping_field['slug'];
                }
              }
            }
          }
        }
      }
      if($billing_fields) {
        $custom_billing_fields = apply_filters('cart66_after_billing_form', '');
        if(is_array($custom_billing_fields)) {
          foreach($custom_billing_fields as $key => $billing_field) {
            if(!$billing_field['required']) {
              $skip[] = $billing_field['slug'];
            }
          }
        }
      }
      foreach($s as $key => $value) {
        if(!in_array($key, $skip)) {
          $value = trim($value);
          if($value == '') {
            $keyName = preg_replace('/([A-Z])/', " $1", $key);
            $this->_errors['Shipping ' . $keyName] = __('Shipping ','cart66') . $keyName . __(' required','cart66');
            $this->_jqErrors[] = "shipping-$key";
          }
        }
      }
    }
  }

  public function getErrors() {
    if(!is_array($this->_errors)) {
      $this->_errors = array();
    }
    return $this->_errors;
  }

  public function getJqErrors() {
    if(!is_array($this->_jqErrors)) {
      $this->_jqErrors = array();
    }
    return $this->_jqErrors;
  }

  public function getTaxLocation() {
    $ship = $this->getShipping();
    $taxLocation = array (
      'state' => $ship['state'],
      'zip' => $ship['zip']
      );
    return $taxLocation;
  }
  public function getTaxAmount() {
    $tax = 0;
    if($this->isTaxed()) {
      $taxable = Cart66Session::get('Cart66Cart')->getTaxableAmount();
      if($this->taxShipping()) {
        $taxable += Cart66Session::get('Cart66Cart')->getShippingCost();
      }
      $tax = number_format($taxable * ($this->_taxRate->rate/100), 2, '.', '');
    }
    return $tax;
  }

  public function isTaxed($isShippingTaxed=null) {
   $s = $this->getShipping();
   if(count($s)) {
     $taxRate = new Cart66TaxRate();
     $isTaxed = $taxRate->loadByZip($s['zip']);
     if($isTaxed == false) {
       $isTaxed = $taxRate->loadByState($s['state']);
     }

     $this->_taxRate = $taxRate;
     $taxShipping = $taxRate->tax_shipping;
     
     return ($isShippingTaxed==null) ? $isTaxed : $taxShipping;
   }
   else {
     throw new Exception(__('Unable to determine tax rate because shipping data is unavailable','cart66'));
   }
 }

 public function __construct() {
  $this->gatewayUrl = 'https://bitpay.com/api/invoice/';
}

public function addField($field, $value) {
  $this->fields[$field] = $value;
}

public function removeField($field) {
  unset($this->fields[$field]);
}

public function setSecret($word) {
  if(!empty($word)) {
    $this->secret = $word;
  }
}

public function getShipping() {
  return count($this->_shipping) ? $this->_shipping : $this->_billing;
}

public function getBilling() {
  return $this->_billing;
}

public function getPayment() {
  return $this->_payment;
}

public function purchase_data($data) {
  $this->_purchase_data = $data;
}

public function get_redirect_url() {
    // Specify your 2CheckOut vendor id
    //$this->addField('sid', Cart66Setting::getValue('tco_account_number'));

    // Specify the order information
  $items = Cart66Session::get('Cart66Cart')->getItems();
  $number = 0;
  $item_amount = array();
  foreach($items as $i) {
    $product = new Cart66Product($i->getProductId());
    $this->addField('li_' . $number . '_type', 'product');
    $this->addField('li_' . $number . '_name', $product->name);
    $this->addField('li_' . $number . '_price', number_format($i->getProductPrice(), 2, '.', ''));
    $this->addField('li_' . $number . '_product_id', $i->getItemNumber());
    $this->addField('li_' . $number . '_quantity', $i->getQuantity());
    $this->addField('li_' . $number . '_tangible', 'N');
    $item_amount[] = number_format($i->getProductPrice(), 2, '.', '');
    $number++;
  }

  $item_amount = array_sum($item_amount);
  $total_amount = number_format(Cart66Session::get('Cart66Cart')->getGrandTotal() + Cart66Session::get('Cart66Tax'), 2, '.', '');

    // Discounts
  $promotion = Cart66Session::get('Cart66Promotion');
  if($promotion) {
    $this->addField('li_' . $number . '_type', 'coupon');
    $this->addField('li_' . $number . '_name', $promotion->name);
    $this->addField('li_' . $number . '_price', Cart66Session::get('Cart66Cart')->getDiscountAmount());
    $this->addField('li_' . $number . '_product_id', __('Discount', 'cart66') . '(' . Cart66Session::get('Cart66PromotionCode') . ')');
    $this->addField('li_' . $number . '_quantity', 1);
    $this->addField('li_' . $number . '_tangible', 'N');
    $number++;
  }

    // Shipping
  $shipping = Cart66Session::get('Cart66Cart')->getShippingCost();
  if(CART66_PRO && Cart66Setting::getValue('use_live_rates')) {
    $selectedRate = Cart66Session::get('Cart66LiveRates')->getSelected();
    $shippingMethod = $selectedRate->service;
  }
  else {
    $method = new Cart66ShippingMethod(Cart66Session::get('Cart66Cart')->getShippingMethodId());
    $shippingMethod = $method->name;
  }
  $cart = Cart66Session::get('Cart66Cart');
  if($cart->requireShipping() || $cart->hasTaxableProducts()) {
    $this->addField('li_' . $number . '_type', 'product');
    $this->addField('li_' . $number . '_product_id', __('Shipping', 'cart66'));
    $this->addField('li_' . $number . '_name', $shippingMethod);
    $this->addField('li_' . $number . '_price', $shipping);
    $this->addField('li_' . $number . '_quantity', 1);
    $this->addField('li_' . $number . '_tangible', 'N');
    $number++;
      // Shipping Fields
    if(strlen($this->_shipping['address']) > 3) {
      $this->addField('ship_name', $this->_shipping['firstName'] . ' ' . $this->_shipping['lastName']);
      $this->addField('ship_street_address', $this->_shipping['address']);
      $this->addField('ship_street_address2', $this->_shipping['address2']);
      $this->addField('ship_city', $this->_shipping['city']);
      $this->addField('ship_state', $this->_shipping['state']);
      $this->addField('ship_zip', $this->_shipping['zip']);
      $this->addField('ship_country', $this->_shipping['country']);
      $this->addField('phone', $this->_payment['phone']);
    }
  }

    // Tax
  $tax = Cart66Session::get('Cart66Tax');
  if($tax > 0) {
    $this->addField('li_' . $number . '_type', 'tax');
    $this->addField('li_' . $number . '_product_id', __('Tax', 'cart66'));
    $this->addField('li_' . $number . '_name', Cart66Session::get('Cart66TaxRate'));
    $this->addField('li_' . $number . '_price', $tax);
    $this->addField('li_' . $number . '_quantity', 1);
    $this->addField('li_' . $number . '_tangible', 'N');
    $number++;
  }

    // Default Fields
  $this->addField('mode', '2CO');
  $this->addField('return_url', Cart66Setting::getValue('shopping_url') );
  $this->addField('pay_method', 'CC');
  $this->addField('x_receipt_link_url', add_query_arg('listener', '2CO', Cart66Common::getPageLink('store/receipt')));
  $this->addField('tco_currency', 'USD');

  $redirect_url = $this->gatewayUrl . '?' . http_build_query($this->fields, '', '&');
  Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] $redirect_url");

  return $redirect_url;

}

public function storePendingOrder() {
  $orderInfo = array();
  $orderInfo['bill_address'] = '';
  $orderInfo['coupon'] = Cart66Common::getPromoMessage();
  $orderInfo['shipping'] = Cart66Session::get('Cart66Cart')->getShippingCost();
  $orderInfo['trans_id'] = '';
  $orderInfo['status'] = 'checkout_pending';
  $orderInfo['ordered_on'] = date('Y-m-d H:i:s', Cart66Common::localTs());
  $orderInfo['shipping_method'] = Cart66Session::get('Cart66Cart')->getShippingMethodName();
  $orderInfo['account_id'] = 0;
  $orderInfo['total'] = Cart66Session::get('Cart66Cart')->getGrandTotal() + Cart66Session::get('Cart66Tax');
  $orderInfo['tax'] = Cart66Session::get('Cart66Tax');
  $orderInfo['ship_first_name'] = $this->_shipping['firstName'];
  $orderInfo['ship_last_name'] = $this->_shipping['lastName'];
  $orderInfo['ship_address'] = $this->_shipping['address'];
  $orderInfo['ship_address2'] = $this->_shipping['address2'];
  $orderInfo['ship_city'] = $this->_shipping['city'];
  $orderInfo['ship_state'] = $this->_shipping['state'];
  $orderInfo['ship_zip'] = $this->_shipping['zip'];
  $orderInfo['ship_country'] = $this->_shipping['country'];
  $orderId = Cart66Session::get('Cart66Cart')->storeOrder($orderInfo);
  return $orderId;
}

public function saveOrder() {
  global $wpdb;
    // NEW Parse custom value
  $referrer = false;
  $ouid = $_POST['custom'];
  if(strpos($ouid, '|') !== false) {
    list($ouid, $referrer) = explode('|', $ouid);
  }
  $order = new Cart66Order();
  $order->loadByOuid($ouid);

  if($order->id > 0 && $order->status == 'checkout_pending' && $_POST['total'] == $order->total) {
    $statusOptions = Cart66Common::getOrderStatusOptions();
    $status = $statusOptions[0];

    $data = array(
      'bill_first_name' => $_POST['first_name'],
      'bill_last_name' => $_POST['last_name'],
      'bill_address' => $_POST['street_address'],
      'bill_address2' => $_POST['street_address2'],
      'bill_city' => $_POST['city'],
      'bill_state' => $_POST['state'],
      'bill_zip' => $_POST['zip'],
      'bill_country' => $_POST['country'],
      'email' => $_POST['email'],
        //'tax' => $pp['tax'],
      'trans_id' => $_POST['order_number'],
      'ordered_on' => date('Y-m-d H:i:s', Cart66Common::localTs()),
      'status' => $status
      );


    // Verify the first items in the IPN are for products managed by Cart66. It could be an IPN from some other type of transaction.

    //Bitpay IPN
    $response = bpVerifyNotification( $bp->settings['apiKey'] );
    if (isset($response['error']))
      bplog($response);
    else
    {
      $orderId = $response['posData'];

      switch($response['status'])
      {
        case 'paid':
         $order->setData($data);
         $order->save();
         $orderId = $order->id;
        break;
        case 'confirmed':
        case 'complete':

       }

       break;
     }
   }

      // Handle email receipts
  if(CART66_PRO && CART66_EMAILS && Cart66Setting::getValue('enable_advanced_notifications') ==1) {
    $notify = new Cart66AdvancedNotifications($orderId);
    $notify->sendAdvancedEmailReceipts();
  }
  elseif(CART66_EMAILS) {
    $notify = new Cart66Notifications($orderId);
    $notify->sendEmailReceipts();
  }

  wp_redirect(remove_query_arg('listener', Cart66Common::getCurrentPageUrl()));
  exit;
}
}

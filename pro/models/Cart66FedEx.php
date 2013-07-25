<?php 
class Cart66FedEx {
  protected $developerKey;  
  protected $password;  
  protected $accountNumber;
  protected $meterNumber;
  protected $dimensionsUnits = "IN";
  protected $weightUnits = "LB";
  protected $fromZip;
  protected $dropOffType;
  protected $credentials;

  public function __construct() {
    $setting = new Cart66Setting();
    $this->developerKey = Cart66Setting::getValue('fedex_developer_key');
    $this->password = Cart66Setting::getValue('fedex_password');
    $this->accountNumber = Cart66Setting::getValue('fedex_account_number');
    $this->meterNumber = Cart66Setting::getValue('fedex_meter_number');
    $this->fromZip = Cart66Setting::getValue('fedex_ship_from_zip');
    $this->dropOffType = Cart66Setting::getValue('fedex_pickup_code');
    $this->credentials = 1;
  }
  
  public function setDimensionsUnits($unit){
    $this->dimensionsUnits = $unit;
  }
  
  public function setWeightUnits($unit){
    $this->weightUnits = $unit;
  }
  
  public function getRate($PostalCode, $dest_zip, $dest_country_code, $service, $weight, $length=0, $width=0, $height=0) {
    $setting= new Cart66Setting();
    $countryCode = array_shift(explode('~', Cart66Setting::getValue('home_country')));
    $pickupCode = (Cart66Setting::getValue('fedex_pickup_code')) ? Cart66Setting::getValue('fedex_pickup_code') : "REGULAR_PICKUP";
    $residential = (Cart66Setting::getValue('fedex_only_ship_commercial')) ? "0" : "1";
    $locationType = (Cart66Setting::getValue('fedex_location_type') == 'commercial') ? "0" : "1";
    
    if ($this->credentials != 1) {
      print 'Please set your credentials with the setCredentials function';
      die();
    }
    
    // Rate Request
    $data = '<?xml version="1.0" encoding="UTF-8" ?>';
    $data .='<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:v10="http://fedex.com/ws/rate/v10">';
    $data .='  <soapenv:Header/>';
    $data .='  <soapenv:Body>';
    $data .='    <v10:RateRequest>';
    $data .='      <v10:WebAuthenticationDetail>';
    $data .='        <v10:UserCredential>';
    $data .='          <v10:Key>' . $this->developerKey . '</v10:Key>';
    $data .='          <v10:Password>' . $this->password . '</v10:Password>';
    $data .='        </v10:UserCredential>';
    $data .='      </v10:WebAuthenticationDetail>';
    $data .='      <v10:ClientDetail>';
    $data .='        <v10:AccountNumber>' . $this->accountNumber . '</v10:AccountNumber>';
    $data .='        <v10:MeterNumber>' . $this->meterNumber . '</v10:MeterNumber>';
    $data .='      </v10:ClientDetail>';
    $data .='      <v10:TransactionDetail>';
    $data .='        <v10:CustomerTransactionId></v10:CustomerTransactionId>';
    $data .='      </v10:TransactionDetail>';
    $data .='      <v10:Version>';
    $data .='        <v10:ServiceId>crs</v10:ServiceId>';
    $data .='        <v10:Major>10</v10:Major>';
    $data .='        <v10:Intermediate>0</v10:Intermediate>';
    $data .='        <v10:Minor>0</v10:Minor>';
    $data .='      </v10:Version>';
    $data .='      <v10:ReturnTransitAndCommit>1</v10:ReturnTransitAndCommit>';
    $data .='      <v10:CarrierCodes>FDXE</v10:CarrierCodes>';
    $data .='      <v10:CarrierCodes>FDXG</v10:CarrierCodes>';
    $data .='      <v10:RequestedShipment>';
    $data .='        <v10:ShipTimestamp>' . date("Y-m-d\TH:i:sP") . '</v10:ShipTimestamp>';
    $data .='        <v10:DropoffType>' . $pickupCode . '</v10:DropoffType>';
    //$data .= '       <v10:ServiceType>' . $service . '</v10:ServiceType>';
    $data .='        <v10:PackagingType>YOUR_PACKAGING</v10:PackagingType>';
    $data .='        <v10:Shipper>';
    $data .='          <v10:AccountNumber>' . $this->accountNumber . '</v10:AccountNumber>';
    //$data .='          <v10:Tins>';
    //$data .='            <v10:TinType></v10:TinType>';
    //$data .='            <v10:Number></v10:Number>';
    //$data .='            <v10:Usage></v10:Usage>';
    //$data .='          </v10:Tins>';
    //$data .='          <v10:Contact>';
    //$data .='            <v10:ContactId></v10:ContactId>';
    //$data .='            <v10:PersonName></v10:PersonName>';
    //$data .='            <v10:CompanyName></v10:CompanyName>';
    //$data .='            <v10:PhoneNumber></v10:PhoneNumber>';
    //$data .='            <v10:PhoneExtension></v10:PhoneExtension>';
    //$data .='            <v10:EMailAddress></v10:EMailAddress>';
    //$data .='          </v10:Contact>';
    $data .='          <v10:Address>';
    //$data .='            <v10:StreetLines></v10:StreetLines>';
    //$data .='            <v10:StreetLines></v10:StreetLines>';
    //$data .='            <v10:City></v10:City>';
    //$data .='            <v10:StateOrProvinceCode></v10:StateOrProvinceCode>';
    $data .='            <v10:PostalCode>' . $this->fromZip . '</v10:PostalCode>';
    //$data .='            <v10:UrbanizationCode></v10:UrbanizationCode>';
    $data .='            <v10:CountryCode>' . $countryCode . '</v10:CountryCode>';
    $data .='            <v10:Residential>' . $locationType . '</v10:Residential>';
    $data .='          </v10:Address>';
    $data .='        </v10:Shipper>';
    $data .='        <v10:Recipient>';
    //$data .='          <v10:Contact>';
    //$data .='            <v10:PersonName></v10:PersonName>';
    //$data .='            <v10:CompanyName></v10:CompanyName>';
    //$data .='            <v10:PhoneNumber></v10:PhoneNumber>';
    //$data .='            <v10:PhoneExtension></v10:PhoneExtension>';
    //$data .='            <v10:EMailAddress></v10:EMailAddress>';
    //$data .='          </v10:Contact>';
    $data .='          <v10:Address>';
    //$data .='            <v10:StreetLines></v10:StreetLines>';
    //$data .='            <v10:StreetLines></v10:StreetLines>';
    //$data .='            <v10:City></v10:City>';
    //$data .='            <v10:StateOrProvinceCode></v10:StateOrProvinceCode>';
    $data .='            <v10:PostalCode>' . $dest_zip . '</v10:PostalCode>';
    //$data .='            <v10:UrbanizationCode></v10:UrbanizationCode>';
    $data .='            <v10:CountryCode>' . $dest_country_code . '</v10:CountryCode>';
    $data .='            <v10:Residential>' . $residential . '</v10:Residential>';
    $data .='          </v10:Address>';
    $data .='        </v10:Recipient>';
    //$data .='        <v10:RecipientLocationNumber></v10:RecipientLocationNumber>';
    $data .='        <v10:Origin>';
    //$data .='          <v10:Contact>';
    //$data .='            <v10:ContactId></v10:ContactId>';
    //$data .='            <v10:PersonName></v10:PersonName>';
    //$data .='            <v10:CompanyName></v10:CompanyName>';
    //$data .='            <v10:PhoneNumber></v10:PhoneNumber>';
    //$data .='            <v10:PhoneExtension></v10:PhoneExtension>';
    //$data .='            <v10:EMailAddress></v10:EMailAddress>';
    //$data .='          </v10:Contact>';
    $data .='          <v10:Address>';
    //$data .='            <v10:StreetLines></v10:StreetLines>';
    //$data .='            <v10:StreetLines></v10:StreetLines>';
    //$data .='            <v10:City></v10:City>';
    //$data .='            <v10:StateOrProvinceCode></v10:StateOrProvinceCode>';
    $data .='            <v10:PostalCode>' . $this->fromZip . '</v10:PostalCode>';
    //$data .='            <v10:UrbanizationCode></v10:UrbanizationCode>';
    $data .='            <v10:CountryCode>' . $countryCode . '</v10:CountryCode>';
    $data .='            <v10:Residential>' . $locationType . '</v10:Residential>';
    $data .='          </v10:Address>';
    $data .='        </v10:Origin>';
    $data .='        <v10:ShippingChargesPayment>';
    $data .='          <v10:PaymentType>SENDER</v10:PaymentType>';
    $data .='          <v10:Payor>';
    $data .='            <v10:AccountNumber>' . $this->accountNumber . '</v10:AccountNumber>';
    $data .='            <v10:CountryCode>' . $countryCode . '</v10:CountryCode>';
    $data .='          </v10:Payor>';
    $data .='        </v10:ShippingChargesPayment>';
    $data .='        <v10:RateRequestTypes>ACCOUNT</v10:RateRequestTypes>';
    $data .='        <v10:PackageCount>' . $this->getPackageCount() . '</v10:PackageCount>';
    $data .=         $this->getRequestedPackageLineItems($weight);
    $data .='      </v10:RequestedShipment>';
    $data .='    </v10:RateRequest>';
    $data .='  </soapenv:Body>';
    $data .='</soapenv:Envelope>';
    
    $ch = curl_init("https://gateway.fedex.com:443/web-services");
    curl_setopt ($ch, CURLOPT_POST, 1);
    curl_setopt($ch,CURLOPT_TIMEOUT, 60);  
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);  
    curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);  
    curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);  
    curl_setopt($ch,CURLOPT_POSTFIELDS,$data);
    $result = curl_exec($ch);
    curl_close($ch);
    
    Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] FEDEX XML REQUEST: \n$data");
    Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] FEDEX XML RESULT: \n$result");
    
    try{
      $xml = new SimpleXmlElement($result);
    }
    catch(Exception $e){
      Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Cart66 Exception caught when trying to get FedEx XML Response: " . $e->getMessage() . " \n");
      $rate = false;
    }
    
    if(isset($xml->children("soapenv", true)->Body->children("v10", true)->RateReply->HighestSeverity)) {
      $response = $xml->children("soapenv", true)->Body->children("v10", true)->RateReply->HighestSeverity;
      $rateReplyDetails = $xml->children("soapenv", true)->Body->children("v10", true)->RateReply->RateReplyDetails;
      
      Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Response Description: (Service: $service) $service");
      if($response == "FAILURE" || $response == "ERROR") {
        $error = $xml->children("soapenv", true)->Body->children("v10", true)->RateReply->Notifications->Message;
        Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Response Description: (Service: $service) $response $error");
        $rate = false;
      }
      else {
        $rate = array();
        foreach($rateReplyDetails as $r) {
          $service = $r->ServiceType;
          $amount = $r->RatedShipmentDetails->ShipmentRateDetail->TotalNetCharge->Amount;
          $rate[] = array('name' => $service, 'rate' => $amount);
        }
      }
    } else {
      $rate = false;
    }
    return $rate;
  }
  
  /**
   * Return an array where the keys are the service names and the values are the prices
   */
  public function getAllRates($toZip, $toCountryCode, $weight) {
    
    $rates = array();
    $method = new Cart66ShippingMethod();
    if($toCountryCode == 'US' || $toCountryCode == 'CA') {
      $fedexServices = $method->getServicesForCarrier('fedex');
      $rate = $this->getRate($this->fromZip, $toZip, $toCountryCode, null, $weight);
      if($rate !== false) {
        foreach($fedexServices as $service => $code) {
          foreach($rate as $r) {
            if($r["name"] == $code) {
              $rates[$service] = number_format((float) $r["rate"], 2, '.', '');
            }
          }
          Cart66Common::log("LIVE RATE REMOTE RESULT ==> ZIP: $toZip Service: $service $code) Rate: " . print_r($rates, true));
        }
      }
      $fedexServices = $method->getServicesForCarrier('fedex_intl');
      $rate = $this->getRate($this->fromZip, $toZip, $toCountryCode, null, $weight);
      if($rate !== false) {
        foreach($fedexServices as $service => $code) {
          foreach($rate as $r) {
            if($r["name"] == $code) {
              $rates[$service] = number_format((float) $r["rate"], 2, '.', '');
            }
          }
          Cart66Common::log("LIVE RATE REMOTE RESULT ==> ZIP: $toZip Service: $service $code) Rate: " . print_r($rates, true));
        }
      }
    } else {
      $fedexServices = $method->getServicesForCarrier('fedex_intl');
      $rate = $this->getRate($this->fromZip, $toZip, $toCountryCode, null, $weight);
      if($rate !== false) {
        foreach($fedexServices as $service => $code) {
          foreach($rate as $r) {
            if($r["name"] == $code) {
              $rates[$service] = number_format((float) $r["rate"], 2, '.', '');
            }
          }
          Cart66Common::log("LIVE RATE REMOTE RESULT ==> ZIP: $toZip Service: $service $code) Rate: " . print_r($rates, true));
        }
      }
    }
    return $rates;
  } 
  
  public function getPackageCount() {
    $items = Cart66Session::get('Cart66Cart')->getItems();
    $count = 0;
    if(Cart66Setting::getValue('fedex_ship_individually')) {
      foreach($items as $item) {
        for($i=1; $i <= $item->getQuantity(); $i++){
          $count++;
        }
      }
    }
    else {
      $count = 1;
    }
    return $count;
  }

  public function getRequestedPackageLineItems($weight) {
    $items = Cart66Session::get('Cart66Cart')->getItems();
    $length = 0;
    $width = 0;
    $height = 0;
    $data = '';
    if(Cart66Setting::getValue('fedex_ship_individually')) {
      foreach($items as $item) {
        for($i=1; $i <= $item->getQuantity(); $i++){
          $data .='        <v10:RequestedPackageLineItems>';
          $data .='          <v10:SequenceNumber>1</v10:SequenceNumber>';
          $data .='          <v10:GroupNumber>1</v10:GroupNumber>';
          $data .='          <v10:GroupPackageCount>1</v10:GroupPackageCount>';
          $data .='          <v10:Weight>';
          $data .='            <v10:Units>' . $this->weightUnits . '</v10:Units>';
          $data .='            <v10:Value>' . $item->getWeight() . '</v10:Value>';
          $data .='          </v10:Weight>';
          $data .='          <v10:Dimensions>';
          $data .='            <v10:Length>' . $length . '</v10:Length>';
          $data .='            <v10:Width>' . $width . '</v10:Width>';
          $data .='            <v10:Height>' . $height . '</v10:Height>';
          $data .='            <v10:Units>' . $this->dimensionsUnits . '</v10:Units>';
          $data .='          </v10:Dimensions>';
          //$data .='          <v10:PhysicalPackaging></v10:PhysicalPackaging>';
          //$data .='          <v10:ContentRecords>';
          //$data .='            <v10:PartNumber></v10:PartNumber>';
          //$data .='            <v10:ItemNumber></v10:ItemNumber>';
          //$data .='            <v10:ReceivedQuantity></v10:ReceivedQuantity>';
          //$data .='            <v10:Description></v10:Description>';
          //$data .='          </v10:ContentRecords>';
          $data .='        </v10:RequestedPackageLineItems>';
        }
      }
    }
    else {
      $data .='        <v10:RequestedPackageLineItems>';
      $data .='          <v10:SequenceNumber>1</v10:SequenceNumber>';
      $data .='          <v10:GroupNumber>1</v10:GroupNumber>';
      $data .='          <v10:GroupPackageCount>1</v10:GroupPackageCount>';
      $data .='          <v10:Weight>';
      $data .='            <v10:Units>' . $this->weightUnits . '</v10:Units>';
      $data .='            <v10:Value>' . $weight . '</v10:Value>';
      $data .='          </v10:Weight>';
      $data .='          <v10:Dimensions>';
      $data .='            <v10:Length>' . $length . '</v10:Length>';
      $data .='            <v10:Width>' . $width . '</v10:Width>';
      $data .='            <v10:Height>' . $height . '</v10:Height>';
      $data .='            <v10:Units>' . $this->dimensionsUnits . '</v10:Units>';
      $data .='          </v10:Dimensions>';
      //$data .='          <v10:PhysicalPackaging></v10:PhysicalPackaging>';
      //$data .='          <v10:ContentRecords>';
      //$data .='            <v10:PartNumber></v10:PartNumber>';
      //$data .='            <v10:ItemNumber></v10:ItemNumber>';
      //$data .='            <v10:ReceivedQuantity></v10:ReceivedQuantity>';
      //$data .='            <v10:Description></v10:Description>';
      //$data .='          </v10:ContentRecords>';
      $data .='        </v10:RequestedPackageLineItems>';
    }
    return $data;
  }
  
}
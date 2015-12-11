<?php
class API_bison_ups_shipping extends API
{
  public function calculateShipping()
  {
    if ($this->config['ups_access_key']=='')
    {
        $this->log->error("UPS Access Key is not set");
        return false;
    }
    
    $bison_config = $this->addon->api('bison')->getBisonConfig();
    $cart_items = $this->addon->api('bison')->getCartItems();
	$total_weight = 0;
	foreach ($cart_items as $item) 
    {
		$total_weight += $item[$bison_config['weight_field']] * $item['quantity'];
	}
    
    if (in_array($this->config['live_mode'], array('false', 'no', '0', '-1')))
    {
        $endpoint = 'https://wwwcie.ups.com/ups.app/xml/Rate';
    }
    else
    {
        $endpoint = 'https://onlinetools.ups.com/ups.app/xml/Rate';
    }
    
    $shipping_options = $bison_config['shipping_options'];
    $customer = $this->addon->api('bison')->getCustomerInfo();
    if (!isset($customer['shipping_option']) || $customer['shipping_option']=='')
    {
        $customer['shipping_option'] = $this->config['default_service_code'];
    }
    
    $xml = "<?xml version=\"1.0\"?>
<AccessRequest xml:lang=\"en-US\">
 	<AccessLicenseNumber>".$this->config['ups_access_key']."</AccessLicenseNumber>
	<UserId>".$this->config['ups_login']."</UserId>
	<Password>".$this->config['ups_password']."</Password>
</AccessRequest>
<?xml version=\"1.0\"?>
<RatingServiceSelectionRequest xml:lang=\"en-US\">
  <Request>
    <TransactionReference>
      <CustomerContext>Rating and Service</CustomerContext>
      <XpciVersion>1.0</XpciVersion>
    </TransactionReference>
	<RequestAction>Rate</RequestAction>
	<RequestOption>Rate</RequestOption>
  </Request>
    <PickupType>
  	<Code>07</Code>
  	<Description>Rate</Description>
    </PickupType>
  <Shipment>
    	<Description>Rate Description</Description>
    <Shipper>
      <Address>
        <AddressLine1>".$this->config['sender_address']."</AddressLine1>
		<City>".$this->config['sender_city']."</City>
        <StateProvinceCode>".$this->config['sender_state_code']."</StateProvinceCode>
        <PostalCode>".$this->config['sender_zip']."</PostalCode> 
        <CountryCode>".$this->config['sender_country_code']."</CountryCode>
      </Address>
    </Shipper>
    <ShipTo>
      <Address>
        <AddressLine1>".$customer['shipping_address_1']."</AddressLine1>
        <AddressLine2>".$customer['shipping_address_2']."</AddressLine2>
        <City>".$customer['shipping_city']."</City>
        <PostalCode>".$customer['shipping_zip']."</PostalCode> 
        <CountryCode>".$customer['shipping_country']."</CountryCode>
      </Address>
    </ShipTo>
    <ShipFrom>
      <Address>
        <AddressLine1>".$this->config['sender_address']."</AddressLine1>
		<City>".$this->config['sender_city']."</City>
        <StateProvinceCode>".$this->config['sender_state_code']."</StateProvinceCode>
        <PostalCode>".$this->config['sender_zip']."</PostalCode> 
        <CountryCode>".$this->config['sender_country_code']."</CountryCode>
      </Address>
    </ShipFrom>
    <Service>
        <Code>".$customer['shipping_option']."</Code>
    </Service> 
  	<Package>
      		<PackagingType>
	        	<Code>02</Code>
        		<Description>Customer Supplied</Description>
      		</PackagingType>
      		<Description>Rate</Description>
      		<PackageWeight>
      			<UnitOfMeasurement>
      			  <Code>LBS</Code>
      			</UnitOfMeasurement>
	        	<Weight>".$total_weight."</Weight>
      		</PackageWeight>   
   	</Package>
  </Shipment>
</RatingServiceSelectionRequest>";

    $ch = curl_init($endpoint);  
	curl_setopt($ch, CURLOPT_HEADER, 0);  
	curl_setopt($ch,CURLOPT_POST,1);  
	curl_setopt($ch,CURLOPT_TIMEOUT, 60);  
	curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);  
	curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);  
	curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);  
	curl_setopt($ch,CURLOPT_POSTFIELDS,$xml);  
	$result=curl_exec ($ch);  

	// Find out if the UPS service is down
    /*
	preg_match_all('/HTTP\/1\.\d\s(\d+)/',$result,$matches);
	foreach($matches[1] as $key=>$value) {
	    if ($value != 100 && $value != 200) {
		throw new Exception("The UPS service seems to be down with HTTP/1.1 $value");
	    }
	}*/
    
    $xml = simplexml_load_string($result);
    
	return (float)$xml->RatedShipment->TotalCharges->MonetaryValue*100;
    
  }
}
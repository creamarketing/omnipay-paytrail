<?php
namespace Omnipay\Paytrail\Message;

use Omnipay\Common\Message\AbstractRequest;

abstract class Request extends AbstractRequest {
	
	public function getData() {
		$data = array();
		foreach ($this->parameters->all() as $key => $value) {
			$data[$key] = $value;
		}
		return $data;
	}
	
	public function setMerchantId($value) {
		return $this->setParameter('merchant_id', $value);
	}
	
	public function setMerchantSecret($value) {
		return $this->setParameter('merchant_secret', $value);
	}
	
	public function setLocale($value) {
		return $this->setParameter('locale', $value);
	}

	public function setRedirectMethod($value) {
		return $this->setParameter('redirect_method', $value);
	}
	
	public function setPaymentType($value) {
		return $this->setParameter('payment_type', $value);
	}
	
}

<?php
namespace Omnipay\Paytrail;

use Omnipay\Common\AbstractGateway;

/**
 * Paytrail gateway
 */
class Gateway extends AbstractGateway {

	public function getName() {
		return 'Paytrail';
	}

	public function getDefaultParameters() {
		return array(
			'merchant_id' => '13466',
			'merchant_secret' => '6pKF4jkv97zmqBJ3ZL8gUw5DfT2NMQ',
			'redirect_method' => 'POST',
			'payment_type' => 'S1'
		);
	}
	
	public function setMerchantId($value) {
		return $this->setParameter('merchant_id', $value);
	}
	
	public function setMerchantSecret($value) {
		return $this->setParameter('merchant_secret', $value);
	}
	
	public function setRedirectMethod($value) {
		return $this->setParameter('redirect_method', $value);
	}
	
	public function setPaymentType($value) {
		return $this->setParameter('payment_type', $value);
	}
	
	public function purchase(array $parameters = array()) {
		foreach ($this->parameters->all() as $key => $value) {
			$parameters[$key] = $value;
		}
		return $this->createRequest('\Omnipay\Paytrail\Message\PurchaseRequest', $parameters);
	}
	
	public function completePurchase(array $parameters = array()) {
		foreach ($this->parameters->all() as $key => $value) {
			$parameters[$key] = $value;
		}
		return $this->createRequest('\Omnipay\Paytrail\Message\CompletePurchaseRequest', $parameters);
	}
	
}

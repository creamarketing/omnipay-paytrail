<?php
namespace Omnipay\Paytrail\Message;

use Omnipay\Common\Message\AbstractResponse;

abstract class Response extends AbstractResponse {
	
	public function getType() {
		if (isset($this->data['payment_type'])) {
			return $this->data['payment_type'];
		}
		return 'S1';
	}
	
	public function getAuthCode($data) {
		$hashData = '';
		if ($this instanceof PurchaseResponse) {
			$hashData .= $this->data['merchant_secret'];
		}
		foreach ($data as $fieldName => $fieldData) {
			if ($hashData) {
				$hashData .= '|';
			}
			$hashData .= $fieldData;
		}
		if ($this instanceof CompletePurchaseResponse) {
			$hashData .= '|'.$this->data['merchant_secret'];
		}
		$algo = 'md5';
		if ($this->getType() == 'E2') {
			$algo = 'sha256';
		}
		return strtoupper(hash($algo, $hashData));
	}

}

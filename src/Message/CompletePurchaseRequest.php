<?php

namespace Omnipay\Paytrail\Message;

class CompletePurchaseRequest extends Request {
	
	public function sendData($data) {
		return $this->response = new CompletePurchaseResponse($this, $data);
	}
	
}

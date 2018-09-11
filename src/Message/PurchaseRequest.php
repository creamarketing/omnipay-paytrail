<?php

namespace Omnipay\Paytrail\Message;

class PurchaseRequest extends Request {
	
	public function sendData($data) {
		return $this->response = new PurchaseResponse($this, $data);
	}
	
}

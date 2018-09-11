<?php
namespace Omnipay\Paytrail\Message;

class CompletePurchaseResponse extends Response {
	
	protected $message = null;
	
	public function isSuccessful() {
		// check return auth code
		$status = $this->checkReturnHashPayment();
		if ($status !== true) {
			$this->message = $status;
		}
		return $status === true;
	}
	
	public function getTransactionId() {
		return isset($_GET['ORDER_NUMBER']) ? $_GET['ORDER_NUMBER'] : null;
	}
	
	public function getMessage() {
		return $this->message;
	}
	
	protected function checkReturnHashPayment() {
		$status = 'Unknown error';
		$type = $this->getType();
		if ($type == 'E2') {
			if (isset($_GET['PAYMENT_ID']) &&
				isset($_GET['ORDER_NUMBER']) &&
				isset($_GET['AMOUNT']) &&
				isset($_GET['PAYMENT_METHOD']) &&
				isset($_GET['TIMESTAMP']) &&
				isset($_GET['STATUS']) &&
				isset($_GET['RETURN_AUTHCODE'])) {
				
				$data = array(
					'PAYMENT_ID' => $_GET['PAYMENT_ID'],
					'ORDER_NUMBER' => $_GET['ORDER_NUMBER'],
					'AMOUNT' => $_GET['AMOUNT'],
					'PAYMENT_METHOD' => $_GET['PAYMENT_METHOD'],
					'TIMESTAMP' => $_GET['TIMESTAMP'],
					'STATUS' => $_GET['STATUS']
				);
				$calculatedAuthCode = $this->getAuthCode($data);
				$returnedAuthCode = $_GET['RETURN_AUTHCODE'];
				if ($calculatedAuthCode == $returnedAuthCode) {
					$status = true;
				}
				else {
					$status = 'Incorrect return auth code!';
				}
			}
			else {
				$status = 'Insufficient parameters returned from Paytrail!';
			}
		}
		if ($type == 'S1' || $type == 'E1') {
			if (isset($_GET['ORDER_NUMBER']) &&
				isset($_GET['TIMESTAMP']) &&
				isset($_GET['PAID']) &&
				isset($_GET['METHOD']) &&
				isset($_GET['RETURN_AUTHCODE'])) {
				
				$data = array(
					'ORDER_NUMBER' => $_GET['ORDER_NUMBER'],
					'TIMESTAMP' => $_GET['TIMESTAMP'],
					'PAID' => $_GET['PAID'],
					'METHOD' => $_GET['METHOD']
				);
				$calculatedAuthCode = $this->getAuthCode($data);
				$returnedAuthCode = $_GET['RETURN_AUTHCODE'];
				if ($calculatedAuthCode == $returnedAuthCode) {
					$status = true;
				}
				else {
					$status = 'Incorrect return auth code!';
				}
			}
			else {
				$status = 'Insufficient parameters returned from Paytrail!';
			}
		}
		return $status;
	}
	
}

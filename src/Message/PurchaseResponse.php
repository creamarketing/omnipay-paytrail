<?php
namespace Omnipay\Paytrail\Message;

use Omnipay\Common\Exception\RuntimeException;
use Omnipay\Common\Message\RedirectResponseInterface;
use Symfony\Component\HttpFoundation\RedirectResponse as HttpRedirectResponse;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class PurchaseResponse extends Response implements RedirectResponseInterface {
	
	protected $redirectUrl = '';
	
	public function isSuccessful() {
		return true;
	}
	
	public function isRedirect() {
		return true;
	}
	
	public function getRedirectMethod() {
		if (isset($this->data['redirect_method'])) {
			return $this->data['redirect_method'];
		}
		return 'POST';
	}
	
	public function getRedirectUrl() {
		if (!$this->redirectUrl) {
			if ($this->getRedirectMethod() == 'GET') {
				require_once dirname(__FILE__)."/../Paytrail/Paytrail_Module_Rest.php";
				
				$urlset = new \Paytrail_Module_Rest_Urlset(
					$this->data['returnUrl'],
					$this->data['cancelUrl'],
					$this->data['notifyUrl'],
					""  // pending url not in use
				);
				
				$type = $this->getType();
				if ($type == 'S1') {
					$payment = new \Paytrail_Module_Rest_Payment_S1($this->data['transactionId'], $urlset, $this->data['amount']);
				}
				else if ($type == 'E1' || $type == 'E2') {
					// we use the same payment data for E2 as for E1 for now, REST interface doesn't support it yet
					$contact = new \Paytrail_Module_Rest_Contact(
						$this->data['card']->getBillingFirstName(),
						$this->data['card']->getBillingLastName(),
						$this->data['card']->getEmail(),
						$this->data['card']->getBillingAddress1(),
						$this->data['card']->getBillingPostcode(),
						$this->data['card']->getBillingCity(),
						$this->data['card']->getBillingCountry(),
						$this->data['card']->getBillingPhone(),
						$this->data['card']->getBillingPhone(),
						$this->data['card']->getBillingCompany()
					);
					$payment = new \Paytrail_Module_Rest_Payment_E1($this->data['transactionId'], $urlset, $contact);
					$payment->addProduct($this->data['transactionId'], '', 1, $this->data['amount'], '24.00', 0);
				}
				$payment->setLocale($this->data['locale']);
				
				// Sending payment to Paytrail service and handling possible errors
				$module = new \Paytrail_Module_Rest($this->data['merchant_id'], $this->data['merchant_secret']);
				try {
					$result = $module->processPayment($payment);
				}
				catch (\Paytrail_Exception $e) {
					// processing the error
					// Error description available $e->getMessage()
					throw new RuntimeException('Paytrail error: '.$e->getMessage());
				}
				$this->redirectUrl = $result->getUrl();
			}
			else {
				$this->redirectUrl = 'https://payment.paytrail.com/';
				if ($this->getType() == 'E2') {
					$this->redirectUrl .= 'e2';
				}
			}
		}
		return $this->redirectUrl;
	}
	
	public function getRedirectData() {
		$data = array(
			'MERCHANT_ID' => $this->data['merchant_id']
		);
		$type = $this->getType();
		if ($type == 'S1' || $type == 'E1') {
			if ($type == 'S1') {
				$data = $data + array(
					'AMOUNT' => $this->data['amount']
				);
			}
			$data = $data + array(
				'ORDER_NUMBER' => $this->data['transactionId'],
				'REFERENCE_NUMBER' => '',
				'ORDER_DESCRIPTION' => '',
				'CURRENCY' => 'EUR',
				'RETURN_ADDRESS' => $this->data['returnUrl'],
				'CANCEL_ADDRESS' => $this->data['cancelUrl'],
				'PENDING_ADDRESS' => '',
				'NOTIFY_ADDRESS' => $this->data['notifyUrl'],
				'TYPE' => $type,
				'CULTURE' => $this->data['locale'],
				'PRESELECTED_METHOD' => '',
				'MODE' => '1',
				'VISIBLE_METHODS' => '',
				'GROUP' => ''
			);
			if ($type == 'E1') {
				$data = $data + array(
					'CONTACT_TELNO' => $this->data['card']->getBillingPhone(),
					'CONTACT_CELLNO' => $this->data['card']->getBillingPhone(),
					'CONTACT_EMAIL' => $this->data['card']->getEmail(),
					'CONTACT_FIRSTNAME' => $this->data['card']->getBillingFirstName(),
					'CONTACT_LASTNAME' => $this->data['card']->getBillingLastName(),
					'CONTACT_COMPANY' => $this->data['card']->getBillingCompany(),
					'CONTACT_ADDR_STREET' => $this->data['card']->getBillingAddress1(),
					'CONTACT_ADDR_ZIP' => $this->data['card']->getBillingPostcode(),
					'CONTACT_ADDR_CITY' => $this->data['card']->getBillingCity(),
					'CONTACT_ADDR_COUNTRY' => $this->data['card']->getBillingCountry(),
					'INCLUDE_VAT' => '1',
					'ITEMS' => '1',
					'ITEM_TITLE[0]'  => $this->data['transactionId'],
					'ITEM_NO[0]'  => '',
					'ITEM_AMOUNT[0]' => '1',
					'ITEM_PRICE[0]' => $this->data['amount'],
					'ITEM_TAX[0]' => '24.00',
					'ITEM_DISCOUNT[0]' => '0',
					'ITEM_TYPE[0]' => '1'
				);
			}
		}
		if ($type == 'E2') {
			$data = $data + array(
				'URL_SUCCESS' => $this->data['returnUrl'],
				'URL_CANCEL' => $this->data['cancelUrl'],
				'ORDER_NUMBER' => $this->data['transactionId'],
				'AMOUNT' => $this->data['amount'],
				'PARAMS_IN' => '',
				'PARAMS_OUT' => 'PAYMENT_ID,ORDER_NUMBER,AMOUNT,PAYMENT_METHOD,TIMESTAMP,STATUS',
				'MSG_UI_MERCHANT_PANEL' => '',
				'URL_NOTIFY' => $this->data['notifyUrl'],
				'LOCALE' => $this->data['locale'],
				'CURRENCY' => 'EUR',
				'REFERENCE_NUMBER' => '',
				'PAYMENT_METHODS' => '',
				'PAYER_PERSON_PHONE' => $this->data['card']->getBillingPhone(),
				'PAYER_PERSON_EMAIL' => $this->data['card']->getEmail(),
				'PAYER_PERSON_FIRSTNAME' => $this->data['card']->getBillingFirstName(),
				'PAYER_PERSON_LASTNAME' => $this->data['card']->getBillingLastName(),
				'PAYER_COMPANY_NAME' => $this->data['card']->getBillingCompany(),
				'PAYER_PERSON_ADDR_STREET' => $this->data['card']->getBillingAddress1(),
				'PAYER_PERSON_ADDR_POSTAL_CODE' => $this->data['card']->getBillingPostcode(),
				'PAYER_PERSON_ADDR_TOWN' => $this->data['card']->getBillingCity(),
				'PAYER_PERSON_ADDR_COUNTRY' => $this->data['card']->getBillingCountry(),
				'ALG' => '1'
			);
			$paramsIn = '';
			foreach ($data as $key => $value) {
				if ($paramsIn) {
					$paramsIn .= ',';
				}
				$paramsIn .= $key;
			}
			$data['PARAMS_IN'] = $paramsIn;
		}
		$data['AUTHCODE'] = $this->getAuthCode($data);
		return $data;
	}
	
	public function getRedirectResponse() {
		if (!$this instanceof RedirectResponseInterface || !$this->isRedirect()) {
			throw new RuntimeException('This response does not support redirection.');
		}
	
		if ('GET' === $this->getRedirectMethod()) {
			return HttpRedirectResponse::create($this->getRedirectUrl());
		} elseif ('POST' === $this->getRedirectMethod()) {
			$hiddenFields = '';
			foreach ($this->getRedirectData() as $key => $value) {
				$hiddenFields .= sprintf(
					'<input type="hidden" name="%1$s" value="%2$s" />',
					htmlentities($key, ENT_QUOTES, 'UTF-8', false),
					htmlentities($value, ENT_QUOTES, 'UTF-8', false)
				)."\n";
			}
	
			$output = '<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <title>Redirecting...</title>
    </head>
    <body onload="document.forms[0].submit();">
        <form action="%1$s" method="post">
            <p>
                %2$s
            </p>
        </form>
    </body>
</html>';
			$output = sprintf(
				$output,
				htmlentities($this->getRedirectUrl(), ENT_QUOTES, 'UTF-8', false),
				$hiddenFields
			);
	
			return HttpResponse::create($output);
		}
	
		throw new RuntimeException('Invalid redirect method "'.$this->getRedirectMethod().'".');
	}
	
}

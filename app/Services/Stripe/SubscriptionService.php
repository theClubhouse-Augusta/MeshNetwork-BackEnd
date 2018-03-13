<?php 
namespace App\Services\Stripe;

use Stripe\Stripe;
use Stripe\Customer as StripeCustomer;
use Stripe\Subscription as StripeSubscription;

class SubscriptionService {
        
    
    private $apiKey;
    private $stripeCustomer;
    private $stripeSubscription;
    private $cardToken;
    
    public function __construct($apiKey) {
        $this->apiKey = $apiKey;
        Stripe::setApiKey($apiKey);
    }

    public function getCustomer() { return $this->stripeCustomer; }
    public function getSubscription() { return $this->stripeSubscription; }
    
    public function createCustomer($customerData) {
        try {
            $this->stripeCustomer = StripeCustomer::create(array(
                "source" => $customerData['cardToken'], // obtained with Stripe.js
                "email" => $customerData['email'],
            ), array(
                "idempotency_key" => $customerData['customer_idempotency_key']
            ));
        } catch(\Stripe\Error\Card $e) {
            return "card error";
        } catch (\Stripe\Error\RateLimit $e) {
            return "rateLimit error";
        } catch (\Stripe\Error\InvalidRequest $e) {
            $body = $e->getJsonBody();
            $err  = $body['error'];
            $error = 'Status is:' . $e->getHttpStatus() . "\n";
            $error = $error.'Type is:' . $err['type'] . "\n";
            $error = array_key_exists('code', $err) ? $error.'Code is:' . $err['code'] . "\n" : $error;
            // param is '' in this case
            $error = array_key_exists('param', $err) ? error.'Param is:' . $err['param'] . "\n" : $error;
            $error = $error.'Message is:' . $err['message'] . "\n";
            return $error;
        } catch (\Stripe\Error\Authentication $e) {
            return "auth error";
        } catch (\Stripe\Error\ApiConnection $e) {
            return "apiconn error";
        } catch (\Stripe\Error\Api $e) {
            return "api error";
        } catch (\Stripe\Error\Permission $e) {
            return "permission error";
        } catch (\Stripe\Error\SignatureVerification $e) {
            return "sig error";
        } catch (\Stripe\Error\Base $e) {
            return "base error";
        } catch (\Exception $e) {
            return "error";
        } finally {
            if ($this->stripeCustomer != NULL) {
                $this->createSubscription($this->stripeCustomer, $customerData);
            }
        }
        return (($this->stripeSubscription != NULL) && ($this->stripeCustomer != NULL));

    }
        
    private function createSubscription($customer, $customerData) {
        try {
            $this->stripeSubscription = StripeSubscription::create(array(
                "customer" => $customer['id'],
                 "items" => array(
                    array(
                        "plan" => $customerData['plan'],
                    ),
                )), array(
                    "idempotency_key" => $customerData['subscription_idempotency_key']
            ));
        } catch(\Stripe\Error\Card $e) {
            return "card error";
        } catch (\Stripe\Error\RateLimit $e) {
            return "rateLimit error";
        } catch (\Stripe\Error\InvalidRequest $e) {
            $body = $e->getJsonBody();
            $err  = $body['error'];
            $error = 'Status is:' . $e->getHttpStatus() . "\n";
            $error = $error.'Type is:' . $err['type'] . "\n";
            $error = array_key_exists('code', $err) ? $error.'Code is:' . $err['code'] . "\n" : $error;
            // param is '' in this case
            $error = array_key_exists('param', $err) ? error.'Param is:' . $err['param'] . "\n" : $error;
            $error = $error.'Message is:' . $err['message'] . "\n";
            return $error;
        } catch (\Stripe\Error\Authentication $e) {
            return "auth error";
        } catch (\Stripe\Error\ApiConnection $e) {
            return "apiconn error";
        } catch (\Stripe\Error\Api $e) {
            return "api error";
        } catch (\Stripe\Error\Permission $e) {
            return "permission error";
        } catch (\Stripe\Error\SignatureVerification $e) {
            return "sig error";
        } catch (\Stripe\Error\Base $e) {
            return "base error";
        } catch (\Exception $e) {
            return "error";
        }

    }
}
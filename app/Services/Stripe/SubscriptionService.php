<?php 
namespace App\Services\Stripe;
use App\Services\ExceptionFormatter;
use App\User;
use Stripe\Stripe;
use Stripe\Customer as StripeCustomer;
use Stripe\Subscription as StripeSubscription;

class SubscriptionService {
    private $apiKey;
    private $stripeCustomer;
    private $stripeSubscription;
    private $error;
    
    public function __construct($apiKey) {
        $this->apiKey = $apiKey;
        Stripe::setApiKey($apiKey);
    }

    public function getCustomer() { return $this->stripeCustomer; }
    public function updateCustomer() { return $this->stripeCustomer; }
    public function getSubscription() { return $this->stripeSubscription; }
    public function getError() { return $this->error; }
    
    public function getAllCustomers() { 
        $customerList = new CustomerList();
        return $customerList->getCustomers();
    }
    
    public function getAllCustomersFromDateRange($start, $end) { 
        $customerList = new CustomerList();
        return $customerList->getCustomersFromDateRange($start, $end);
    }
    
    public function getThisMonthsCustomers($start, $end) { 
        $customerList = new CustomerList();
        return $customerList->getThisMonthsCustomers($start, $end);
    }

    public function getBalancesFromDateRange($start, $end) { 
        $balanceList = new BalanceList();
        return $balanceList->getBalancesFromDateRange($start, $end);
    }
    
    public function getAllSubscriptions() { 
        $subscriptionList = new SubscriptionList();
        return $subscriptionList->getSubscriptions();
    }
    
    public function getAllPlans() { 
        $planList = new PlanList();
        return $planList->getPlans();
    }
    
    public function createCustomer($customerData) {
        $exceptionFormatter = new ExceptionFormatter();
        try {
            $this->stripeCustomer = StripeCustomer::create([
                "source" => $customerData['cardToken'], // obtained with Stripe.js
                "email" => $customerData['email'],
                "metadata" => [
                    'userID' => $customerData['userID']
                ],
            ], [
                "idempotency_key" => $customerData['customer_idempotency_key']
            ]);
        } catch(\Stripe\Error\Card $e) {
           $this->error = $exceptionFormatter::formatStripeException($e);
        } catch (\Stripe\Error\RateLimit $e) {
           $this->error = $exceptionFormatter::formatStripeException($e);
        } catch (\Stripe\Error\InvalidRequest $e) {
           $this->error = $exceptionFormatter::formatStripeException($e);
        } catch (\Stripe\Error\Authentication $e) {
           $this->error = $exceptionFormatter::formatStripeException($e);
        } catch (\Stripe\Error\ApiConnection $e) {
           $this->error = $exceptionFormatter::formatStripeException($e);
        } catch (\Stripe\Error\Api $e) {
           $this->error = $exceptionFormatter::formatStripeException($e);
        } catch (\Stripe\Error\Permission $e) {
           $this->error = $exceptionFormatter::formatStripeException($e);
        } catch (\Stripe\Error\SignatureVerification $e) {
           $this->error = $exceptionFormatter::formatStripeException($e);
        } catch (\Stripe\Error\Base $e) {
           $this->error = $exceptionFormatter::formatStripeException($e);
        } catch (\Exception $e) {
            $this->error = $exceptionFormatter::formatException($e);
        } finally {
            if ($this->stripeCustomer != NULL) {
                $this->createSubscription($this->stripeCustomer, $customerData);
            }
        }
        return (($this->stripeSubscription != NULL) && ($this->stripeCustomer != NULL));

    }
        
    private function createSubscription($customer, $customerData) {
        $exceptionFormatter = new ExceptionFormatter();
        try {
            $this->stripeSubscription = StripeSubscription::create([
                "customer" => $customer['id'],
                "metadata" => [ 'userID' => $customerData['userID'] ],
                 "items" => [
                    [
                        "plan" => $customerData['plan'],
                    ],
                 ]
                
            ], [ "idempotency_key" => $customerData['subscription_idempotency_key']]
                );
        } catch(\Stripe\Error\Card $e) {
            $this->error = $exceptionFormatter::formatStripeException($e);
        } catch (\Stripe\Error\RateLimit $e) {
            $this->error = $exceptionFormatter::formatStripeException($e);
        } catch (\Stripe\Error\InvalidRequest $e) {
            $this->error = $exceptionFormatter::formatStripeException($e);
        } catch (\Stripe\Error\Authentication $e) {
            $this->error = $exceptionFormatter::formatStripeException($e);
        } catch (\Stripe\Error\ApiConnection $e) {
            $this->error = $exceptionFormatter::formatStripeException($e);
        } catch (\Stripe\Error\Api $e) {
            $this->error = $exceptionFormatter::formatStripeException($e);
        } catch (\Stripe\Error\Permission $e) {
            $this->error = $exceptionFormatter::formatStripeException($e);
        } catch (\Stripe\Error\SignatureVerification $e) {
            $this->error = $exceptionFormatter::formatStripeException($e);
        } catch (\Stripe\Error\Base $e) {
            $this->error = $exceptionFormatter::formatStripeException($e);
        } catch (\Exception $e) {
            $this->error = $exceptionFormatter::formatException($e);
        }
    }
    
    public function updateCustomerMeshEmail($customerData) {
        $exceptionFormatter = new ExceptionFormatter();
        try {
            $this->stripeCustomer = StripeCustomer::retrieve($customerData['customer_id']);
            $metadata = $this->stripeCustomer->metadata;
            $user = User::where(['email' => $customerData['email'], 'spaceID' => $customerData['spaceID']])->first();
            if (!empty($user)) {
                $this->stripeCustomer->metadata['userID'] = $user['id'];
                $this->stripeCustomer->metadata['updated'] = 1;
                $this->stripeCustomer->save();
            } else {
                $this->stripeCustomer = NULL;
            }
        } catch(\Stripe\Error\Card $e) {
            $this->error = $e;
        } catch (\Stripe\Error\RateLimit $e) {
            $this->error = $e;
        } catch (\Stripe\Error\InvalidRequest $e) {
            $this->error = $e;
        } catch (\Stripe\Error\Authentication $e) {
            $this->error = $e;
        } catch (\Stripe\Error\ApiConnection $e) {
            $this->error = $e;
        } catch (\Stripe\Error\Api $e) {
            $this->error = $e;
        } catch (\Stripe\Error\Permission $e) {
            $this->error = $e;
        } catch (\Stripe\Error\SignatureVerification $e) {
            $this->error = $e;
        } catch (\Stripe\Error\Base $e) {
            $this->error = $e;
        } catch (\Exception $e) {
            $this->error = $e;
        } 
        finally {
            if ($this->stripeCustomer != NULL) {
                return true;
            } else {
                return false;
            }
        }
    }
    
}
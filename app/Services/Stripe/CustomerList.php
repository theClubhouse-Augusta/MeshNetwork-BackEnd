<?php
namespace App\Services\Stripe;
use Stripe\Customer;
use App\User;
use App\Services\ExceptionFormatter;

class CustomerList {
    private $customers = [];
    private $error;
    
    public function getCustomers() { 
        $this->getCustomersWithMetaData();
        if (!empty($this->customers)) {
            return $this->customers;
        } else {
            return $this->error; 
        }
    }
    
    public function getCustomersFromDateRange($start, $end) { 
        $this->getCustomersFromDate($start, $end);
        if (!empty($this->customers)) {
            return $this->customers;
        } else {
            return $this->error; 
        }
    }
    
    private function getCustomersFromDate($start, $end) {
        try {
            $customerList = Customer::all([
                "limit" => 100, 
                "created" => [ 
                    "gte" => $start,
                    "lte" => $end, 
                ]
            ]);
            foreach ($customerList->autoPagingIterator() as $customer) {
                $userID = $customer['metadata']['userID'];
                $updated = $customer['metadata']['updated'];
                if ($userID != NULL) {
                    $user = User::where('id', intval($userID))->first();
                    if (!empty($user)) {
                        $customer->meshEmail = $user['email'];
                        $customer->meshName = $user['name'];
                    }
                }
                if ($updated != NULL) {
                    $customer->updated = $updated;
                }

                array_push($this->customers, $customer);
           }
        } catch(\Stripe\Error\Card $e) {
        //    $this->error = $exceptionFormatter::formatStripeException($e);
           //$this->error = $e;
           $this->error = "ten";
        } catch (\Stripe\Error\RateLimit $e) {
        //    $this->error = $exceptionFormatter::formatStripeException($e);
           //$this->error = $e;
           $this->error = "nine";
        } catch (\Stripe\Error\InvalidRequest $e) {
        //    $this->error = $exceptionFormatter::formatStripeException($e);
//            $this->error = $e;
           $this->error = "eigth";
        } catch (\Stripe\Error\Authentication $e) {
        //    $this->error = $exceptionFormatter::formatStripeException($e);
//            $this->error = $e;
           $this->error = "seven";
        } catch (\Stripe\Error\ApiConnection $e) {
        //    $this->error = $exceptionFormatter::formatStripeException($e);
//            $this->error = $e;
           $this->error = "six";
        } catch (\Stripe\Error\Api $e) {
        //    $this->error = $exceptionFormatter::formatStripeException($e);
//            $this->error = $e;
           $this->error = "five";
        } catch (\Stripe\Error\Permission $e) {
        //    $this->error = $exceptionFormatter::formatStripeException($e);
//            $this->error = $e;
           $this->error = "four";
        } catch (\Stripe\Error\SignatureVerification $e) {
        //    $this->error = $exceptionFormatter::formatStripeException($e);
//            $this->error = $e;
           $this->error = "three";
        } catch (\Stripe\Error\Base $e) {
        //    $this->error = $exceptionFormatter::formatStripeException($e);
//            $this->error = $e;
           $this->error = "two";
        } catch (\Exception $e) {
            // $this->error = $exceptionFormatter::formatException($e);
           //$this->error = $e;
           $this->error = "one";
        } 
    }
    
    private function getCustomersWithMetaData() {
        // $exceptionFormatter = new ExceptionFormatter();
        try {
            $customerList = Customer::all(array("limit" => 100));
            foreach ($customerList->autoPagingIterator() as $customer) {
                $userID = $customer['metadata']['userID'];
                $updated = $customer['metadata']['updated'];
                if ($userID != NULL) {
                    $user = User::where('id', intval($userID))->first();
                    if (!empty($user)) {
                        $customer->meshEmail = $user['email'];
                        $customer->meshName = $user['name'];
                    }
                }
                if ($updated != NULL) {
                    $customer->updated = $updated;
                }

                array_push($this->customers, $customer);
           }
        } catch(\Stripe\Error\Card $e) {
        //    $this->error = $exceptionFormatter::formatStripeException($e);
           $this->error = $e;
        } catch (\Stripe\Error\RateLimit $e) {
        //    $this->error = $exceptionFormatter::formatStripeException($e);
           $this->error = $e;
        } catch (\Stripe\Error\InvalidRequest $e) {
        //    $this->error = $exceptionFormatter::formatStripeException($e);
           $this->error = $e;
        } catch (\Stripe\Error\Authentication $e) {
        //    $this->error = $exceptionFormatter::formatStripeException($e);
           $this->error = $e;
        } catch (\Stripe\Error\ApiConnection $e) {
        //    $this->error = $exceptionFormatter::formatStripeException($e);
           $this->error = $e;
        } catch (\Stripe\Error\Api $e) {
        //    $this->error = $exceptionFormatter::formatStripeException($e);
           $this->error = $e;
        } catch (\Stripe\Error\Permission $e) {
        //    $this->error = $exceptionFormatter::formatStripeException($e);
           $this->error = $e;
        } catch (\Stripe\Error\SignatureVerification $e) {
        //    $this->error = $exceptionFormatter::formatStripeException($e);
           $this->error = $e;
        } catch (\Stripe\Error\Base $e) {
        //    $this->error = $exceptionFormatter::formatStripeException($e);
           $this->error = $e;
        } catch (\Exception $e) {
            // $this->error = $exceptionFormatter::formatException($e);
           $this->error = $e;
        } 
    }
}
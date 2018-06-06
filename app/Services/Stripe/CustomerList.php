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
    
    public function getThisMonthsCustomers($start, $end) { 
        $this->getCustomersForMonth($start, $end);
        if (!empty($this->customers)) {
            return $this->customers;
        } else {
            return 0; 
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
    
    private function getCustomersForMonth($start, $end) {
        try {
            $customerList = Customer::all([
                "limit" => 100, 
                "created" => [ 
                    "gte" => $start,
                    "lte" => $end, 
                ]
            ]);
            foreach ($customerList->autoPagingIterator() as $customer) {
                array_push($this->customers, $customer);
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
    }
}
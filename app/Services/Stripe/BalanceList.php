<?php
namespace App\Services\Stripe;
use Stripe\BalanceTransaction;
//use App\Services\ExceptionFormatter;

class BalanceList {
    private $balances = [];
    private $error;

    public function getBalancesFromDateRange($start, $end) { 
        $this->getBalancesFromDate($start, $end);
        if (!empty($this->balances)) {
            return $this->balances;
        } else {
            return ['error' => 1 ]; 
        }
    }

    private function getBalancesFromDate($start, $end) {
        try {
            $balanceList = BalanceTransaction::all([
                "limit" => 100, 
                "created" => [ 
                    "gte" => $start,
                    "lte" => $end, 
                ]
            ]);
            foreach ($balanceList->autoPagingIterator() as $balance) {
                array_push($this->balances, $balance);
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
}
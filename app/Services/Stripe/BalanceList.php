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
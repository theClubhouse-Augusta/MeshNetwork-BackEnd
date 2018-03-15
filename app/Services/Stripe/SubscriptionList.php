<?php
namespace App\Services\Stripe;

use Stripe\Subscription;

class SubscriptionList {
    protected function getSubscriptions() {
        try {
            $subscriptionList = Subscription::all(array("limit" => 100));
            $subscriptions = [];
            foreach ($subscriptionList->autoPagingIterator() as $subscription) {
                array_push($subscriptions, $subscription);
            }
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
            return $plans;
        }
    }
}
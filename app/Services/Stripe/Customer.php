<?php
namespace App\Services\Stripe;
use Stripe\Customer as StripeCustomer;

class Customer {
    private $email;
    
    public function __construct($cardToken, $email) {
        $this->email = $email;
        try {
            StripeCustomer::create(array(
                "source" => $cardToken, // obtained with Stripe.js
                "email" => $email
            ));
            $this->createSubscription($this->stripeCustomer);
        } catch(\Stripe\Error\Card $e) {
        } catch (\Stripe\Error\RateLimit $e) {
        } catch (\Stripe\Error\InvalidRequest $e) {
        } catch (\Stripe\Error\Authentication $e) {
        } catch (\Stripe\Error\ApiConnection $e) {
        } catch (\Stripe\Error\Api $e) {
        } catch (\Stripe\Error\Permission $e) {
        } catch (\Stripe\Error\SignatureVerification $e) {
        } catch (\Stripe\Error\Base $e) {
        } catch (\Exception $e) {
        }
    }
}
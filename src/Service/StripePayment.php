<?php

namespace App\Service;

use Stripe\Checkout\Session;
use Stripe\Stripe;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class StripePayment
{
    private $redirectUrl;

    public function __construct()
    {
        // Stripe::setApiKey($this->params->get('STRIPE_SECRET')); // this is better
        Stripe::setApiKey($_SERVER['STRIPE_SECRET']);
        Stripe::setApiVersion('2024-06-20');
    }

    public function startPayment($cart, $shippingCost)
    {
        $cartProducts = $cart['cart'];
        $products = [
            [
                'qte' => 1,
                'price' => $shippingCost,
                'name' => "frais de livraison"
            ]
        ];

        foreach ($cartProducts as $value) {
            $productItem = [];
            $productItem['name'] = $value['product']->getName();
            $productItem['price'] = $value['price']->getPrice();
            $productItem['qte'] = $value['quantity'];
            $products[] = $productItem;
        }

        $session = Session::create([
            'line_items' => [
                array_map(fn(array $product)=> [
                    'quantity' => $product['qte'],
                    'price_data' => [
                        'currency' => 'EUR',
                        'product_data' => [
                            'name' => $product['name'],
                        ],
                        'unit_amount' => $product['price'] * 100 ,
                    ],
                ],$products)
            ],
            'mode' => 'payment',
            'cancel_url' => 'http://commerce.loc/pay/cancel',
            'success_url' => 'http://commerce.loc/pay/success',
            'billing_address_collection' => 'required',
            'shupping_address_collection' => [
                'allowed_countries' => ['FR', 'CM'],
            ],
            'metadata' => [

            ]
        ]);

        $this->redirectUrl = $session->url;
    }

    public function getStripeRedirectUrl(): string
    {
        return $this->redirectUrl;
    }
}
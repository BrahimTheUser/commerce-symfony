<?php

namespace App\Service;

use App\Repository\ProductRepository;

class Cart 
{
    public function __construct(private readonly ProductRepository $productRepository){}
    public function getCart($session) {
        $cart = $session->get('cart', []);
        $cartWithData = [];
        foreach ($cart as $id => $quantity) {
            $cartWithData[] = [
                'product' => $this->productRepository->find($id),
                'quantity' => $quantity
            ];
        }
        $total = array_sum(array_map(
            fn ($item) => $item['product']->getPrice() * $item['quantity'],
            $cartWithData
        ));

        return [
            'total'=>$total,
            'cart'=> $cartWithData
        ];
    }
}
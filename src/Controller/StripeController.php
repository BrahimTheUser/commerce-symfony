<?php

namespace App\Controller;

use Stripe\Stripe;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class StripeController extends AbstractController
{
    #[Route('/pay/success', name: 'app_stripe_success')]
    public function success(): Response
    {
        return $this->render('stripe/index.html.twig', [
            'controller_name' => 'StripeController',
        ]);
    }

    #[Route('/pay/cancel', name: 'app_stripe_cancel')]
    public function cancel(): Response
    {
        return $this->render('stripe/index.html.twig', [
            'controller_name' => 'StripeController',
        ]);
    }

    #[Route('/stripe/notify', name: 'app_stripe_notify')]
    public function stripeNotify(Request $request): Response
    {
        Stripe::setApiKey($_SERVER['STRIPE_SECRET']);

        $endpoint_secret = 'the config of the secret in the video';

        $payload = $request->getContent();

        $seg_header = $request->headers->get('stripe-signature');

        $event = null;

        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload, $seg_header, $endpoint_secret
            );
        } catch (\UnexpectedValueException $e) {
            return new Response('payload invalide', 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            return new Response('Signature invalide');
        }

        switch ($event->type) {
            case 'payment_intent.succeeded': // contient l'object payment_intent
                $paymentIntent = $event->data->object;
                $fileName = 'stripe-details'.uniqid().'txt';
                file_put_contents($fileName, $paymentIntent);
                break;
            case 'payment_method.attached': // contient l'object payment_method
                $paymentMethod = $event->data->object;
                break;
            default:
                break;
        }

        return new Response('evenement reçu', 200);
    }
}

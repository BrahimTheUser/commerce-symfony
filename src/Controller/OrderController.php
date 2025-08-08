<?php

namespace App\Controller;

use App\Entity\City;
use App\Entity\Order;
use App\Entity\OrderProducts;
use App\Form\OrderType;
use App\Repository\OrderRepository;
use App\Repository\ProductRepository;
use App\Service\Cart;
use App\Service\StripePayment;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Attribute\Route;

class OrderController extends AbstractController
{
    public function __construct(private MailerInterface $mailer){}


    /**
     * @throws TransportExceptionInterface
     */
    #[Route('/order', name: 'app_order')]
    public function index(Request $request, 
        SessionInterface $session, 
        ProductRepository $productRepository,
        EntityManagerInterface $entityManager,
        Cart $cart
    ): Response
    {
        $data = $cart->getCart($session);

        $order = new Order();
        $form = $this->createForm(OrderType::class, $order);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($order->isPayOnDelivery()) {

                if (!empty($data['total'])) {

                    $order->setTotalPrice($data['total']);
                    $order->setCreateAt(new \DateTimeImmutable());
                    $entityManager->persist($order);
                    $entityManager->flush();

                    foreach ($data['cart'] as  $value) {
                        $orderProducts = new OrderProducts();
                        $orderProducts->setOrder($order);
                        $orderProducts->setProduct($value['product']);
                        $orderProducts->setQte($value['quantity']);
                        $entityManager->persist($orderProducts);
                        $entityManager->flush();
                    }
                }
                $session->set('cart', []);

                $html = $this->renderView('mail/orderConfirm.html.twig', [
                    'order' => $order,
                ]);

                $email = (new Email())
                ->from('myShop@gmail.com')
                ->to($order->getEmail())
                ->subject('Confirmation de réception de commande')
                ->html($html);

                // for the email to be sent, you should make the: php bin/console messager:consume async -vv

                $this->mailer->send($email);

                return $this->redirectToRoute('order_ok_message');
            }

            //
            $payment = new StripePayment();
            $shippingCost = $order->getCity()->getShippingCost();
            $payment->startPayment($data, $shippingCost);
            $stripeRedirectUrl = $payment->getStripeRedirectUrl();
            return $this->redirect($stripeRedirectUrl);
        }

        return $this->render('order/index.html.twig', [
            'form' => $form->createView(),
            'total' => $data['total'],
        ]);
    }

    #[Route('order-ok-message', name: 'order_ok_message')]
    public function orderMessage()
    {
        return $this->render('order/order_message.html.twig');
    }

    #[Route('/editor/order', name: 'app_order_show')]
    public function showAllOrder(OrderRepository $orderRepository): Response
    {
        $order = $orderRepository->findAll();
        // dd($order);
        return $this->render('order/order.html.twig', [
            'orders' => $order,
        ]);
    }

    #[Route('/editor/order/{id}/is-completed/update', name: 'app_order_is_completed_update')]
    public function isCompletedUpdate($id, OrderRepository $orderRepository, EntityManagerInterface $entityManager): Response
    {
        $order = $orderRepository->find($id);
        $order->setCompleted(true);
        $entityManager->flush();
        $this->addFlash('success', 'La commande a été marquée comme complétée');
        return $this->redirectToRoute('app_order_show');
    }

    #[Route('/editor/order/{id}/remove', name: 'app_order_remove')]
    public function removeOrder(Order $order, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($order);
        $entityManager->flush();
        $this->addFlash('danger', 'La commande a été Supprimée');
        return $this->redirectToRoute('app_order_show');
    }

    #[Route('/city/{id}/shipping/cost', name: 'app_city_shipping_cost')]
    public function cityShippingCost(City $city): Response 
    {
        $shippingCost = $city->getShippingCost();
        // return $this->json(['shippingCost' => $shippingCost]);
        return new Response(json_encode(['shippingCost' => $shippingCost, 'status' => '200', 'message' => 'on']));
    }
}

<?php

namespace App\Controller;

use App\Repository\OrderRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Dompdf\Options;
use Dompdf\Dompdf;

class BillController extends AbstractController
{
    #[Route('/editor/order/{id}/bill', name: 'app_bill')]
    public function index($id, OrderRepository $orderRepository): Response
    {
        $order = $orderRepository->find($id);

        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');
        $dompdf = new Dompdf($pdfOptions);
        $html = $this->renderView('bill/index.html.twig', [
            'order' => $order,
        ]);
        $dompdf->loadHtml($html);
        // $dompdf->setPaperSize('A4', 'portrait');
        $dompdf->render();
        $dompdf->stream("My_shop_bill_{$id}.pdf", [
            "Attachment" => false, // to not install it directly, but show it as pdf in the navigator
        ]);

        return new Response('', 200, [
            'Content-Type' => 'application/pdf',
        ]);
    }
}

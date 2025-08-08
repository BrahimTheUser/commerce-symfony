<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use App\Repository\SubCategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(
        ProductRepository $productRepository, 
        CategoryRepository $categoryRepository, 
        Request $request,
        PaginatorInterface $paginator
    ): Response
    {
        $data = $productRepository->findBy([], ['id' => "DESC"]);
        $products = $paginator->paginate(
            $data,
            $request->query->getInt('page', 1),
            6
        );

        return $this->render('home/index.html.twig', [
            'products' => $products,
            'categories' => $categoryRepository->findAll()
        ]);
    }

    #[Route('/product/{id}/show', name: 'home_product_show')]
    public function show(Product $product, ProductRepository $productRepository, CategoryRepository $categoryRepository): Response
    {
        $latest = $productRepository->findBySubcategoryExcludingProduct(
            $product->getId(), 
            $product->getSubCategories()[0]->getName()
        );

        return $this->render('home/show.html.twig', [
            'product' => $product,
            'latest' => $latest,
            'categories' => $categoryRepository->findAll()
        ]);
    }

    #[Route('/product/subcategory/{id}/show/filter', name: 'app_home_product_filter')]
    public function filter($id, SubCategoryRepository $subCategoryRepository, CategoryRepository $categoryRepository): Response
    {
        $products = $subCategoryRepository->find($id)->getProducts();
        return $this->render('home/filter.html.twig', [
            'products' => $products,
           'subcategory' => $subCategoryRepository->find($id)->getName(),
           'categories' => $categoryRepository->findAll()
        ]);
    }

    // this route is used by javascript to make translations available
    // #[Route('/changeLocale', name: 'app_locale_change')]
    // public function changeLocale(RequestStack $requestStack, Request $request, EntityManagerInterface $em): JsonResponse
    // {
    //     $langue = $request->request->get('langue');
    //     $user = $this->getUser();
    //     if ($user) {
    //         $user->setLocale($langue);
    //         $em->persist($user);
    //         $em->flush();
    //     }
        
    //     $requestStack->getSession()->set('_locale', $langue);
        
    //     return new JsonResponse(['success' => true]);
    // }
    #[Route('/changeLocale/{langue}', name: 'app_locale_change')]
    public function changeLocale(string $langue, RequestStack $requestStack, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        if ($user) {
            $user->setLocale($langue);
            $em->persist($user);
            $em->flush();
        }
        
        $requestStack->getSession()->set('_locale', $langue);
        
        return $this->redirect($this->generateUrl('app_home')); // Redirect to home or referer
    }
}

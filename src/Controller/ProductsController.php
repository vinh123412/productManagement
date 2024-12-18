<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class ProductsController extends AbstractController
{
    // get products list
    #[Route('/api/products', methods: ['GET'])]
    public function getProductsList(ProductRepository $productRepository): JsonResponse
    {
        $products = $productRepository->getProductsList();
        return $this->json($products);
    }

    // search products by brand
    #[Route('/api/products/search-by-brand', methods: ['GET'])]
    public function searchByBrand(Request $request, ProductRepository $productRepository): JsonResponse
    {
        $brandName = $request->query->get('brand');

        if (!$brandName) {
            return $this->json(['error' => 'Brand name is required'], 400);
        }

        $products = $productRepository->findProductsByBrand($brandName);

        return $this->json($products);
    }

    // search products by price
    #[Route('/api/products/search-by-price', methods: ['GET'])]
    public function searchByPrice(Request $request, ProductRepository $productRepository): JsonResponse
    {
        $price = $request->query->get('price');

        if (!$price) {
            return $this->json(['error' => 'Price is required'], 400);
        }

        $products = $productRepository->findProductsByPrice((float) $price);

        if (empty($products)) {
            return $this->json(['message' => 'No products found for the given price'], 404);
        }

        return $this->json($products);
    }

    // get product details
    #[Route('/api/products/{id}', methods: ['GET'])]
    public function getProductDetail(int $id, ProductRepository $productRepository): JsonResponse
    {
        $product = $productRepository->findProductById($id);

        if (!$product) {
            return $this->json(['message' => 'Product not found'], 404);
        }

        return $this->json($product);
    }
}

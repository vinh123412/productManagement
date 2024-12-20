<?php

namespace App\Controller;

use App\Entity\Cart;
use App\Entity\CartProduct;
use App\Entity\Product;
use App\Repository\CartProductRepository;
use App\Repository\CartRepository;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class CartController extends AbstractController
{
    #[Route('/api/cart', methods: ['POST'])]
    public function addToCart(
        Request $request,
        ProductRepository $productRepository,
        CartRepository $cartRepository,
        CartProductRepository $cartProductRepository,
        EntityManagerInterface $entityManager,
        JWTTokenManagerInterface $jwtManager
    ): JsonResponse {
        // Lấy user từ token đã được giải mã
        $user = $this->getUser(); 
        if (!$user) {
            return $this->json(['error' => 'Unauthorized'], 401);
        }

        $data = json_decode($request->getContent(), true);
        $productId = $data['product_id'] ?? null;
        $quantity = $data['quantity'] ?? 1;

        if (!$productId || $quantity < 1) {
            return $this->json(['error' => 'Invalid product or quantity'], 400);
        }

        // Tìm sản phẩm theo ID
        $product = $productRepository->find($productId);
        if (!$product) {
            return $this->json(['error' => 'Product not found'], 404);
        }

        // Lấy giỏ hàng của user hiện tại
        $cart = $cartRepository->findOneBy(['user' => $user]);
        if (!$cart) {
            // Nếu chưa có giỏ hàng, tạo mới
            $cart = new Cart();
            $cart->setUser($user);
            $entityManager->persist($cart);
            $entityManager->flush();
        }

        // Kiểm tra sản phẩm đã có trong giỏ hàng chưa
        $cartProduct = $cartProductRepository->findOneBy(['cart' => $cart, 'product' => $product]);
        if ($cartProduct) {
            // Nếu đã có, cập nhật số lượng
            $cartProduct->setQuantity($cartProduct->getQuantity() + $quantity);
        } else {
            // Nếu chưa có, tạo mới
            $cartProduct = new CartProduct();
            $cartProduct->setCart($cart);
            $cartProduct->setProduct($product);
            $cartProduct->setQuantity($quantity);

            $entityManager->persist($cartProduct);
        }

        $entityManager->flush();

        return $this->json(['message' => 'Product added to cart successfully']);
    }

    #[Route('/api/cart', methods: ['GET'])]
    public function getCart(
        CartRepository $cartRepository,
        CartProductRepository $cartProductRepository
    ){
        $user = $this->getUser(); 
        if (!$user) {
            return $this->json(['error' => 'Unauthorized'], 401);
        }

        $cart = $cartRepository->findOneBy(['user' => $user]);
        if(!$cart){
            return $this->json(['cart' => [], 'totalPrice' => 0]);
        }

        $cartProducts = $cartProductRepository->findBy(['cart' => $cart]);
        if(!$cartProducts){
            return $this->json(['cart' => [], 'totalPrice' => 0]);
        }

        $items = [];
        $total = 0;
        foreach($cartProducts as $cartProduct){
            $product = $cartProduct->getProduct();
            $items[] = [
                'product_id' => $product->getId(),
                'name' => $product->getName(),
                'price' => $product->getPrice(),
                'quantity' => $cartProduct->getQuantity(),
                'subtotal' => $product->getPrice() * $cartProduct->getQuantity(),
            ];
            $total += $product->getPrice() * $cartProduct->getQuantity();
        }

        return $this->json(['item' => $items,'totalPrice' => $total]);
    }

    #[Route('/api/cart', methods: ['PUT'])]
    public function updateCart(
        Request $request,
        CartRepository $cartRepository,
        CartProductRepository $cartProductRepository,
        ProductRepository $productRepository,
        EntityManagerInterface $entityManager
    ){
        $user = $this->getUser(); 
        if (!$user) {
            return $this->json(['error' => 'Unauthorized'], 401);
        }

        $data = json_decode($request->getContent(), true);
        $productId = $data['product_id'] ?? null;
        $quantity = $data['quantity'] ?? null;

        if (!$productId || $quantity === null || $quantity < 0) {
            return $this->json(['error' => 'Invalid product or quantity'], 400);
        }

        $cart = $cartRepository->findOneBy(['user' => $user]);
        if(!$cart){
            return $this-> json(['error' => 'Cart not found'], 404);
        }

        $cartProduct = $cartProductRepository->findOneBy(['cart' => $cart,'product' => $productId]);
        if(!$cartProduct){
            return $this-> json(['error' => 'Product not found in cart'], 404);
        }

        if($quantity == 0){
            $entityManager->remove($cartProduct);
            $entityManager->flush();
        }

        $cartProduct->setQuantity($quantity);
        $entityManager->persist($cartProduct);
        $entityManager->flush();

        return $this->json(['message' => 'Cart updated successfully']);

    }

    #[Route('/api/cart', methods: ['DELETE'])]
    public function removeFromCart(
        Request $request,
        CartRepository $cartRepository,
        CartProductRepository $cartProductRepository,
        EntityManagerInterface $entityManager
    ){
        $user = $this->getUser(); 
        if (!$user) {
            return $this->json(['error' => 'Unauthorized'], 401);
        }

        $data = json_decode($request->getContent(), true);
        $productId = $data['product_id'] ?? null;

        if(!$productId){
            return $this->json(['error' => 'No product found', 400]);
        }

        $cart = $cartRepository->findOneBy(['user' => $user]);
        if(!$cart){
            return $this-> json(['error' => 'Cart not found'], 404); 
        }

        $cartProduct = $cartProductRepository->findOneBy(['cart' => $cart, 'product' => $productId]);
        if(!$cartProduct){
            return $this-> json(['error' => 'Product not found in cart'], 404);
        }

        $entityManager->remove($cartProduct);
        $entityManager->flush();

        return $this->json(['message' => 'Product removed from cart successfully']);

    }
}

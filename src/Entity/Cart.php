<?php

namespace App\Entity;

use App\Repository\CartRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CartRepository::class)]
class Cart
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;


    #[ORM\OneToOne(inversedBy: 'cart', cascade: ['persist', 'remove'])]
    private ?User $user = null;

    /**
     * @var Collection<int, CartProduct>
     */
    #[ORM\OneToMany(targetEntity: CartProduct::class, mappedBy: 'cart')]
    private Collection $cart;

    public function __construct()
    {
        $this->cart = new ArrayCollection();
    }


    public function getId(): ?int
    {
        return $this->id;
    }


    public function getUser(): ?user
    {
        return $this->user;
    }

    public function setUser(?user $user): static
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return Collection<int, CartProduct>
     */
    public function getCart(): Collection
    {
        return $this->cart;
    }

    public function addCart(CartProduct $cart): static
    {
        if (!$this->cart->contains($cart)) {
            $this->cart->add($cart);
            $cart->setCart($this);
        }

        return $this;
    }

    public function removeCart(CartProduct $cart): static
    {
        if ($this->cart->removeElement($cart)) {
            // set the owning side to null (unless already changed)
            if ($cart->getCart() === $this) {
                $cart->setCart(null);
            }
        }

        return $this;
    }

   
}

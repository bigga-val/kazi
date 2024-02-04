<?php

namespace App\Entity;

use App\Repository\LigneBonProduitRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LigneBonProduitRepository::class)]
class LigneBonProduit
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    private ?BonProduit $bonProduit = null;

    #[ORM\ManyToOne]
    private ?LigneFacture $ligneFacture = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBonProduit(): ?BonProduit
    {
        return $this->bonProduit;
    }

    public function setBonProduit(?BonProduit $bonProduit): static
    {
        $this->bonProduit = $bonProduit;

        return $this;
    }

    public function getLigneFacture(): ?LigneFacture
    {
        return $this->ligneFacture;
    }

    public function setLigneFacture(?LigneFacture $ligneFacture): static
    {
        $this->ligneFacture = $ligneFacture;

        return $this;
    }
}

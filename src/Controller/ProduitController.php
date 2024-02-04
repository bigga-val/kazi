<?php

namespace App\Controller;

use App\Entity\BonProduit;
use App\Entity\Produit;
use App\Form\ProduitType;
use App\Repository\FactureRepository;
use App\Repository\ProduitRepository;
use App\Repository\ServiceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\ORM\EntityManagerInterface;

#[Route('/produit')]
class ProduitController extends AbstractController
{
    #[Route('/', name: 'app_produit_index', methods: ['GET'])]
    public function index(ProduitRepository $produitRepository): Response
    {
        return $this->render('produit/index.html.twig', [
            'produits' => $produitRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_produit_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, UserInterface $user): Response
    {
        if($this->isGranted('ROLE_ADMIN') or $this->isGranted('ROLE_GERANT')){
        }else{
            return $this->redirectToRoute('app_user_unauthorized');
        }
        $produit = new Produit();
        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $produit->setCreatedBy($this->getUser()->getUserIdentifier());
            $entityManager->persist($produit);
            $entityManager->flush();

            return $this->redirectToRoute('app_produit_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('produit/new.html.twig', [
            'produit' => $produit,
            'form' => $form,
        ]);
    }

    #[Route('/jsonGetProduct', name: 'jsonGetProduct', methods: ['GET'])]
    public function jsonGetProduct(Request $request,
                                EntityManagerInterface $entityManager,
                                ProduitRepository $produitRepository,
    )
    {
        $produit = $produitRepository->find($request->query->get('productID'));

        return new JsonResponse([
            'etat' => true,
            'prix' => $produit->getPrixUnitaire()
        ]);
    }

    #[Route('/jsonGetProduct2', name: 'jsonGetProduct2', methods: ['GET'])]
    public function jsonGetProduct2(Request $request,
                                   EntityManagerInterface $entityManager,
                                   ServiceRepository $serviceRepository,
    )
    {
        $service = $serviceRepository->find($request->query->get('serviceID'));

        return new JsonResponse([
            'etat' => true,
            'prix' => $service->getPrix()
        ]);
    }

    #[Route('/{id}', name: 'app_produit_show', methods: ['GET'])]
    public function show(Produit $produit): Response
    {
        return $this->render('produit/show.html.twig', [
            'produit' => $produit,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_produit_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Produit $produit, EntityManagerInterface $entityManager): Response
    {
        if($this->isGranted('ROLE_ADMIN') or $this->isGranted('ROLE_GERANT')){
        }else{
            return $this->redirectToRoute('app_user_unauthorized');
        }
        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_produit_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('produit/edit.html.twig', [
            'produit' => $produit,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_produit_delete', methods: ['POST'])]
    public function delete(Request $request, Produit $produit, EntityManagerInterface $entityManager): Response
    {
        if($this->isGranted('ROLE_ADMIN') or $this->isGranted('ROLE_GERANT')){
        }else{
            return $this->redirectToRoute('app_user_unauthorized');
        }
        if ($this->isCsrfTokenValid('delete'.$produit->getId(), $request->request->get('_token'))) {
            $entityManager->remove($produit);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_produit_index', [], Response::HTTP_SEE_OTHER);
    }
}

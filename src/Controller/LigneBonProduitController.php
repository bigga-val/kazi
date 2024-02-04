<?php

namespace App\Controller;

use App\Entity\LigneBonProduit;
use App\Form\LigneBonProduitType;
use App\Repository\BonProduitRepository;
use App\Repository\LigneBonProduitRepository;
use App\Repository\LigneFactureRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/ligne/bon/produit')]
class LigneBonProduitController extends AbstractController
{
    #[Route('/', name: 'app_ligne_bon_produit_index', methods: ['GET'])]
    public function index(LigneBonProduitRepository $ligneBonProduitRepository): Response
    {
        return $this->render('ligne_bon_produit/index.html.twig', [
            'ligne_bon_produits' => $ligneBonProduitRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_ligne_bon_produit_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $ligneBonProduit = new LigneBonProduit();
        $form = $this->createForm(LigneBonProduitType::class, $ligneBonProduit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($ligneBonProduit);
            $entityManager->flush();

            return $this->redirectToRoute('app_ligne_bon_produit_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('ligne_bon_produit/new.html.twig', [
            'ligne_bon_produit' => $ligneBonProduit,
            'form' => $form,
        ]);
    }

    #[Route('/jsonnew', name: 'app_ligne_bon_produit_new_json', methods: ['GET', 'POST'])]
    public function jsonnew(Request $request,
                            BonProduitRepository $bonProduitRepository,
                            LigneFactureRepository $ligneFactureRepository,
                            EntityManagerInterface $entityManager): JsonResponse
    {
        $bon = $bonProduitRepository->find($request->query->get("bonID"));
        $ligne = $ligneFactureRepository->find($request->query->get("ligneID"));


        $ligneBonProduit = new LigneBonProduit();
        $ligneBonProduit->setBonProduit($bon);
        $ligneBonProduit->setLigneFacture($ligne);
        $entityManager->persist($ligneBonProduit);
        $entityManager->flush();

        return new JsonResponse([
            'etat' => true,
            'bonID'=>$ligneBonProduit->getId()
        ]);

    }

    #[Route('/jsondeleteAll', name: 'app_ligne_bon_deletejson', methods: ['GET', 'POST'])]
    public function jsondeleteAll(Request $request,
                            BonProduitRepository $bonProduitRepository,
                            LigneFactureRepository $ligneFactureRepository,
                            LigneBonProduitRepository $ligneBonProduitRepository,
                            EntityManagerInterface $entityManager): JsonResponse
    {
        $lignes = $ligneBonProduitRepository->findBy(['bonProduit'=>$request->query->get('bonID')]);
        foreach ($lignes as $ligne){
            $entityManager->remove($ligne);
            $entityManager->flush();
        }
        return new JsonResponse([
            'etat' => true
        ]);

    }

    #[Route('/{id}', name: 'app_ligne_bon_produit_show', methods: ['GET'])]
    public function show(LigneBonProduit $ligneBonProduit): Response
    {
        return $this->render('ligne_bon_produit/show.html.twig', [
            'ligne_bon_produit' => $ligneBonProduit,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_ligne_bon_produit_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, LigneBonProduit $ligneBonProduit, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(LigneBonProduitType::class, $ligneBonProduit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_ligne_bon_produit_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('ligne_bon_produit/edit.html.twig', [
            'ligne_bon_produit' => $ligneBonProduit,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_ligne_bon_produit_delete', methods: ['POST'])]
    public function delete(Request $request, LigneBonProduit $ligneBonProduit, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$ligneBonProduit->getId(), $request->request->get('_token'))) {
            $entityManager->remove($ligneBonProduit);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_ligne_bon_produit_index', [], Response::HTTP_SEE_OTHER);
    }
}

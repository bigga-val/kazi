<?php

namespace App\Controller;

use App\Entity\Facture;
use App\Entity\LigneFacture;
use App\Form\LigneFactureType;
use App\Repository\ClientRepository;
use App\Repository\LigneFactureRepository;
use App\Repository\ProduitRepository;
use App\Repository\ServiceRepository;
use App\Repository\TypeFactureRepository;
use App\Repository\FactureRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/ligne/facture')]
class LigneFactureController extends AbstractController
{
    #[Route('/', name: 'app_ligne_facture_index', methods: ['GET'])]
    public function index(LigneFactureRepository $ligneFactureRepository): Response
    {
        return $this->render('ligne_facture/index.html.twig', [
            'ligne_factures' => $ligneFactureRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_ligne_facture_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $ligneFacture = new LigneFacture();
        $form = $this->createForm(LigneFactureType::class, $ligneFacture);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($ligneFacture);
            $entityManager->flush();

            return $this->redirectToRoute('app_ligne_facture_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('ligne_facture/new.html.twig', [
            'ligne_facture' => $ligneFacture,
            'form' => $form,
        ]);
    }

    #[Route('/jsonSaveLigneFacture', name: 'jsonSaveLigneFacture', methods: ['GET'])]
    public function jsonSaveLigneFacture(Request $request, EntityManagerInterface $entityManager, FactureRepository $FactureRepository, ProduitRepository $produitRepository)
    {
        $facture = $FactureRepository->find($request->query->get('fatureID'));
        $produit = $produitRepository->find($request->query->get('produitID'));
        $ligneFacture = new LigneFacture();

        try {

            $ligneFacture->setFacture($facture);
            $ligneFacture->setProduit($produit);
            $ligneFacture->setQuantite(floatval($request->query->get('quantity')));
            $ligneFacture->setPrix(floatval($request->query->get('prix')));
            $ligneFacture->setCreatedBy($this->getUser()->getUserIdentifier());
            $ligneFacture->setCreatedAt(new \DateTime());

            $entityManager->persist($ligneFacture);
            $entityManager->flush();
            return new JsonResponse([
                'etat'=>true,
                'id'=>$facture->getId()
            ]);


        }catch (\Exception $exception){
            return new JsonResponse([
                'response'=>$exception->getMessage()
            ]);
        }
//        return new JsonResponse([
//            'etat'=>true
//        ]);
    }


    #[Route('/jsonSaveLigneFactureService', name: 'jsonSaveLigneFactureService', methods: ['GET'])]
    public function jsonSaveLigneFactureService(Request $request,
                                                EntityManagerInterface $entityManager,
                                                FactureRepository $FactureRepository,
                                                ServiceRepository $serviceRepository)
    {
        $facture = $FactureRepository->find($request->query->get('fatureID'));
        $service = $serviceRepository->find($request->query->get('serviceID'));
        $ligneFacture = new LigneFacture();

        try {
            $ligneFacture->setFacture($facture);
            $ligneFacture->setService($service);
            $ligneFacture->setQuantite(floatval($request->query->get('quantity')));
            $ligneFacture->setPrix(floatval($request->query->get('prix')));
            $ligneFacture->setCreatedBy($this->getUser()->getUserIdentifier());
            $ligneFacture->setCreatedAt(new \DateTime());

            $entityManager->persist($ligneFacture);
            $entityManager->flush();
            return new JsonResponse([
                'etat'=>true,
                'id'=>$facture->getId()
            ]);


        }catch (\Exception $exception){
            return new JsonResponse([
                'response'=>$exception->getMessage()
            ]);
        }
//        return new JsonResponse([
//            'etat'=>true
//        ]);
    }



    #[Route('/jsonSupprimerLigneFacture', name: 'jsonSupprimerLigneFacture', methods: ['GET'])]
    public function jsonSupprimerLigneFacture(Request $request,
                                              LigneFactureRepository $ligneFactureRepository,
                                              EntityManagerInterface $entityManager,
                                              )
    {
        try {
            $lignesFacture = $ligneFactureRepository->findBy(['facture'=>$request->query->get('factureID')]);

            foreach ($lignesFacture as $ligneFacture){
                $entityManager->remove($ligneFacture);
                $entityManager->flush();
            }
            return new JsonResponse([
                'etat'=>true
            ]);
        }catch (\Exception $exception){
            return new JsonResponse([
                'response'=>$exception->getMessage()
            ]);
        }
//        return new JsonResponse([
//            'etat'=>true
//        ]);
    }


    #[Route('/{id}', name: 'app_ligne_facture_show', methods: ['GET'])]
    public function show(LigneFacture $ligneFacture): Response
    {
        return $this->render('ligne_facture/show.html.twig', [
            'ligne_facture' => $ligneFacture,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_ligne_facture_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, LigneFacture $ligneFacture, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(LigneFactureType::class, $ligneFacture);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_ligne_facture_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('ligne_facture/edit.html.twig', [
            'ligne_facture' => $ligneFacture,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_ligne_facture_delete', methods: ['POST'])]
    public function delete(Request $request, LigneFacture $ligneFacture, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$ligneFacture->getId(), $request->request->get('_token'))) {
            $entityManager->remove($ligneFacture);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_ligne_facture_index', [], Response::HTTP_SEE_OTHER);
    }
}

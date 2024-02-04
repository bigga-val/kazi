<?php

namespace App\Controller;

use App\Entity\BonProduit;
use App\Entity\Facture;
use App\Form\BonProduitType;
use App\Repository\BonProduitRepository;
use App\Repository\ClientRepository;
use App\Repository\FactureRepository;
use App\Repository\LigneBonProduitRepository;
use App\Repository\LigneFactureRepository;
use App\Repository\TypeFactureRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPMailer\PHPMailer\PHPMailer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\FPdfGenerator;
use App\Service\PdfService;

#[Route('/bon/produit')]
class BonProduitController extends AbstractController
{
    #[Route('/home', name: 'app_bon_produit_index', methods: ['GET'])]
    public function index(BonProduitRepository $bonProduitRepository): Response
    {
        return $this->render('bon_produit/index.html.twig', [
            'bon_produits' => $bonProduitRepository->findAll(),
        ]);
    }


    #[Route('/jsoncreerbon', name: 'app_bon_produit_index2', methods: ['GET'])]
    public function index2(BonProduitRepository $bonProduitRepository,
                        EntityManagerInterface $entityManager,
                        FactureRepository $factureRepository,
                        Request $request,
    ): Response
    {
        $bon = new BonProduit();
        $facture = $factureRepository->find($request->query->get('factureID'));
        try {
            $bon->setFacture($facture);
            $bon->setCreatedAt(new \DateTimeImmutable());
            $bon->setCreatedBY($this->getUser()->getUserIdentifier());
            $bon->setNumero($request->query->get('numeroBon'));
            $entityManager->persist($bon);
            $entityManager->flush();

        } catch (\Exception $exception) {
            return new JsonResponse([
                'response' => $exception->getMessage()
            ]);
        }
//        return $this->render('bon_produit/index.html.twig', [
//            'bon_produits' => $bonProduitRepository->findAll(),
//        ]);
        return new JsonResponse([
            'etat' => true,
            'bonID'=>$bon->getId()
        ]);
    }


    #[Route('/new/{id}', name: 'app_bon_produit_new', methods: ['GET', 'POST'])]
    public function new($id,
                        FactureRepository $factureRepository,
                        LigneFactureRepository $ligneFactureRepository,
                        Request $request,
                        BonProduitRepository $bonProduitRepository,
                        EntityManagerInterface $entityManager): Response
    {
        $count = count($bonProduitRepository->findAll());
        $increment = $count+1;
        $formatted = sprintf('%04d', $increment);
        $facture = $factureRepository->find($id);

        $lignes=$ligneFactureRepository->findBy([
            'facture'=>$facture->getID()
        ]);


        return $this->render('bon_produit/new.html.twig', [
           'facture'=>$facture,
            'lignes'=>$lignes,
            'nextNbre'=>$formatted
        ]);
    }

    #[Route('/service/new/{id}', name: 'app_bon_service_new', methods: ['GET', 'POST'])]
    public function newBonService($id,
                        FactureRepository $factureRepository,
                        LigneFactureRepository $ligneFactureRepository,
                        Request $request,
                        BonProduitRepository $bonProduitRepository,
                        EntityManagerInterface $entityManager): Response
    {
        $count = count($bonProduitRepository->findAll());
        $increment = $count+1;
        $formatted = sprintf('%04d', $increment);
        $facture = $factureRepository->find($id);
        $lignes=$ligneFactureRepository->findBy([
            'facture'=>$facture->getID()
        ]);


        return $this->render('bon_produit/new.html.twig', [
            'facture'=>$facture,
            'lignes'=>$lignes,
            'nextNbre'=>$formatted
        ]);
    }

    #[Route('/{id}', name: 'app_bon_produit_show', methods: ['GET'])]
    public function show(BonProduit $bonProduit,
                         FactureRepository $factureRepository,
                         LigneBonProduitRepository $ligneBonProduitRepository): Response
    {
        $facture = $factureRepository->find($bonProduit->getFacture()->getId());
        $lignesBon = $ligneBonProduitRepository->findBy(['bonProduit'=>$bonProduit->getId()]);
        return $this->render('bon_produit/show.html.twig', [
            'bon_produit' => $bonProduit,
            'facture'=>$facture,
            'lignesBon'=>$lignesBon
        ]);
    }

    #[Route('/{id}/generateBonPdf', name: 'app_generate_bon_pdf', methods: ['GET'])]
    public function generateBonPdfAction(BonProduit $bonProduit,
                                      FPdfGenerator $pdfGenerator,
                                      FactureRepository $factureRepository,
                                      MailerInterface $mailer,
                                      LigneBonProduitRepository $ligneBonProduitRepository ): Response
    {
        $pdfContent = $pdfGenerator->generateBonPdf($bonProduit, $factureRepository, $ligneBonProduitRepository);
        //dd($pdfContent);

        //return $this->redirectToRoute('app_facture_show', ['id'=>$facture->getId()], Response::HTTP_SEE_OTHER);
        return new Response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Attachement'=>false
            //'Content-Disposition' => 'attachment; filename="votre_fichier.pdf"',
        ]);
    }

    #[Route('/{id}/modifier', name: 'app_bon_produit_modifier', methods: ['GET'])]
    public function show2(BonProduit $bonProduit,
                          FactureRepository $factureRepository,
                          LigneFactureRepository $ligneFactureRepository,
                          LigneBonProduitRepository $ligneBonProduitRepository): Response
    {
        $facture = $factureRepository->find($bonProduit->getFacture()->getId());
        $lignesFacture = $ligneFactureRepository->findBy(['facture'=>$facture]);
        $lignesBon = $ligneBonProduitRepository->findBy(['bonProduit'=>$bonProduit->getId()]);
        return $this->render('bon_produit/edit.html.twig', [
            'bon_produit' => $bonProduit,
            'facture'=>$facture,
            'lignesBon'=>$lignesBon,
            'lignesFacture'=>$lignesFacture
        ]);
    }

    #[Route('/{id}/{facture}', name: 'app_bon_produit', methods: ['GET'])]
    public function showProduit(BonProduit $bonProduit,
                                FactureRepository $factureRepository,
                                LigneFactureRepository $ligneFactureRepository): Response
    {
        $facture = $bonProduit->getFacture();
        return $this->render('bon_produit/showProduit.html.twig', [
            'bon_produit' => $bonProduit,
            'facture'=>$facture
        ]);
    }

    #[Route('/jsonSaveBon', name: 'jsonSaveBon', methods: ['GET'])]
    public function jsonSaveBon(Request $request,
                                    EntityManagerInterface $entityManager,
                                    FactureRepository $factureRepository,
                                    ): JsonResponse
    {
        $bon = new BonProduit();
        $facture = $factureRepository->find($request->query->get('factureID'));
        try {
            $bon->setFacture($facture);
            $bon->setCreatedAt(new \DateTimeImmutable());
            $bon->setCreatedBY($this->getUser()->getUserIdentifier());
            $bon->setNumero($request->query->get('numeroBon'));
            $entityManager->persist($bon);
            $entityManager->flush();

        } catch (\Exception $exception) {
            return new JsonResponse([
                'response' => $exception->getMessage()
            ]);
        }
        return new JsonResponse([
            'etat' => true,
            'factID' => $bon->getId()
        ]);
    }



    #[Route('/{id}/editerss', name: 'app_bon_produit_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, BonProduit $bonProduit, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(BonProduitType::class, $bonProduit);
        $form->handleRequest($request);
        return $this->render('bon_produit/edit.html.twig', [
            'bon_produit' => $bonProduit,
            'form' => $form,
        ]);
    }



    #[Route('/{id}', name: 'app_bon_produit_delete', methods: ['POST'])]
    public function delete(Request $request, BonProduit $bonProduit, EntityManagerInterface $entityManager): Response
    {
        $facture = $bonProduit->getFacture()->getId();
        if ($this->isCsrfTokenValid('delete'.$bonProduit->getId(), $request->request->get('_token'))) {
            $entityManager->remove($bonProduit);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_facture_bon', ['id'=>$facture], Response::HTTP_SEE_OTHER);
    }
}

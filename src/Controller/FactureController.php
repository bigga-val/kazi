<?php

namespace App\Controller;

use App\Entity\BonProduit;
use App\Entity\Client;
use App\Entity\Facture;
use App\Form\FactureType;
use App\Repository\BonProduitRepository;
use App\Repository\FactureRepository;
use App\Repository\ClientRepository;
use App\Repository\LigneFactureRepository;
use App\Repository\ProduitRepository;
use App\Repository\ServiceRepository;
use App\Repository\TypeFactureRepository;
use App\Service\PdfService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\FPdfGenerator;


#[Route('/facture')]
class FactureController extends AbstractController
{
    #[Route('/', name: 'app_facture_index', methods: ['GET'])]
    public function index(FactureRepository $factureRepository): Response
    {
        $facturesProduit = $factureRepository->findBy([
            'typeBien'=> 'produit'
        ]);
        return $this->render('facture/index.html.twig', [
            'factures' => $facturesProduit
        ]);
    }

    #[Route('/indexService', name: 'app_facture_index_service', methods: ['GET'])]
    public function indexservice(FactureRepository $factureRepository): Response
    {
        $factures = $factureRepository->findBy([
            'typeBien'=>'service'
        ]);
        return $this->render('facture/indexService.html.twig', [
            'factures' => $factures,
        ]);
    }

    #[Route('/new', name: 'app_facture_new', methods: ['GET', 'POST'])]
    public function new(Request $request, ProduitRepository $produitRepository, ClientRepository $clientRepository, TypeFactureRepository $typeFactureRepository): Response
    {
        $typeFactures = $typeFactureRepository->findAll();
        $clients = $clientRepository->findAll();
        $produits = $produitRepository->findAll();
        return $this->render('facture/new.html.twig', [
            'typeFactures'=>$typeFactures,
            'clients'=>$clients,
            'produits'=>$produits
        ]);
    }

    #[Route('/newFactureService', name: 'app_facture_new_service', methods: ['GET', 'POST'])]
    public function newFactureService(Request $request,
                                      ServiceRepository $serviceRepository,
                                      ClientRepository $clientRepository,
                                      TypeFactureRepository $typeFactureRepository): Response
    {
        $typeFactures = $typeFactureRepository->findAll();
        $clients = $clientRepository->findAll();
        $services = $serviceRepository->findAll();
        return $this->render('facture/newFactureService.html.twig', [
            'typeFactures'=>$typeFactures,
            'clients'=>$clients,
            'services'=>$services
        ]);
    }



    #[Route('/getCountFacture', name: 'getCountFacture', methods: ['GET'])]
    public function getClient(Request $request, FactureRepository $factureRepository)
    {
        $id = $request->query->get("typeID");
        //$factures = $factureRepository->findBy(["type"=>$id]);
        $factures = $factureRepository->findAll();
        $count = count($factures);
        $increment = $count+1;
        $formatted = sprintf('%04d', $increment);
        //$client = $clientRepository->find($id);
        return new JsonResponse([
            'count'=>$formatted
        ]);
    }

    #[Route('/jsonSaveFacture', name: 'jsonSaveFacture', methods: ['GET'])]
    public function jsonSaveFacture(Request $request,
                                    EntityManagerInterface $entityManager,
                                    TypeFactureRepository $typeFactureRepository,
                                    ClientRepository $clientRepository)
    {
        $clientID = $clientRepository->find($request->query->get('client'));
        $typeID = $typeFactureRepository->find($request->query->get('invType'));
        $facture = new Facture();
        try {
            $facture->setClient($clientID);
            $facture->setDateDebut(new \DateTime($request->query->get('startDate')));
            $facture->setDateFin(new \DateTime($request->query->get('endDate')));
            $facture->setDesignation($request->query->get('designation'));
            $facture->setType($typeID);
            $facture->setTypeBien('produit');
            $facture->setNumeroFacture($request->query->get('numeroFacture'));
            $facture->setTax($request->query->get('tax'));
            $facture->setDiscount($request->query->get('discount'));
            $entityManager->persist($facture);
            $entityManager->flush();
        }catch (\Exception $exception){
            return new JsonResponse([
                'response'=>$exception->getMessage()
            ]);
        }
        return new JsonResponse([
            'etat'=>true,
            'factID'=>$facture->getId()
        ]);
    }


    #[Route('/jsonSaveFactureService', name: 'jsonSaveFactureService', methods: ['GET'])]
    public function jsonSaveFactureService(Request $request,
                                    EntityManagerInterface $entityManager,
                                    TypeFactureRepository $typeFactureRepository,
                                    ClientRepository $clientRepository)
    {
        $clientID = $clientRepository->find($request->query->get('client'));
        $typeID = $typeFactureRepository->find($request->query->get('invType'));
        $facture = new Facture();

        try {
            $facture->setClient($clientID);
            $facture->setDateDebut(new \DateTime($request->query->get('startDate')));
            $facture->setDateFin(new \DateTime($request->query->get('endDate')));
            $facture->setDesignation($request->query->get('designation'));
            $facture->setType($typeID);
            $facture->setTypeBien('service');
            $facture->setNumeroFacture($request->query->get('numeroFacture'));
            $facture->setTax($request->query->get('tax'));
            $facture->setDiscount($request->query->get('discount'));

            $entityManager->persist($facture);
            $entityManager->flush();



        }catch (\Exception $exception){
            return new JsonResponse([
                'response'=>$exception->getMessage()
            ]);
        }
        return new JsonResponse([
            'etat'=>true,
            'factID'=>$facture->getId()
        ]);
    }

    #[Route('/jsonEditFacture', name: 'jsonEditFacture', methods: ['GET'])]
    public function jsonEditFacture(Request $request,
                                    FactureRepository $factureRepository,
                                    EntityManagerInterface $entityManager,
                                    TypeFactureRepository $typeFactureRepository,
                                    ClientRepository $clientRepository)
    {
        $facture = $factureRepository->find($request->query->get('factureID'));
        $clientID = $clientRepository->find($request->query->get('client'));
        $typeID = $typeFactureRepository->find($request->query->get('invType'));

        try {
            $facture->setClient($clientID);
            $facture->setDateDebut(new \DateTime($request->query->get('startDate')));
            $facture->setDateFin(new \DateTime($request->query->get('endDate')));
            $facture->setDesignation($request->query->get('designation'));
            $facture->setType($typeID);
            $facture->setTax($request->query->get('tax'));
            $facture->setDiscount($request->query->get('discount'));
            $entityManager->persist($facture);
            $entityManager->flush();
            return new JsonResponse([
                'etat'=>true,
                'factID'=>$facture->getId()
            ]);


        }catch (\Exception $exception){
            return new JsonResponse([
                'response'=>$exception->getMessage()
            ]);
        }

    }


    #[Route('/{id}', name: 'app_facture_show', methods: ['GET'])]
    public function show(Facture $facture, LigneFactureRepository $ligneFactureRepository): Response
    {
        $lignes = $ligneFactureRepository->findBy([
            'facture'=>$facture->getId()
        ]);
        return $this->render('facture/show.html.twig', [
            'facture' => $facture,
            'lignes'=>$lignes
        ]);
    }

    #[Route('/{id}/bons', name: 'app_facture_bon', methods: ['GET'])]
    public function bon(Facture $facture,
                        LigneFactureRepository $ligneFactureRepository,
                        BonProduitRepository $bonProduitRepository): Response
    {
        $bons = $bonProduitRepository->findBy(
            ['facture'=>$facture->getId()]);
        $lignes = $ligneFactureRepository->findBy([
            'facture'=>$facture->getId()
        ]);
        return $this->render('facture/bons.html.twig', [
            'facture' => $facture,
            'lignes'=>$lignes,
            'bons'=>$bons
        ]);
    }

    #[Route('/{id}/bonservice', name: 'app_facture_bon_service', methods: ['GET'])]
    public function bonservice(Facture $facture,
                        LigneFactureRepository $ligneFactureRepository,
                        BonProduitRepository $bonProduitRepository): Response
    {
        $bons = $bonProduitRepository->findBy(
            ['facture'=>$facture->getId()]);
        $lignes = $ligneFactureRepository->findBy([
            'facture'=>$facture->getId()
        ]);
        return $this->render('facture/bons_services.html.twig', [
            'facture' => $facture,
            'lignes'=>$lignes,
            'bons'=>$bons
        ]);
    }

    #[Route('/jsonSaveBon', name: 'jsonSaveBon', methods: ['GET'])]
    public function jsonSaveBon(Request $request,
                                EntityManagerInterface $entityManager,
                                FactureRepository $factureRepository,
    )
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

    #[Route('/{id}/pdf', name: 'app_facture_show_pdf', methods: ['GET'])]
    public function showpdf(Facture $facture,
                            LigneFactureRepository $ligneFactureRepository,
                            PdfService $pdfService): Response
    {
        $lignes = $ligneFactureRepository->findBy([
            'facture'=>$facture->getId()
        ]);
        $html = '<html lang="fr">
            <head>
            <title>Mon document PDF</title>
            <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
            
            <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
            <style>
                .container{
                    display: flex;
                    flex-wrap: wrap;
                }
            </style>
            </head>
                <body>
                    <div style="display: flex; flex-direction: column; justify-content: space-around">
                        <div class="" style="">My Invoice</div>
                        <div class="">
                            <img src="" alt="">
                            <p>text1</p>
                            <p>text2</p>
                            <p>text3</p>
                        </div>
                    </div>
                </body>
            </html>';
//        $html = $this->render('facture/show.html.twig', [
//            'facture' => $facture,
//            'lignes'=>$lignes
//        ]);
    return $pdfService->showPdfFIle($html);

    }


    #[Route('/{id}/generatePdf', name: 'app_generate_pdf', methods: ['GET'])]
    public function generatePdfAction(Facture $facture,
                                      FPdfGenerator $pdfGenerator,
                                      FactureRepository $factureRepository,
                                      MailerInterface $mailer,
                                      LigneFactureRepository $ligneFactureRepository ): Response
    {
        $pdfContent = $pdfGenerator->generatePdf($facture->getId(), $factureRepository, $ligneFactureRepository);
        //dd($pdfContent);

        //return $this->redirectToRoute('app_facture_show', ['id'=>$facture->getId()], Response::HTTP_SEE_OTHER);
        return new Response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Attachement'=>false
            //'Content-Disposition' => 'attachment; filename="votre_fichier.pdf"',
        ]);
    }


    #[Route('/{id}/sendPdfByEmail', name: 'app_send_email', methods: ['GET'])]
    public function sendPdfByEmail(Facture $facture,
                                      FPdfGenerator $pdfGenerator,
                                      FactureRepository $factureRepository,
                                      MailerInterface $mailer,
                                      LigneFactureRepository $ligneFactureRepository ): Response
    {
        $pdfContent = $pdfGenerator->sendInvoiceByEMail($facture->getId(), $factureRepository, $ligneFactureRepository);
        //dd($pdfContent);

        return $this->redirectToRoute('app_facture_show', ['id'=>$facture->getId()], Response::HTTP_SEE_OTHER);

    }


    #[Route('/{id}/approve', name: 'app_facture_approve', methods: ['GET', 'POST'])]
    public function approve(Request $request, Facture $facture,
                         EntityManagerInterface $entityManager)
    {

        $facture->setIsApproved(true);
        $entityManager->persist($facture);
        $entityManager->flush();

        return $this->redirectToRoute('app_facture_show', ['id'=>$facture->getId()], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/disapprove', name: 'app_facture_disapprove', methods: ['GET', 'POST'])]
    public function disapprove(Request $request, Facture $facture,
                            EntityManagerInterface $entityManager)
    {

        $facture->setIsApproved(true);
        $entityManager->persist($facture);
        $entityManager->flush();

        return $this->redirectToRoute('app_facture_show', ['id'=>$facture->getId()], Response::HTTP_SEE_OTHER);
    }


    #[Route('/{id}/edit', name: 'app_facture_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Facture $facture,
                         TypeFactureRepository $typeFactureRepository,
                         ProduitRepository $produitRepository,
                         ClientRepository $clientRepository,
                         LigneFactureRepository $ligneFactureRepository,
                         FactureRepository $factureRepository): Response
    {

        $typeFactures = $typeFactureRepository->findAll();
        //$form = $this->createForm(FactureType::class, $facture);
        //$form->handleRequest($request);
        $produits = $produitRepository->findAll();
        $clients = $clientRepository->findAll();
        $lignes = $ligneFactureRepository->findBy([
            'facture'=>$facture->getId()]);
//        if ($form->isSubmitted() && $form->isValid()) {
//            $factureRepository->save($facture, true);
//
//            return $this->redirectToRoute('app_facture_index', [], Response::HTTP_SEE_OTHER);
//        }

        return $this->renderForm('facture/edit.html.twig', [
            'facture' => $facture,
            'typeFactures' => $typeFactures,
            'produits'=>$produits,
            'clients'=>$clients,
            'lignes'=>$lignes
        ]);
    }

    #[Route('/{id}/editFactureService', name: 'app_facture_edit_service', methods: ['GET', 'POST'])]
    public function editFactureService(Request $request, Facture $facture,
                         TypeFactureRepository $typeFactureRepository,
                         ServiceRepository $serviceRepository,
                         ClientRepository $clientRepository,
                         LigneFactureRepository $ligneFactureRepository,
                         FactureRepository $factureRepository): Response
    {

        $typeFactures = $typeFactureRepository->findAll();
        //$form = $this->createForm(FactureType::class, $facture);
        //$form->handleRequest($request);
        $services = $serviceRepository->findAll();
        $clients = $clientRepository->findAll();
        $lignes = $ligneFactureRepository->findBy([
            'facture'=>$facture->getId()]);
//        if ($form->isSubmitted() && $form->isValid()) {
//            $factureRepository->save($facture, true);
//
//            return $this->redirectToRoute('app_facture_index', [], Response::HTTP_SEE_OTHER);
//        }

        return $this->renderForm('facture/editFactureService.html.twig', [
            'facture' => $facture,
            'typeFactures' => $typeFactures,
            'services'=>$services,
            'clients'=>$clients,
            'lignes'=>$lignes
        ]);
    }

    #[Route('/{id}', name: 'app_facture_delete', methods: ['POST'])]
    public function delete(Request $request, Facture $facture, FactureRepository $factureRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$facture->getId(), $request->request->get('_token'))) {
            $factureRepository->remove($facture, true);
        }

        return $this->redirectToRoute('app_facture_index', [], Response::HTTP_SEE_OTHER);
    }
}

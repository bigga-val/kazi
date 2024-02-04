<?php

namespace App\Controller;

use App\Entity\Client;
use App\Form\ClientType;
use App\Repository\ClientRepository;
use App\Repository\FactureRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

#[Route('/client')]
#[IsGranted("ROLE_ADMIN")]
class ClientController extends AbstractController
{
    #[Route('/', name: 'app_client_index', methods: ['GET'])]
    public function index(ClientRepository $clientRepository): Response
    {
        return $this->render('client/index.html.twig', [
            'clients' => $clientRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_client_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        if($this->isGranted('ROLE_ADMIN') or $this->isGranted('ROLE_GERANT')){
        }else{
            return $this->redirectToRoute('app_user_unauthorized');
        }
        $client = new Client();
        $form = $this->createForm(ClientType::class, $client);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $client->setCreatedBy($this->getUser());
            $client->setCreatedDate(new \DateTime());
            $entityManager->persist($client);
            $entityManager->flush();

            return $this->redirectToRoute('app_client_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('client/new.html.twig', [
            'client' => $client,
            'form' => $form,
        ]);
    }


    #[Route('/{id}', name: 'app_client_show', methods: ['GET'])]
    public function show(Client $client, FactureRepository $factureRepository): Response
    {
        $FacturesServices = $factureRepository->findBy(["typeBien"=>'service', "client"=>$client->getId()]);
        $FacturesProduits = $factureRepository->findBy(["typeBien"=>'produit', "client"=>$client->getId()]);

        return $this->render('client/show.html.twig', [
            'client' => $client,
            'FacturesServices'=>$FacturesServices,
            'FacturesProduits'=>$FacturesProduits
        ]);
    }

    #[Route('/{id}/edit', name: 'app_client_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Client $client, EntityManagerInterface $entityManager): Response
    {
        if($this->isGranted('ROLE_ADMIN') or $this->isGranted('ROLE_GERANT')){
        }else{
            return $this->redirectToRoute('app_user_unauthorized');
        }
        $form = $this->createForm(ClientType::class, $client);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_client_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('client/edit.html.twig', [
            'client' => $client,
            'form' => $form,
        ]);
    }

    #[Route('/getClientFacture/{id}', name: 'getClientFacture', methods: ['GET'])]
    public function getClientFacture(Client $client, Request $request,
                                     FactureRepository $factureRepository,
                                     ClientRepository $clientRepository)
    {
        $clientID = $request->request->get("id");
        $factures = $factureRepository->findBy([
            'client'=>$client->getId()
        ]);
        //$client = $clientRepository->find($id);
        return $this->render('client/mesfactures.html.twig', [
            'factures' => $factures,
            'client'=>$client
        ]);
    }

    #[Route('/getClient/{id}', name: 'getClient', methods: ['GET'])]
    public function getClient(Client $client, Request $request, ClientRepository $clientRepository)
    {
        $clientID = $request->request->get("id");
        //$client = $clientRepository->find($id);
        return new JsonResponse([
            'clientEmail' => $client->getEmail(),
            'clientTelephone'=>$client->getTelephone(),
            'clientAdresse'=>$client->getAdressephysique()
        ]);
    }


    #[Route('/{id}', name: 'app_client_delete', methods: ['POST'])]
    public function delete(Request $request, Client $client, EntityManagerInterface $entityManager): Response
    {
        if($this->isGranted('ROLE_ADMIN') or $this->isGranted('ROLE_GERANT')){
        }else{
            return $this->redirectToRoute('app_user_unauthorized');
        }
        if ($this->isCsrfTokenValid('delete'.$client->getId(), $request->request->get('_token'))) {
            $entityManager->remove($client);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_client_index', [], Response::HTTP_SEE_OTHER);
    }
}

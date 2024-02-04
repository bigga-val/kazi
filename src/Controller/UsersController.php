<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;
use App\Form\UserType;
use function PHPUnit\Framework\isEmpty;
use function PHPUnit\Framework\isNull;

class UsersController extends AbstractController
{
    public function users_profile(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        return $this->render('users/profile.html.twig');
    }
   
    public function users_account_settings(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        return $this->render('users/user-account-settings.html.twig');
    }

    #[Route('/list_users', name: 'app_user_index', methods: ['GET', 'POST'])]
    public function list_users(UserRepository $userRepository):Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        return $this->render("users/list_users.html.twig",[
            "users"=>$userRepository->findAll()
        ]);
    }
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[Route('/profile_user/{id}', name: 'app_user_profile', methods: ['GET', 'POST'])]
    public function profile_user($id, Request $request,EntityManagerInterface $entityManager, UserRepository $userRepository):Response
    {

        $user = $userRepository->find($id);
        $currentID = $this->getUser()->getId();
        if (count($request->query->all()) == 1) {
            $role = $request->query->all()["role"];
            $user->setRoles([$role]);
            $entityManager->persist($user);
            $entityManager->flush();
            if($user->getId() == $currentID){
                return $this->redirectToRoute('app_logout');
            }
        }
        return $this->render("users/profile_user.html.twig",[
            "user"=>$user
        ]);
    }

    #[Route('/saveChangedRole/{id}', name: 'saveChangedRole', methods: ['GET', 'POST'])]
    public function saveChangedRole($id, User $user, Request $request,EntityManagerInterface $entityManager, UserRepository $userRepository):Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');


            //dd($request->query->all()['role']);

        //}
        return $this->render("users/profile_user.html.twig",[
            "user"=>$user
        ]);
    }



    #[Route('/new_user', name: 'app_user_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager,
                        UserPasswordHasherInterface $userPasswordHasher): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            //$user->setCreatedBy($this->getUser());
            //$client->setCreatedDate(new \DateTime());
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user, 123456)
            );
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('users/new.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/unauthorized', name: 'app_user_unauthorized', methods: ['GET', 'POST'])]
    public function unauthaurized(): Response
    {


        return $this->render('users/unauthorized.html.twig', [

        ]);
    }

    #[Route('/{id}/edit_user', name: 'app_user_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, User $user, UserRepository $userRepository, EntityManagerInterface $entityManager): Response
    {

//        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('app_user_profile', [
                "id"=>$user->getId()
            ], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('users/edit_user.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }


    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[Route('/{id}/reset_password', name: 'app_reset_password', methods: ['GET', 'POST'])]
    public function reset_password(Request $request, $id, EntityManagerInterface $entityManager, UserPasswordHasherInterface $userPasswordHasher):Response
    {
        //$this->denyAccessUnlessGranted('ROLE_ADMIN');

        $data = $request->request->all();
        $user = $entityManager->getRepository(User::class)->find($id);
        if ($request->getMethod() == "POST") {
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user, $data["nouveau"])
            );
            $entityManager->flush();
            return $this->redirectToRoute('app_user_show', ['id'=>$user->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('users/reset_pwd.html.twig', [

        ]);
    }
}

<?php

namespace App\Controller;

use App\Entity\Users;
use App\Form\UsersType;
use App\Repository\UsersRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

/**
 * @Route("/users")
 */
class UsersController extends AbstractController
{
    /**
     * @Route("/", name="users_index", methods="GET")
     */
    public function index(UsersRepository $usersRepository): Response
    {
      return $this->render('users/index.html.twig', ['users' => $usersRepository->findAll()]);
    }

    /**
     * @Route("/new", name="users_new", methods="GET|POST")
     */
    public function new(Request $request): Response
    {
      if (!isset($_SESSION['user'])&& !isset($_SESSION['role'])){
      $user = new Users();
      $form = $this->createFormBuilder($user)
      ->add('username', TextType::class)
      ->add('password', TextType::class)
      ->add('register', SubmitType::class)
      ->getForm();
      $user->setRole('user');
      $form->handleRequest($request);

      if ($form->isSubmitted() && $form->isValid()) {
        $user = $form->getData();
        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();

        return $this->redirectToRoute('users_index');
      }

      return $this->render('users/new.html.twig', [
        'user' => $user,
        'form' => $form->createView(),
      ]);
    }
    return $this->redirectToRoute('posts_index');
    }

    /**
     * @Route("/login", name="users_login", methods={"GET","POST"})
     */
    public function login()
    {
      if(isset($_POST['login'])){
       $user = $this->getDoctrine()
       ->getRepository(Users::class)
       ->findby(['username' => $_POST['login']]);
         if($user[0]->getUsername() == $_POST['login'] && $user[0]->getPassword() == $_POST['password']){
          if(isset($_SESSION['user'])&&$_SESSION['user']!=null){
          session_destroy();
        }
          session_start();
          $_SESSION['user']=$user[0]->getUsername();
          $_SESSION['role']=$user[0]->getRole();
          return $this->redirectToRoute('posts_index'); 
        }else {
          $this->addFlash('error','user or password incorrect');
          return $this->redirectToRoute('users_login');
        }

          
    }else 
    {
      return $this->render('users/login.html.twig');
    }
  }
    /**
     * @Route("/logout", name="users_logout", methods={"GET","POST"})
     */
    public function logout()
    {
      if (isset($_SESSION['user'])) {
        session_destroy();
        return $this->redirectToRoute('posts_index');
      }
      return $this->redirectToRoute('posts_index');
    }
    /**
     * @Route("/{id}/edit", name="users_edit", methods="GET|POST")
     */
    public function edit(Request $request, Users $user): Response
    {
       if(isset($_SESSION['user'])&& isset($_SESSION['role'])){
            if($user->getUsername() == $_SESSION['user'] || $_SESSION['role'] == 'admin'){
      $form = $this->createForm(UsersType::class, $user);
      $form->handleRequest($request);

      if ($form->isSubmitted() && $form->isValid()) {
        $this->getDoctrine()->getManager()->flush();

        return $this->redirectToRoute('users_edit', ['id' => $user->getId()]);
      }

      return $this->render('users/edit.html.twig', [
        'user' => $user,
        'form' => $form->createView(),
      ]);
       }
     }
     return $this->redirectToRoute('posts_index');
    }

    /**
     * @Route("/{id}", name="users_delete", methods="DELETE")
     */
    public function delete(Request $request, Users $user): Response
    {
     return $this->redirectToRoute('posts_index');
   }
  }

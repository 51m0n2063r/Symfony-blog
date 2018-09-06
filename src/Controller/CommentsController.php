<?php
namespace App\Controller;

use App\Entity\Posts;
use App\Entity\Comments;
use App\Form\CommentsType;
use App\Repository\CommentsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

/**
 * @Route("/comments")
 */
class CommentsController extends AbstractController
{
    /**
     * @Route("/", name="comments_index", methods="GET")
     */
    public function index(CommentsRepository $commentsRepository): Response
    {
        return $this->render('comments/index.html.twig', ['comments' => $commentsRepository->findAll()]);
    }
    /**
     * @Route("/{id}/new", name="comments_new", methods="GET|POST")
     */

    public function new(Request $request, Posts $post): Response
    {
        if(isset($_SESSION['user'])&& isset($_SESSION['role'])){
            $comment = new Comments();
            $form = $this->createFormBuilder($comment)
            ->add('content', TextareaType::class)
            ->add('Submit comment', SubmitType::class)
            ->getForm();
            $comment->setAuthor($_SESSION["user"]);
            $comment->setCreatedAt(new \DateTime('now'));
            $comment->setPost($post);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $comment=$form->getData();
                $em = $this->getDoctrine()->getManager();
                $em->persist($comment);
                $em->flush();

                return $this->redirectToRoute('posts_index');
            }

            return $this->render('comments/new.html.twig', [
                'comment' => $comment,
                'form' => $form->createView(),
            ]);
        }
        return $this->redirectToRoute('posts_index');
    }

    /**
     * @Route("/{id}", name="comments_show", methods="GET")
     */
    public function show(Comments $comment): Response
    {
        if(isset($_SESSION['user'])&& isset($_SESSION['role'])){
            if($comment->getAuthor() == $_SESSION['user'] || $_SESSION['role'] == 'admin'){
            return $this->render('comments/show.html.twig', ['comment' => $comment,'token' => 'A']);
            }
        }

        return $this->render('comments/show.html.twig', ['comment' => $comment]);
    }

    /**
     * @Route("/{id}/edit", name="comments_edit", methods="GET|POST")
     */
    public function edit(Request $request, Comments $comment): Response
    {
       return $this->redirectToRoute('posts_index');
    }

    /**
     * @Route("/{id}", name="comments_delete", methods="DELETE")
     */
    public function delete(Request $request, Comments $comment): Response
    {
        if(isset($_SESSION['user'])&& isset($_SESSION['role'])){
            if($comment->getAuthor() == $_SESSION['user'] || $_SESSION['role'] == 'admin'){
        if ($this->isCsrfTokenValid('delete'.$comment->getId(), $request->request->get('_token'))) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($comment);
            $em->flush();
        }

        return $this->redirectToRoute('comments_index');
    }
}
 return $this->redirectToRoute('posts_index');
}
}

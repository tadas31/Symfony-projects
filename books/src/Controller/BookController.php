<?php

namespace App\Controller;

use App\Entity\Book;
use App\Form\BookType;
use App\Repository\BookRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class BookController extends AbstractController
{
    /**
     * Gets all books and displays them.
     * @Route("/", name="home")
     */
    public function index(BookRepository $bookRepository)
    {
        $books = $bookRepository->findAll();

        return $this->render('book/index.html.twig', [
            'books' => $books
        ]);
    }

    /**
     * Gets specific book and displays it.
     * @Route("/show/{id}", name="show")
     */
    public function show(Book $book) {
        return $this->render('book/book.html.twig', [
            'book' => $book
        ]);
    }

    /**
     * Displays form to create book and saves them when submitted.
     * @Route("/create", name="create")
     */
    public function create(Request $request)
    {
        $book = new Book();

        $form = $this->createForm(BookType::class, $book);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            $em->persist($book);
            $em->flush();

            return $this->redirect($this->generateUrl('home'));
        }


        return $this->render('book/create.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * Updates selected book.
     * @Route("/update/{id}", name="update")
     */
    public function update(Book $book, Request $request) {
        $form = $this->createForm(BookType::class, $book);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($book);
            $em->flush();

            return $this->redirect($this->generateUrl('home'));
        }


        return $this->render('book/update.html.twig', [
            'book' => $book,
            'form' => $form->createView()
        ]);
    }

    /**
     * Deletes selected book.
     * @Route("/delete/{id}", name="delete", methods={"DELETE"})
     */
    public function remove(Book $book) {
        $em = $this->getDoctrine()->getManager();

        $em->remove($book);
        $em->flush();

        return $this->redirect($this->generateUrl('home'));
    }
}

<?php

namespace App\Controller;

use App\Entity\Coffee;
use App\Form\CoffeeType;
use App\Repository\CoffeeRepository;
use App\Service\MaxProductChecker;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("admin/coffee")
 */
class CoffeeController extends AbstractController
{
    /**
     * @Route("/", name="coffee_index", methods="GET")
     */
    public function index(CoffeeRepository $coffeeRepository): Response
    {
        return $this->render(
            'admin/coffee/index.html.twig',
            ['coffees' => $coffeeRepository->findBy([], ['country'=>'ASC'])]
        );
    }

    /**
     * @Route("/{id}/novelty", name="coffee_novelty", methods="GET|POST"))
     */
    public function updateNovelty(Coffee $coffee, MaxProductChecker $maxProductChecker): Response
    {
        if ($maxProductChecker->checkNoveltyNumber() || $coffee->getNovelty()) {
            $coffee->setNovelty(!$coffee->getNovelty());
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('success', 'Modification enregistrée');
        } else {
            $this->addFlash('danger', 'Impossible d\'ajouter plus de ' . MaxProductChecker::MAX . ' nouveautés');
        }

        return $this->redirectToRoute('coffee_index');
    }

    /**
     * @Route("/{id}/highlighted", name="coffee_highlighted", methods="GET|POST"))
     */
    public function updateHighlighted(Coffee $coffee, MaxProductChecker $maxProductChecker): Response
    {
        if ($maxProductChecker->checkHighlightedNumber() || $coffee->getHighlighted()) {
            $coffee->setHighlighted(!$coffee->getHighlighted());
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('success', 'Modification enregistrée');
        } else {
            $this->addFlash('danger', 'Impossible d\'ajouter plus de ' . MaxProductChecker::MAX . ' produits du mois');
        }

        return $this->redirectToRoute('coffee_index');
    }

    /**
     * @Route("/new", name="coffee_new", methods="GET|POST")
     */
    public function new(Request $request): Response
    {
        $coffee = new Coffee();
        $form = $this->createForm(CoffeeType::class, $coffee);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $coffee->setUpdatedAt();
            $em->persist($coffee);
            $em->flush();

            return $this->redirectToRoute('coffee_index');
        }

        return $this->render('admin/coffee/new.html.twig', [
            'coffee' => $coffee,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="coffee_show", methods="GET")
     */
    public function show(Coffee $coffee): Response
    {

        return $this->render('admin/coffee/show.html.twig', [
            'coffee' => $coffee
        ]);
    }

    /**
     * @Route("/{id}/edit", name="coffee_edit", methods="GET|POST")
     */
    public function edit(Request $request, Coffee $coffee): Response
    {
        $form = $this->createForm(CoffeeType::class, $coffee);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('coffee_index', ['id' => $coffee->getId()]);
        }

        return $this->render('admin/coffee/edit.html.twig', [
            'coffee' => $coffee,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="coffee_delete", methods="DELETE")
     */
    public function delete(Request $request, Coffee $coffee): Response
    {
        if ($this->isCsrfTokenValid('delete'.$coffee->getId(), $request->request->get('_token'))) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($coffee);
            $em->flush();
        }

        return $this->redirectToRoute('coffee_index');
    }
}

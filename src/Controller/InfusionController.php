<?php

namespace App\Controller;

use App\Entity\Infusion;
use App\Form\InfusionType;
use App\Repository\InfusionRepository;
use App\Service\MaxProductChecker;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("admin/infusion")
 */
class InfusionController extends AbstractController
{
    /**
     * @Route("/", name="infusion_index", methods="GET")
     */
    public function index(InfusionRepository $infusionRepository): Response
    {
        return $this->render(
            'admin/infusion/index.html.twig',
            ['infusions' => $infusionRepository->findBy([], ['category'=>'ASC'])]
        );
    }

    /**
     * @Route("/{id}/novelty", name="infusion_novelty", methods="GET|POST"))
     */
    public function updateNovelty(Infusion $infusion, MaxProductChecker $maxProductChecker): Response
    {
        if ($maxProductChecker->checkNoveltyNumber() || $infusion->getNovelty()) {
            $infusion->setNovelty(!$infusion->getNovelty());
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('success', 'Modification enregistrée');
        } else {
            $this->addFlash('danger', 'Impossible d\'ajouter plus de ' . MaxProductChecker::MAX . ' nouveautés');
        }

        return $this->redirectToRoute('infusion_index');
    }

    /**
     * @Route("/{id}/highlighted", name="infusion_highlighted", methods="GET|POST"))
     */
    public function updateHighlighted(Infusion $tea, MaxProductChecker $maxProductChecker): Response
    {
        if ($maxProductChecker->checkHighlightedNumber() || $tea->getHighlighted()) {
            $tea->setHighlighted(!$tea->getHighlighted());
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('success', 'Modification enregistrée');
        } else {
            $this->addFlash('danger', 'Impossible d\'ajouter plus de ' . MaxProductChecker::MAX . ' produits du mois');
        }


        return $this->redirectToRoute('infusion_index');
    }

    /**
     * @Route("/new", name="infusion_new", methods="GET|POST")
     */
    public function new(Request $request): Response
    {
        $infusion = new Infusion();
        $form = $this->createForm(InfusionType::class, $infusion);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($infusion);
            $em->flush();
            $this->addFlash('success', 'L\'infusion à bien été créé');

            return $this->redirectToRoute('infusion_index');
        }

        return $this->render('admin/infusion/new.html.twig', [
            'infusion' => $infusion,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="infusion_show", methods="GET")
     */
    public function show(Infusion $infusion): Response
    {
        return $this->render('admin/infusion/show.html.twig', ['infusion' => $infusion]);
    }

    /**
     * @Route("/{id}/edit", name="infusion_edit", methods="GET|POST")
     */
    public function edit(Request $request, Infusion $infusion): Response
    {
        $form = $this->createForm(InfusionType::class, $infusion);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('success', 'L\'infusion a bien été éditée');

            return $this->redirectToRoute('infusion_index', ['id' => $infusion->getId()]);
        }

        return $this->render('admin/infusion/edit.html.twig', [
            'infusion' => $infusion,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="infusion_delete", methods="DELETE")
     */
    public function delete(Request $request, Infusion $infusion): Response
    {
        if ($this->isCsrfTokenValid('delete'.$infusion->getId(), $request->request->get('_token'))) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($infusion);
            $em->flush();
            $this->addFlash('success', 'L\'infusion a bien été supprimée');
        }

        return $this->redirectToRoute('infusion_index');
    }
}

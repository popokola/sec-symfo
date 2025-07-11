<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\TagRepository;
use App\Form\TagForm;
use App\Entity\Tag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/dashboard')]
#[IsGranted('ROLE_ADMIN', message: 'You are not allowed to perform this action.')]
final class TagController extends AbstractController
{
    #[Route('/tag', name: 'app_tag')]
    public function index(TagRepository $tagRepository): Response
    {
        $tags = $tagRepository->findAll();
        return $this->render('tag/index.html.twig', [
            'tags' => $tags,
        ]);

    }

    #[Route('/tag/add', name: 'app_tag_add')]
    public function addTag(Request $request, TagRepository $tagRepository): Response
    {
        $tag = new Tag();   
        $form = $this->createForm(TagForm::class, $tag);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $tagRepository->save($tag, true);
            $this->addFlash('success', 'Tag added!');
            return $this->redirectToRoute('app_tag');
        }

        return $this->render('tag/add.html.twig', [
            'tagForm' => $form
        ]);

    }

    #[Route('/tag/{id}/edit', name: 'app_tag_edit')]
    public function editTag(Tag $tag, TagRepository $tagRepository, Request $request): Response
    {
        $form = $this->createForm(TagForm::class, $tag);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $tagRepository->save($tag, true);
            $this->addFlash('success', 'Tag updated!');
            return $this->redirectToRoute('app_tag');
        }

        return $this->render('tag/edit.html.twig', [
            'tagForm' => $form
        ]);
    }

    #[Route('/tag/{id}/approuved', name: 'app_tag_approuval')]
    public function approuvedTag(Tag $tag, TagRepository $tagRepository): Response
    {
        if ($tag->isActive() == true) {
            $tag->setActive(false);
            $tagRepository->save($tag, true);
            $this->addFlash('warning', 'Tag unaprouved!');
            return $this->redirectToRoute('app_tag');
        }

        $tag->setActive(true);
        $tagRepository->save($tag, true);
        $this->addFlash('success', 'Tag approuved!');
        return $this->redirectToRoute('app_tag');
    }

    #[Route('/tag/{id}/delete', name: 'app_tag_delete')]
    public function deleteTag(Tag $tag, TagRepository $tagRepository): Response
    {
        $tagRepository->remove($tag, true);
        $this->addFlash('success', 'Tag deleted!');
        return $this->redirectToRoute('app_tag');

    }
}

<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use App\Form\TestType;
use App\Sqs\Publisher;

class TestController extends AbstractController
{
    private Publisher $publisher;

    public function __construct(Publisher $publisher)
    {
        $this->publisher = $publisher;
    }

    #[Route('/test', name: 'test', methods: ['GET', 'POST'])]
    public function index(Request $request): Response
    {
        $form = $this->createForm(TestType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $messageId = $this->publisher->publish($form->getData()['message']);

            return $this->redirectToRoute('test');
        }

        return $this->render('test/hello.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}

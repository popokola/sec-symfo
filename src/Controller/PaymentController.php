<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\PaymentRepository;
use App\Form\PaymentForm;
use App\Entity\Payment;
use Symfony\Component\HttpFoundation\Request;

#[Route('/dashboard')]
final class PaymentController extends AbstractController
{
    #[Route('/payment', name: 'app_payment')]
    public function index(PaymentRepository $paymentRepository): Response
    {
        #check the current user role if it is admin find all payments otherwise find only the payments of the current user
        $user = $this->getUser();
        if ($this->isGranted('ROLE_ADMIN')) {
            $payments = $paymentRepository->findAll();
        } else {
            $payments = $paymentRepository->findBy(['member' => $user]);
        }
        return $this->render('payment/index.html.twig', [
            'payments' => $payments,
        ]);
    }

    #[Route('/payment/add', name: 'app_payment_add')]
    public function addPayment(Request $request, PaymentRepository $paymentRepository): Response
    {
        $payment = new Payment();

        $form = $this->createForm(PaymentForm::class, $payment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $paymentRepository->save($payment, true);
            $this->addFlash('success', 'payement added!');
            return $this->redirectToRoute('app_payment');
        }

        return $this->render('payment/add.html.twig', [
            'paymentForm' => $form
        ]);

    }

}

<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Transaction;
use Beelab\PaypalBundle\Paypal\Exception;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

class PaypalController extends Controller
{
    public function indexAction(){
        return $this->render('AppBundle:Paypal:index.html.twig');
    }


    public function paymentAction(Request $request, $offers)
    {
          switch ($offers){
              case 1:
                  $amount = 1.00;
                  break;
              case 2:
                  $amount = 20.00;
                  break;
              case 3:
                  $amount = 50.00;
                  break;
              default:
                  $amount = 0.00;
          }


        $transaction = new Transaction($amount);

        try {
            $response = $this->get('beelab_paypal.service')->setTransaction($transaction)->start();
            $this->getDoctrine()->getManager()->persist($transaction);
            $this->getDoctrine()->getManager()->flush();

            return $this->redirect($response->getRedirectUrl());
        } catch (Exception $e) {
            throw new HttpException(503, 'Payment error', $e);
        }
    }

    /**
     * The route configured in "cancel_route" (see above) should point here
     *
     */
    public function canceledPaymentAction(Request $request)
    {
        $token = $request->query->get('token');

        /**
         * @var Transaction $transaction
         */
        $transaction = $this->getDoctrine()->getRepository('AppBundle:Transaction')->findOneByToken($token);
        if (is_null($transaction)) {
            throw $this->createNotFoundException(sprintf('Transaction with token %s not found.', $token));
        }
        $transaction->cancel(null);


        $this->getDoctrine()->getManager()->flush();

        return $this->render('AppBundle:Paypal:canceled.html.twig');

    }

    /**
     * The route configured in "return_route" (see above) should point here
     */
    public function completedPaymentAction(Request $request)
    {
        $tokenStorage = $this->get('security.token_storage');
        $user = $tokenStorage->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();

        $token = $request->query->get('token');
        /**
         * @var Transaction $transaction
         */
        $transaction = $this->getDoctrine()->getRepository('AppBundle:Transaction')->findOneByToken($token);


        if($transaction->getStatus()){
            $this->addFlash("warning", "Erreur lors de l'ajout de solde");
            return $this->redirectToRoute("app_paypal_paiement");
        }

        if (is_null($transaction)) {
            throw $this->createNotFoundException(sprintf('Transaction with token %s not found.', $token));
        }
        $this->get('beelab_paypal.service')->setTransaction($transaction)->complete();
        $this->getDoctrine()->getManager()->flush();

        //on récupère le montant payer et on ajoute en conséquence
        switch ($transaction->getAmount()){
            case 1.00:
                $duration = 1;
                break;
            case 20.00:
                $duration = 24;
                break;
            case 50.00:
                $duration = 168;
                break;
            default:
                $duration = 0;
                break;
        }
        $duration = $duration * 3600;
        if($transaction->getStatus())
        $user->addTranscodetime($duration);

        $em->persist($user);
        $em->flush();



      return  $this->render('AppBundle:Paypal:completed.html.twig');
    }
}

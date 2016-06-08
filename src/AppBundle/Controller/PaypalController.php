<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Transaction;
use Beelab\PaypalBundle\Paypal\Exception;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

class PaypalController extends Controller
{
    public function indexAction(){
        return $this->render('AppBundle:Paypal:index.html.twig');
    }


    public function paymentAction(Request $request, $offers)
    {
        $amount = 0.00;
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

        $token = $request->query->get('token');
        /**
         * @var Transaction $transaction
         */
        $transaction = $this->getDoctrine()->getRepository('AppBundle:Transaction')->findOneByToken($token);
        if (is_null($transaction)) {
            throw $this->createNotFoundException(sprintf('Transaction with token %s not found.', $token));
        }
        $this->get('beelab_paypal.service')->setTransaction($transaction)->complete();


        $this->getDoctrine()->getManager()->flush();

        if (!$transaction->isOk()) {
            dump($transaction);
            die();
            return $this->render('AppBundle:Paypal:canceled.html.twig');
        }

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
        dump($duration);

        $user->addTranscodetime($duration);
        $this->getDoctrine()->getManager()->flush();







      return  $this->render('AppBundle:Paypal:completed.html.twig');
    }
}

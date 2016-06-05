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

    public function paymentAction(Request $request)
    {
        
        $amount = 1;  // get an amount, e.g. from your cart
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
     */
    public function canceledPaymentAction(Request $request)
    {
        $token = $request->query->get('token');
        $transaction = $this->getDoctrine()->getRepository('AppBundle:Transaction')->findOneByToken($token);
        if (is_null($transaction)) {
            throw $this->createNotFoundException(sprintf('Transaction with token %s not found.', $token));
        }
        $transaction->cancel(null);
        $this->getDoctrine()->getManager()->flush();

        return array(); // or a Response...
    }

    /**
     * The route configured in "return_route" (see above) should point here
     */
    public function completedPaymentAction(Request $request)
    {
        $token = $request->query->get('token');
        $transaction = $this->getDoctrine()->getRepository('AppBundle:Transaction')->findOneByToken($token);
        if (is_null($transaction)) {
            throw $this->createNotFoundException(sprintf('Transaction with token %s not found.', $token));
        }
        $this->get('beelab_paypal.service')->setTransaction($transaction)->complete();
        $this->getDoctrine()->getManager()->flush();
        if (!$transaction->isOk()) {
            return array(); // or a Response (in case of error)
        }

        return array(); // or a Response (in case of success)
    }
}

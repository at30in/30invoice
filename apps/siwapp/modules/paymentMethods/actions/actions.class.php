<?php

/**
 * paymentMethods actions.
 *
 * @package    siwapp
 * @subpackage paymentMethods
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class paymentMethodsActions extends sfActions
{
 /**
  * Executes index action
  *
  * @param sfRequest $request A request object
  */
  public function executeForFormSelect(sfWebRequest $request)
  {
    $payment = Doctrine::getTable('PaymentMethods')->find($request->getParameter('payment_id'));
    if($payment)
    {
      $this->getResponse()->setHttpHeader('Content-Type', 'application/json; charset=utf-8');
      return $this->renderText(json_encode(array('success' => true, 'value' => $payment->getValue())));  
    }
    return $this->renderText(json_encode(array('success' => false)));  
    
  }
}

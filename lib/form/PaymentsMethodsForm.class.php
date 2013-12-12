<?php

class PaymentsMethodsForm extends FormsContainer
{
  public function __construct($options = array(), $CSRFSecret = null)
  {
    $this->old_payments = Doctrine::getTable('PaymentMethods')->findAll();
      
    $forms = array();
    foreach ($this->old_payments as $payment)
    {
      $forms['old_'.$payment->getId()] = new PaymentMethodsForm($payment, $options, false);
    }
    parent::__construct($forms, 'PaymentMethodsForm', $options, $CSRFSecret);
  }
  
  public function configure()
  {
    $this->widgetSchema->setNameFormat('payments[%s]');
  }
  
}

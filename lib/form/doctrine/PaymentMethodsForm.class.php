<?php

/**
 * PaymentMethods form.
 *
 * @package    siwapp
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class PaymentMethodsForm extends BasePaymentMethodsForm
{
  public function configure()
  {
    unset($this['items_list']);
    $this->widgetSchema['value'] = new sfWidgetFormInputText(array(), array('class'=>'value','size'=>'5'));
    $this->widgetSchema['is_default']->setAttribute('class', 'is_default');
    $this->widgetSchema['name']->setAttribute('class', 'name');
    $this->widgetSchema['active']->setAttribute('class', 'active');
    $this->widgetSchema->setFormFormatterName('Xit');
  }
}

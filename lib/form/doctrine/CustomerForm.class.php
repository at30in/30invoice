<?php

/**
 * Customer form.
 *
 * @package    form
 * @subpackage Customer
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 6174 2007-11-27 06:22:40Z fabien $
 */
class CustomerForm extends BaseCustomerForm
{
  public function configure()
  {
    $i18n = sfContext::getInstance()->getI18N();
    $decorator = new myFormSchemaFormatter($this->getWidgetSchema());
    $this->widgetSchema->addFormFormatter('custom', $decorator);
    $this->widgetSchema->setFormFormatterName('custom');
    $common_defaults = array(
                             'name' => 'Client Name',
                             'identification'=>'Client Legal Id',
                             'contact_person'=> 'Contact Person',
                             'invoicing_address' => 'Invoicing Address',
                             'shipping_address'=> 'Shipping Address',
                             'email'=> 'Client Email Address'
                             );

    $this->widgetSchema['name'] = new sfWidgetFormInputText(
      array('label'       => ''), 
      array('placeholder' => $i18n->__('Client Name'))
      );

    $this->widgetSchema['identification'] = new sfWidgetFormInputText(
      array('label'       => ''), 
      array('placeholder' => $i18n->__('Client Legal Id'))
      );

    $this->widgetSchema['contact_person'] = new sfWidgetFormInputText(
      array('label'       => ''), 
      array('placeholder' => $i18n->__('Contact Person'))
      );

    $this->widgetSchema['invoicing_address'] = new sfWidgetFormTextarea(
      array('label'       => ''), 
      array('placeholder' => $i18n->__('Invoicing Address'))
      );

    $this->widgetSchema['shipping_address'] = new sfWidgetFormTextarea(
      array('label'       => ''), 
      array('placeholder' => $i18n->__('Shipping Address'))
      );

    $this->widgetSchema['email'] = new sfWidgetFormInputText(
      array('label'       => ''), 
      array('placeholder' => $i18n->__('Client Email'))
      );

    $this->widgetSchema->setHelps($common_defaults);

    // validators
    $this->validatorSchema['email'] = new sfValidatorEmail(
                                            array(
                                              'max_length'=>100,
                                              'required'  =>false
                                              ),
                                            array(
                                              'invalid' => 'Invalid email address'
                                              )
                                            );
    $this->validatorSchema['name']->setOption('required', true);
    $this->validatorSchema['name_slug']->
      setMessages(array_merge(array('invalid'=>'sg'),
                              $this->validatorSchema['name_slug']->
                                getMessages()
                              ));
    foreach($this->validatorSchema->getPostValidator()->getValidators() as $val)
    {
      if($val instanceOf sfValidatorDoctrineUnique and 
         $val->getOption('column')==array('name_slug') )
        {
          $val->setMessage(
                           'invalid',
                           'Name too close to one already defined in the db'
                           );
        }

    }
  }

  public function bind(array $taintedValues = null, array $taintedFiles = null)
  {
    $taintedValues['name_slug'] = CustomerTable::slugify(
                                                         $taintedValues['name']
                                                         );
    parent::bind($taintedValues, $taintedFiles);
  }
}
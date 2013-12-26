<?php

/**
 * CustomerRequisition form.
 *
 * @package    siwapp
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class CustomerRequisitionForm extends BaseCustomerRequisitionForm
{
  public function configure()
  {
    //privacy:  
      
    $i18n = sfContext::getInstance()->getI18N();
    $decorator = new myFormSchemaFormatter($this->getWidgetSchema());
    $this->widgetSchema->addFormFormatter('custom', $decorator);
    $this->widgetSchema->setFormFormatterName('custom');
    $common_defaults = array(
                             'name' => 'Client Name',
                             'vat'=>'VAT number',
                             'fiscal_code'=> 'Fiscal Code',
                             'address' => 'Address',
                             'phone'=> 'Client Phone Number',
                             'mobile'=> 'Client Mobile Number',
                             'fax'=> 'Client Fax Number',
                             'email'=> 'Client Email Address',
                             'privacy'=> 'Privacy',
                             'website'=> 'Client Website',
                             'notes'=> 'Client Notes',
                             );

    $this->widgetSchema['name'] = new sfWidgetFormInputText(
      array('label'       => ''), 
      array('placeholder' => $i18n->__('Client Name'))
      );

    $this->widgetSchema['vat'] = new sfWidgetFormInputText(
      array('label'       => ''), 
      array('placeholder' => $i18n->__('Vat Number'))
      );

    $this->widgetSchema['fiscal_code'] = new sfWidgetFormInputText(
      array('label'       => ''), 
      array('placeholder' => $i18n->__('Fiscal Code'))
      );

    $this->widgetSchema['address'] = new sfWidgetFormTextarea(
      array('label'       => ''), 
      array('placeholder' => $i18n->__('Address'))
      );

    $this->widgetSchema['phone'] = new sfWidgetFormInputText(
      array('label'       => ''), 
      array('placeholder' => $i18n->__('Client Phone Number'))
      );

    $this->widgetSchema['mobile'] = new sfWidgetFormInputText(
      array('label'       => ''), 
      array('placeholder' => $i18n->__('Client Mobile Number'))
      );

    $this->widgetSchema['fax'] = new sfWidgetFormInputText(
      array('label'       => ''), 
      array('placeholder' => $i18n->__('Client Fax Number'))
      );

    $this->widgetSchema['email'] = new sfWidgetFormInputText(
      array('label'       => ''), 
      array('placeholder' => $i18n->__('Client Email'))
      );

    $this->widgetSchema['website'] = new sfWidgetFormInputText(
      array('label'       => ''), 
      array('placeholder' => $i18n->__('Client Website'))
      );

    $this->widgetSchema['notes'] = new sfWidgetFormTextarea(
      array('label'       => ''), 
      array('placeholder' => $i18n->__('Client Notes'))
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

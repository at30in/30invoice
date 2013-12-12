<?php

/**
 * Invoice form.
 *
 * @package    form
 * @subpackage Invoice
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 6174 2007-11-27 06:22:40Z fabien $
 */
class InvoiceForm extends CommonForm
{
  protected $number;
  protected $default_series;

  public function configure()
  {
    $this->validatorSchema->setOption('allow_extra_fields', true);
    $this->validatorSchema->setOption('filter_extra_fields', false);
    //unset($this['number'], $this['created_at'], $this['updated_at']);
    unset($this['created_at'], $this['updated_at']);
    // we unset paid_amount so the system don't "nullify" the field on every invoice editing.
    unset($this['paid_amount']); 

    $this->number = $this->getObject()->getNumber();

    $this->widgetSchema['reference'] = new sfWidgetFormInputText();

    $this->widgetSchema['issue_date'] = 
      new sfWidgetFormI18nJQueryDate($this->JQueryDateOptions);
    $this->widgetSchema['due_date']   = 
      new sfWidgetFormI18nJQueryDate($this->JQueryDateOptions);
    $this->widgetSchema['draft']      = new sfWidgetFormInputHidden();
    $this->widgetSchema['closed']->setLabel('Force to be closed');

    $this->widgetSchema['sent_by_email']->setLabel('Sent by email');
    
    $this->default_series = Doctrine::getTable('Series')->find(sfContext::getInstance()->getUser()->getProfile()->getSeries())->value;
    $this->setDefaults(array(
      'issue_date'              => time(),
      'draft'                   => 0,
      'reference'               => $this->default_series.$this->number
      ));
        
    $this->widgetSchema->setNameFormat('invoice[%s]');
    
    parent::configure();
  }

  public function doUpdateObject($values)
  {
    parent::doUpdateObject($values);
    
    $number = intval(str_replace($this->default_series, '', $values['reference']));
    $this->getObject()->setNumber($number);
  }

  public function getModelName()
  {
    return 'Invoice';
  }
  
}
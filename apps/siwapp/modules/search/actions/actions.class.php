<?php

/**
 * search actions.
 *
 * @package    siwapp
 * @subpackage search
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 12479 2008-10-31 10:54:40Z fabien $
 */
class searchActions extends sfActions
{
  /**
   * this ajax function toggles the value of the showTags user's attribute
   * This attribute is for show/hide the layer with all tags in the search form
   *
   * @return void
   * @author Enrique Martinez
   **/
  public function executeToggleTagCloud($request)
  {
    $this->getUser()->toggleTagCloud();
    
    return sfView::NONE;
  }
  
  /**
   * ajax action for customer name autocompletion
   *
   * @return JSON
   * @author Enrique Martinez
   **/
  public function executeAjaxCustomerAutocomplete(sfWebRequest $request)
  {
    $this->getResponse()->setContentType('application/json');
    $q = $request->getParameter('q');
    $items = Doctrine::getTable('Customer')->simpleRetrieveForSelect($request->getParameter('q'),
      $request->getParameter('limit'));

    return $this->renderText(json_encode($items));
  }

  /**
   * ajax action for customer name autocompletion from code
   *
   * @return JSON
   * @author Enrique Martinez
   **/
  public function executeAjaxCustomerFromCodeAutocomplete(sfWebRequest $request)
  {
    $this->getResponse()->setContentType('application/json');
    $q = $request->getParameter('q');
    $items = Doctrine::getTable('Customer')->simpleRetrieveForSelect($request->getParameter('q'),
      $request->getParameter('limit'), 1);

    return $this->renderText(json_encode($items));
  }

  /**
   * ajax action for customer name autocompletion from identification
   *
   * @return JSON
   * @author Enrique Martinez
   **/
  public function executeAjaxCustomerFromIdentificationAutocomplete(sfWebRequest $request)
  {
    $this->getResponse()->setContentType('application/json');
    $q = $request->getParameter('q');
    $items = Doctrine::getTable('Customer')->simpleRetrieveForSelect($request->getParameter('q'),
      $request->getParameter('limit'), 0, 1);

    return $this->renderText(json_encode($items));
  }
}

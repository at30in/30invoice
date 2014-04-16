<?php

/**
 * sfTombolaAssistance configuration.
 * 
 * @package     sfTombolaAssistancePlugin
 * @subpackage  config
 * @author      Andrea Trentin
 * @version     SVN: $Id: PluginConfiguration.class.php 17207 2009-04-10 15:36:26Z Kris.Wallsmith $
 */
class sfTombolaAssistancePluginConfiguration extends sfPluginConfiguration
{
  const VERSION = '1.0.0-DEV';

  /**
   * @see sfPluginConfiguration
   */
  public function initialize()
  {
    $this->dispatcher->connect('routing.load_configuration', array($this, 'listenToRoutingLoadConfigurationEvent'));
  }

  static public function listenToRoutingLoadConfigurationEvent(sfEvent $event)
  {
    $routing = $event->getSubject();

    // aggiunge le regole per le rotte del plugin in cima a quelle esistenti
    $routing->prependRoute('route_tombola', 
      new sfRoute('invoices/new', array('module' => 'invoicesTombola', 'action' => 'new', 'tab' => 'invoices', 'searchForm' => false)
    ));
  }
}
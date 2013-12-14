<?php

class invoiceSetupTask extends sfBaseTask {

  protected function configure()
  {
    $this->namespace        = '30invoice';
    $this->name             = 'setup';
    $this->briefDescription = 'Run all 30invoice setup tasks.';
    $this->detailedDescription = '';
    return;
  }

  protected function execute($arguments = array(), $options = array())
  {
    if(!is_dir('cache'))
    {
      mkdir('cache');
    }
    if(!is_dir('log'))
    {
      mkdir('log');
    }
    if(!is_dir('web/uploads'))
    {
      mkdir('web/uploads');
    }
    chmod('cache', 0777);
    chmod('log', 0777);
    chmod('web/uploads', 0777);

    $this->runTask('doctrine:build',array(),array('all' => true));
    $this->runTask('plugin:publish-assets',array(),array());
    return;
  }
    
}

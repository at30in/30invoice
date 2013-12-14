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

    if(!file_exists('config/databases.yml'))
    {
      copy('config/databases.dist', 'config/databases.yml');
    }

    $response = $this->askConfirmation('Settings database?: (y/n)');
    if($response)
    {
      $db       = $this->ask('Database name: ');
      $user     = $this->ask('Username: ');
      $password = $this->ask('Password: ');
      $this->runTask('configure:database', array('mysql:host=localhost;dbname='.$db, $user, $password));
    }

    $this->runTask('doctrine:build',array(),array('all' => true));
    $this->runTask('plugin:publish-assets',array(),array());
    return;
  }
    
}

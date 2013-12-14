<?php
require_once('config.php');
// check if not installed

//if (!$sw_installed)
if(!file_exists($options['sf_web_dir'].DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR.'installed'))
{
  $redirect = 'http://'.$_SERVER['HTTP_HOST']
    .substr($_SERVER['PHP_SELF'],0,strpos($_SERVER['PHP_SELF'],'index')-1).'/installer.php';
  header("Location: " .$redirect);echo $redirect;
  exit();
}

require_once($options['sf_root_dir'].DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'ProjectConfiguration.class.php');

$configuration = ProjectConfiguration::getApplicationConfiguration('siwapp', 'prod', false);
ProjectConfiguration::getActive()->setWebDir($options['sf_web_dir']);
sfContext::createInstance($configuration)->dispatch();

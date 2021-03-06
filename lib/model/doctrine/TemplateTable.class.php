<?php
/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class TemplateTable extends Doctrine_Table
{
  /**
   * returns the template associated to $model
   *
   * @return Template
   * @author Enrique Martinez
   **/
  public static function getTemplateForModel($model='Invoice')
  {
    $templates = Doctrine::getTable('Template')->createQuery()
      ->where('models LIKE ?', '%'.$model.'%')->limit(1)->execute();
    
    if ($templates->count())
      return $templates[0];
    else 
      throw new TemplateNotFoundException('Template Not Found');
  }
}

class TemplateNotFoundException extends Exception {}
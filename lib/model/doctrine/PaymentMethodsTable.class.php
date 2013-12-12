<?php

/**
 * PaymentMethodsTable
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class PaymentMethodsTable extends Doctrine_Table
{
    /**
     * Returns an instance of this class.
     *
     * @return object PaymentMethodsTable
     */
    public static function getInstance()
    {
        return Doctrine_Core::getTable('PaymentMethods');
    }

    public static function getChoicesForSelect($active_only = true)
    {
      $payments = array();
      $finder = Doctrine::getTable('PaymentMethods')->createQuery();
      
      if ($active_only)
      {
        $finder->where('active = ?', '1');
      }
      
      foreach ($finder->execute() as $s)
      {
        $payments[$s->id] = $s->name;
      }
      
      return $payments;
    }

    public static function getDefault($value = false)
    {
      $finder = Doctrine::getTable('PaymentMethods')->createQuery('p');
      if($value)
      {
        $finder->select('p.value');
      }
      else
      {
        $finder->select('p.id');
      }
      $finder->where('p.is_default = ?', '1');
      
      return $finder->fetchOne(array(), Doctrine_Core::HYDRATE_SINGLE_SCALAR);
    }
}
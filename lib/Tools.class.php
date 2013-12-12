<?php

class Tools
{
  public static function checkDB()
  {
    $configuration =  sfProjectConfiguration::getActive() ;

    $dbm = new sfDatabaseManager($configuration);
    $db = $dbm->getDatabase('doctrine');

    try
    {
      $conn = $db->getConnection();
    }

    catch(sfDatabaseException $ex)
    {        
      return false;
    }
    catch(Doctrine_Connection_Exception $ex2)
    {
      return false;
    }
    return true;
  }

  public static function checkLength($checkMe)
  {
    return strlen($checkMe) > 0 ? $checkMe : false ;
  }
  
  public static function sfDateFromArray($date_array, $lastminute = false)
  {
    $valid = true;
    foreach (array('year', 'month', 'day') as $key)
    {
      $valid = (isset($date_array[$key]) && strlen($date_array[$key])) && $valid;
    }
    
    if (!$valid)
      return false;
    
    $date = sfDate::getInstance()->clearTime();
    
    if ($lastminute)
    {
      $date->setHour(23)->setMinute(59)->setSecond(59);
    }
    
    $date->setDay($date_array['day']);
    $date->setMonth($date_array['month']);
    $date->setYear($date_array['year']);
    
    return $date;
  }

  /**
   * method to deduce a correct or null date value.
   * @param mixed date; if it is an array it must have the 'year', 'month' and 'day' keys.
   * @return mixed date string or whatever passed from outside if not "strictly invalid".
   * @author Carlos Escribano <carlos@markhaus.com>
   */
  public static function filterDate($date = null)
  {
    switch (true)
    {
      case (!$date || !strlen(trim(implode('', (array) $date)))):
      case (is_array($date) && (!isset($date['year']) || !isset($date['month']) || !isset($date['day']))):
        // $date is null or is an array with empty or zero string elements
        // or one of its components is not set (year, month, day)
        return null;
      case (is_array($date)):
        // is an array and is not bad formed
        return $date['year'].'-'.$date['month'].'-'.$date['day'];
      default:
        // is not an array
        return $date;
    }
  }

  public static function getGrossAmount($unitary_cost, $quantity, $discount, $total_taxes_percent)
  {
    $base_amount = $unitary_cost * $quantity;
    $discount_amount = $base_amount * $discount / 100;
    $net_amount = $base_amount - $discount_amount;
    $tax_amount = $net_amount * $total_taxes_percent / 100;
    
    return $net_amount + $tax_amount ;
  }
  
}

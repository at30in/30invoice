<?php
class dataImportTask extends sfBaseTask
{
  public function configure()
  {
    $this->namespace = 'tombola';
    $this->name      = 'import';
  }

  public function execute($arguments = array(), $options = array())
  {
    $manager  = Doctrine_Manager::getInstance();
    $conn     = $manager->connection('mysql://30invoice:30invoice@localhost/30invoice', 'connect');
    $conn1    = $manager->connection('mysql://root:juventina@localhost/prontofattura', 'connect1');

    
    $manager->setCurrentConnection('connect1');
    $result = $conn1->execute("
      SELECT 
      cli.id_cliente AS id,
      cli.cliente_codice AS code, 
      cli.cliente_denominazione AS name, 
      cli.cliente_piva_cf AS identification, 
      cli.cliente_cap AS cap, 
      cli.cliente_indirizzo AS via, 
      T1.comune_nome AS comune, 
      T1.provincia_nome AS provincia, 
      T1.provincia_sigla AS sigla_provincia 
      FROM gest_clienti AS cli 
      LEFT JOIN 
      (SELECT c.*, p.provincia_nome, p.provincia_sigla FROM gest_comuni AS c 
      LEFT JOIN gest_province AS p ON p.id_provincia = c.id_provincia) AS T1 
      ON T1.id_comune = cli.id_comune 
      WHERE cli.cliente_stato = 1 AND cli.cliente_cancellato = 'no'
    ");
    $rows   = $result->fetchAll();

    $manager->setCurrentConnection('connect');

    foreach ($rows as $row)
    {
      
      /*$customer = Doctrine::getTable('Customer')->findOneByName($row['name']);
      if(!$customer)
      {
        $customer = Doctrine::getTable('Customer')->findOneByCode($row['code']);
        if(!$customer)
        {
          $customer                   = new Customer();  
        }
      }*/
      $customer                       = new Customer();  
      $customer->name                 = $row['name'];
      $customer->code                 = $row['code'];
      $customer->identification       = $row['identification'];
      $customer->invoicing_address    = $row['via'] . "\n". $row['cap'] . ' ' . $row['comune'] . ' ('. $row['sigla_provincia'] .')';
      try
      {

        $customer->trySave();
        $customer->refresh();

        $manager->setCurrentConnection('connect1');
        $result = $conn1->execute("
          SELECT 
          fat.fattura_data AS issue_date,
          fat.fattura_scadenza AS due_date,
          fat.fattura_importo AS base_amount,
          fat.fattura_numero AS number,
          fat.fattura_descrizione AS description,
          fat.fattura_cliente_piva AS customer_identification,
          fat.fattura_cliente_denominazione AS customer_name,
          fat.fattura_cliente_indirizzo AS via,
          fat.fattura_cliente_cap AS cap,
          fat.fattura_cliente_citta AS comune,
          fat.fattura_iva AS iva,
          fat.fattura_pagata AS pagato
          FROM gest_fatture fat
          WHERE fat.id_cliente = {$row['id']}
        ");
        $rows1   = $result->fetchAll();

        $manager->setCurrentConnection('connect');
        foreach ($rows1 as $row1)
        {
          $invoice = new Invoice();
          $invoice->customer_id              = $customer->getId();
          $invoice->series_id                = 1;
          $invoice->issue_date               = $row1['issue_date'];
          $invoice->due_date                 = $row1['due_date'];
          $invoice->base_amount              = floatval($row1['base_amount']);
          $invoice->discount_amount          = 0;
          $invoice->net_amount               = floatval($row1['base_amount']);
          $invoice->gross_amount             = floatval($row1['base_amount']) * (1 + (floatval($row1['iva']/100)));
          $invoice->paid_amount              = 0;
          if($row1['pagato'] == 'si')
          {
            $invoice->paid_amount            = $invoice->gross_amount;
          }
          $invoice->tax_amount               = $invoice->gross_amount - $invoice->net_amount;
          $invoice->draft                    = 0;
          $invoice->number                   = $row1['number'];
          $invoice->description              = $row1['description'];
          $invoice->customer_identification  = $row1['customer_identification'];
          $invoice->customer_name            = $row1['customer_name'];

          try
          {
            $invoice->trySave();
            $this->logSection('invoice saved', sprintf('%s', $customer->getId()));
          }
          catch(Exception $e)
          {
            $this->logSection('invoice saved', sprintf('%s', $e));
          }
          unset($invoice);
        }

      }
      catch(Exception $e){}
      unset($customer);

      $this->logSection('customer saved', sprintf('%s', $row['code']));
    }

    /*foreach ($customers as $customer)
    {
      $this->logSection('customer', sprintf('%s', $customer['code']));
    } */   
  }
}
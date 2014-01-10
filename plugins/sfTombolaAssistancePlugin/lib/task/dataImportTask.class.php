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
    //$this->configureIva();
    //$this->configurePaymentMethods();

    //$this->customers();
    //$this->customersForeigners();
    $this->invoices();
    //$this->items();
  }

  private function configurePaymentMethods()
  {
    gc_enable();
    $manager  = Doctrine_Manager::getInstance();
    $manager->setCurrentConnection('connect');

    $methods = array('','Rimessa diretta','Ri.Ba.','Bonifico Bancario','Pagamento già effettuato');

    foreach ($methods as $method)
    {
      $paymentMethods             = Doctrine::getTable('Tax')->findOneByName($method);
      if(!$paymentMethods)
      {
        $paymentMethods             = new PaymentMethods();
      }
      $paymentMethods->name       = $method;
      $paymentMethods->active     = 1;
      $paymentMethods->trySave();
      unset($paymentMethods);
    }
    unset($methods);
    gc_collect_cycles();
  }

  private function configureIva()
  {
    $manager  = Doctrine_Manager::getInstance();
    $manager->setCurrentConnection('connect');

    $taxes = array(
      array('name' => 'IVA22', 'value' => 22, 'active' => 1, 'is_default' => 1),
      array('name' => 'IVA21', 'value' => 21, 'active' => 0, 'is_default' => 0),
      array('name' => 'IVA20', 'value' => 20, 'active' => 0, 'is_default' => 0),
      array('name' => 'IVA10', 'value' => 10, 'active' => 1, 'is_default' => 0),
      array('name' => 'ESE0', 'value' => 0, 'active' => 1, 'is_default' => 0)
    );

    foreach ($taxes as $taxFromArr)
    {
      $tax = Doctrine::getTable('Tax')->findOneByName($taxFromArr['name']);
      if(!$tax)
      {
        $tax = new Tax();
      }
      $tax->name        = $taxFromArr['name'];
      $tax->value       = $taxFromArr['value'];
      $tax->active      = $taxFromArr['active'];
      $tax->is_default  = $taxFromArr['is_default'];
      $tax->trySave();
      $this->logSection('tax saved', sprintf('%s', $tax->name));
      unset($tax);
      unset($taxFromArr);
    }
    unset($taxes);
    unset($manager);
  }

  private function customersForeigners()
  {
    $manager  = Doctrine_Manager::getInstance();
    $manager->setCurrentConnection('connect1');
    $conn1    = $manager->getCurrentConnection();
    $result = $conn1->execute("
      SELECT 
      cli.id_cliente AS id,
      cli.cliente_codice AS code, 
      cli.cliente_denominazione AS name, 
      cli.cliente_piva_cf AS identification, 
      cli.cliente_estero AS address
      FROM gest_clienti AS cli 
      WHERE cli.cliente_stato = 2 AND cli.cliente_cancellato = 'no'
    ");
    $rows   = $result->fetchAll();

    $manager->setCurrentConnection('connect');
    
    foreach ($rows as $row)
    {
      $import = Doctrine::getTable('ImportCustomer')->findOneByImportedId($row['id']);  
      if($import)
      {
        $customer = Doctrine::getTable('Customer')->find($import->id);
      }
      else
      {
        $customer                       = new Customer();  
      }
      $customer->name                 = $row['name'];
      $customer->code                 = $row['code'];
      $customer->identification       = $row['identification'];
      $customer->invoicing_address    = preg_replace('#<br\s*?/?>#i', "\n", $row['address']);
      try
      {

        $customer->trySave();
        $customer->refresh();

        $importCustomer               = new ImportCustomer();
        $importCustomer->id           = $customer->getId();
        $importCustomer->imported_id  = $row['id'];
        $importCustomer->trySave();

        //$this->invoices();

      }
      catch(Exception $e){}
      $this->logSection('customer saved', sprintf('%s', $row['code']));

      unset($customer);
      unset($row);
    }

  }

  private function customers()
  {
    $manager  = Doctrine_Manager::getInstance();
    $manager->setCurrentConnection('connect1');
    $conn1    = $manager->getCurrentConnection();
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
      $import = Doctrine::getTable('ImportCustomer')->findOneByImportedId($row['id']);  
      if($import)
      {
        $customer = Doctrine::getTable('Customer')->find($import->id);
      }
      else
      {
        $customer                       = new Customer();  
      }
      $customer->name                 = $row['name'];
      $customer->code                 = $row['code'];
      $customer->identification       = $row['identification'];
      $customer->invoicing_address    = $row['via'] . "\n". $row['cap'] . ' ' . $row['comune'] . ' ('. $row['sigla_provincia'] .')';
      try
      {

        $customer->trySave();
        $customer->refresh();

        $importCustomer               = new ImportCustomer();
        $importCustomer->id           = $customer->getId();
        $importCustomer->imported_id  = $row['id'];
        $importCustomer->trySave();

        //$this->invoices();

      }
      catch(Exception $e){}
      $this->logSection('customer saved', sprintf('%s', $row['code']));

      unset($customer);
      unset($row);
    }

  }

  private function invoices()
  {
    gc_enable();
    $manager  = Doctrine_Manager::getInstance();
    $manager->setCurrentConnection('connect1');
    $conn1    = $manager->getCurrentConnection();
    $result = $conn1->execute("
      SELECT 
      fat.id_fattura AS id,
      fat.id_cliente AS customer_id,
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
      fat.fattura_pagata AS pagato,
      fat.fattura_cliente_indirizzo AS via,
      fat.fattura_cliente_citta AS comune,
      fat.fattura_cliente_cap AS cap,
      fat.fattura_metodo_pagamento AS payment_method_id, 
      fat.fattura_note_pagamento AS payment_method,
      fat.fattura_note AS notes
      FROM gest_fatture fat
    ");
    $rows   = $result->fetchAll();

    foreach ($rows as $row)
    {
      $manager->setCurrentConnection('connect');

      $import_customer = Doctrine::getTable('ImportCustomer')->findOneByImportedId($row['customer_id']);
      if(!$import_customer)
      {
        continue;
      }
      
      $import = Doctrine::getTable('ImportInvoice')->findOneByImportedId($row['id']);  
      if($import)
      {
        $invoice = Doctrine::getTable('Invoice')->find($import->id);
      }
      else
      {
        $invoice                          = new Invoice();
      }
      $invoice->customer_id               = $import_customer->id;
      $invoice->series_id                 = 1;
      $invoice->issue_date                = $row['issue_date'];
      $invoice->due_date                  = $row['due_date'];
      $invoice->base_amount               = floatval($row['base_amount']);
      $invoice->discount_amount           = 0;
      $invoice->net_amount                = floatval($row['base_amount']);
      $invoice->gross_amount              = round(floatval($row['base_amount']) * (1 + (floatval($row['iva']/100))),2);
      $invoice->paid_amount               = 0;
      if($row['pagato'] == 'si')
      {
        $invoice->paid_amount             = $invoice->gross_amount;
      }
      $invoice->tax_amount                = $invoice->gross_amount - $invoice->net_amount;
      $invoice->draft                     = 0;
      $invoice->number                    = $row['number'];
      $invoice->description               = $row['description'];
      $invoice->customer_identification   = $row['customer_identification'];
      $invoice->customer_name             = $row['customer_name'];
      $invoice->invoicing_address         = $row['via'] . "\n". $row['cap'] . ' ' . $row['comune'];
      $paymentMethod                      = Doctrine::getTable('PaymentMethods')->findOneByName($row['payment_method_id']);
      $invoice->payment_method_id         = $paymentMethod->id;
      $invoice->payment_method            = $row['payment_method'];
      $invoice->notes                     = $row['notes'];

      try
      {
        $invoice->trySave();
        $invoice->refresh();
        $invoice->trySave();  // Trick 

        $importInvoice = Doctrine::getTable('ImportInvoice')->find($invoice->getId());
        if(!$importInvoice)
        {
          $importInvoice               = new ImportInvoice();
        }
        $importInvoice->id           = $invoice->getId();
        $importInvoice->imported_id  = $row['id'];
        $importInvoice->trySave();

        if($row['pagato'] == 'si')
        {
          $payment = Doctrine::getTable('Payment')->findOneByInvoiceId($invoice->getId());
          if(!$payment)
          {
            $payment                = new Payment();
          }
          $payment->invoice_id    = $invoice->id;
          $payment->date          = $invoice->issue_date;
          $payment->amount        = $invoice->gross_amount;
          $payment->trySave();
          unset($payment);
        }
        //$this->items($invoice, $row['id'], $row['iva']);
        $this->logSection('invoice saved', sprintf('%s', $invoice->getId()));
      }
      catch(Exception $e)
      {
        echo $e;
        //$this->logSection('invoice ERR', sprintf('%s', $e), null, 'ERROR');
      }
      unset($invoice);
      unset($paymentMethod);
      unset($row);
    }
    unset($rows);
    unset($result);
    unset($conn1);
    unset($manager);

    gc_collect_cycles();
  }

  private function items()
  {
    gc_enable();
    $manager  = Doctrine_Manager::getInstance();
    $manager->setCurrentConnection('connect1');
    $conn1    = $manager->getCurrentConnection();
    $result = $conn1->execute("
      SELECT r.*, 
      f.fattura_iva, 
      f.id_cliente FROM gest_righe AS r
      LEFT JOIN gest_fatture AS f ON f.id_fattura = r.id_fattura
    ");
    $rows   = $result->fetchAll();

    $manager->setCurrentConnection('connect');

    foreach ($rows as $row)
    {

      $importCustomer = Doctrine::getTable('ImportCustomer')->findOneByImportedId($row['id_cliente']);
      if(!$importCustomer)
      {
        continue;
      }

      $import = Doctrine::getTable('ImportItems')->findOneByImportedIdAndImportedRow($row['id_fattura'], $row['riga_numero']);
      if($import)
      {
        $item = Doctrine::getTable('Item')->find($import->id);
      }
      else
      {
        $item                 = new Item();
      }
      $importInvoice = Doctrine::getTable('ImportInvoice')->findOneByImportedId($row['id_fattura']);  

      $item->common_id      = $importInvoice->getId();
      $item->description    = $row['riga_causale'];
      $item->discount       = 0;
      $item->quantity       = 1;
      $item->unitary_cost   = $row['riga_importo'];
      if($row['riga_tipo_extra'])
      {
        $item->quantity       = $row['riga_extra1'];
        $item->unitary_cost   = $row['riga_extra2'];
      }
      $item->trySave();
      $item->refresh();
      //$this->logSection('item saved', sprintf('%s', $item->getId()));

      $importItems = Doctrine::getTable('ImportItems')->find($item->getId());
      if(!$importItems)
      {
        $importItems               = new ImportItems();
      }
      $importItems->id           = $item->getId();
      $importItems->imported_id  = $row['id_fattura'];
      $importItems->imported_row = $row['riga_numero'];
      $importItems->trySave();


      $itemTax          = new ItemTax();
      $itemTax->item_id = $item->getId();
      $itemTax->tax_id  = Doctrine::getTable('Tax')->findOneByValue($row['fattura_iva'])->id;
      try
      {
        $itemTax->trySave();
        $itemTax->refresh();
        $this->logSection('item tax saved', '');
      }
      catch(Exception $e)
      {
        echo $e;
      }

      unset($item);
      unset($row);
      unset($itemTax);
      unset($import);
      unset($importItems);
    }
    unset($rows);
    unset($conn1);
    unset($result);
    unset($manager);
    gc_collect_cycles();

  }
}
<?php

/**
 * ImportInvoiceTable
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class ImportInvoiceTable extends PluginImportInvoiceTable
{
    /**
     * Returns an instance of this class.
     *
     * @return object ImportInvoiceTable
     */
    public static function getInstance()
    {
        return Doctrine_Core::getTable('ImportInvoice');
    }
}
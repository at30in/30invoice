<?php

/**
 * ImportItemsTable
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class ImportItemsTable extends PluginImportItemsTable
{
    /**
     * Returns an instance of this class.
     *
     * @return object ImportItemsTable
     */
    public static function getInstance()
    {
        return Doctrine_Core::getTable('ImportItems');
    }
}
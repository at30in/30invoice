<?php
use_helper('JavascriptBase', 'Number', 'Siwapp', 'Date');

$customers = $pager->getResults();
$csrf     = new sfForm();
?>

<div class="content">
  
  <?php if (count($customers)): ?>
    
    <?php echo form_tag('customersRequisitions/batch', 'id=batch_form class=batch') ?>
      <?php echo $csrf['_csrf_token']->render(); ?>
      <input type="hidden" name="batch_action" id="batch_action">

      <table id="listing" class="listing">
        <thead>

          <!-- <tr class="empty noborder listing-options">
            <td colspan="2">
              <?php echo gButton_to_function(__("Delete"), "do_batch('delete')", array('class'=>'batch delete action-clear', 'confirm'=>__('Are you sure?'))) ?>
            </td>
            <td class="strong noborder"><?php echo __('Total')?></td>
            <td class="totalDue strong noborder right"><?php echo format_currency($due,$currency)?></td>
            <td class="strong noborder right"><?php echo format_currency($gross,$currency)?></td>
            <td colspan="1000" class="noborder"></td>
          </tr> -->

          <tr class="empty noborder">
            <td colspan="1000"></td>
          </tr>

          <tr>
            <th class="xs"><input id="select_all" rel="all" type="checkbox" name="select_all"></th>
            <?php
              // sort parameter => array (Name, default order)
              renderHeaders(array(
                'name' => array('Customer Name', 'asc'),
                'phone'    => array('Phone', 'desc'),
                'vat'    => array('Vat', 'desc'),
                ), $sf_data->getRaw('sort'), '@customers_requisitions');
            ?>
            <th class="noborder"></th>
          </tr>
        </thead>

        <tbody>
          <?php foreach ($customers as $i => $customer): ?>
            <?php
              $id       = $customer->getId();
              $parity   = ($i % 2) ? 'odd' : 'even';
            ?>
            <tr id="customerRequisition-<?php echo $id ?>" class="<?php echo "$parity link customerRequisition-$id " ?>">
              <td class="check"><input rel="item" type="checkbox" value="<?php echo $id ?>" name="ids[]"></td>
              <td><?php echo $customer ?></td>
              <td><?php echo $customer->getPhone() ?></td>
              <td class="right"><?php echo $customer->getVat() ?></td>
              <td class="right">
               <?php echo format_currency($customer->getAddress())  ?>
              </td>
              <!-- <td class="action payments">
                <?php echo gButton(__("Invoices"), array('id'=>'load-invoices-for-'.$id,'type'=>'button', 'class'=>'invoices action-clear','href'=>'@invoices?search[customer_id]='.$id)) ?>
              </td> -->
            </tr>
          <?php endforeach; ?>
        </tbody>

        <tfoot>
          <tr class="noborder">
            <td colspan="10" class="listing-options">
              <?php echo gButton_to_function(__("Delete"), "do_batch('delete')", array('class'=>'batch delete action-clear', 'confirm'=>__('Are you sure?'))) ?>
            </td>
          </tr>
        </tfoot>

      </table>
    </form>

    <?php include_partial('global/pager', array('pager' => $pager, 'route' => '@customers_requisitions')) ?>
    
  <?php else: ?>
    <p><?php echo __('No results') ?></p>
  <?php endif ?>
  
</div>

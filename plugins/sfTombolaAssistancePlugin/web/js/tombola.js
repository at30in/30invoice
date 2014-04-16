$(document).ready(function() {
  $(document).bind('GlobalUpdateEvent', function() {

    if($('select.tax').val() == 5) {
      $('#invoice_description').val('Esente Iva art 7 ter del DPR n.633/1972');
    }
  });
});
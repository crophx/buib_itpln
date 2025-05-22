// Fungsi untuk menginisialisasi plugin
$(document).ready(function () {
    // jquery datatables
    $('#basic-datatables').DataTable({
    });

    // datepicker
    $('.date-picker').datepicker({
        format: "dd-mm-yyyy",
        autoclose: true,
        todayHighlight: true
    });

    // select2
    $('.select2-single').select2();

	$('[data-tooltip="tooltip"]').tooltip();
});
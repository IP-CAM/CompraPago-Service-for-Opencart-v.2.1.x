<?php
require_once __DIR__."/../../../../../../vendor/autoload.php";

use Compropago\Sdk\Controllers\Views;
?>

<?php echo $header; ?>
<?php echo $column_left ?>

<div class="container">
    <div class="row">
        <div class="col-sm-12">
            <?php
                $obj = json_decode(base64_decode($info_order));
                $id = $obj->id;
            ?>
            <?php Views::loadView('iframe', $id); ?>
        </div>
    </div>
</div>

<?php echo $footer; ?>


<script language="javascript" type="text/javascript">
    function printDiv(divID) {
        //Get the HTML of div
        var divElements = document.getElementById(divID).innerHTML;
        //Get the HTML of whole page
        var oldPage = document.body.innerHTML;

        //Reset the page's HTML with div's HTML only
        document.body.innerHTML = "<html><head><title></title></head><body>" + divElements + "</body>";

        //Print Page
        window.print();

        //Restore orignal HTML
        document.body.innerHTML = oldPage;
    }
</script>
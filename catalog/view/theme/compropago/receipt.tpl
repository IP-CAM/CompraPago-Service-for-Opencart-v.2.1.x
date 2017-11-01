<?php echo $header; ?><?php echo $column_left; ?>

<?php
  $log = new \Log('compropago_view.log');
  $log->write("test 2....|heading title->" . $heading_title);
  echo "TEST 2";
?>

<div id="content">
  <div class="page-header">
    <div class="container-fluid">
      <h1><?php echo $heading_title; ?></h1>
      <ul class="breadcrumb">
        <?php foreach ($breadcrumbs as $breadcrumb) { ?>
        <li><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a></li>
        <?php } ?>
      </ul>
    </div>
  </div>
  <div class="container-fluid"><?php echo $record; ?></div>
</div>
<?php echo $footer; ?>
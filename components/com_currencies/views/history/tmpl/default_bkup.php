<?php
?>
<div class="row-fluid">
    <div class="span12">
        <img src="<?php echo $this->chart?>" />
    </div>
</div>
<div class="row-fluid">
    <div class="span4">
        Date
    </div>
    <div class="span4">
        <?php echo $this->baseCurrency ?>
    </div>
    <div class="span4">
        <?php echo $this->currency2 ?>
    </div>
</div>



<?php foreach($this->rates as $rate){?>
    <div class="row-fluid">
        <div class="span4">
            <?php echo $rate['Date'] ?>
        </div>
        <div class="span4">
            1 <?php echo $this->baseCurrency ?> =
        </div>
        <div class="span4">
            <?php echo $rate['Rate'].' '.$this->currency2; ?>
        </div>
    </div>
<?php } ?>

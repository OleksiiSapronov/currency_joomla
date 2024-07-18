<?php

defined('_JEXEC') or die('Restricted access');





if( date('Y-m-d H:i:s') > date("Y-m-d").' 12:00' && CurrenciesController::canupdate()): ?>

<script>

jQuery.ajax({

      url: "index.php?option=com_currencies&task=update&tmpl=component&format=row",

      type: "post",

      data: '',

      success: function(){

      },

      error:function(){

      }   

    }); 

</script>

<?php endif; ?>

    







<?php





/*

<script src="http://code.jquery.com/jquery-1.9.1.js"></script>

<script src="http://code.jquery.com/ui/1.10.3/jquery-ui.js"></script>

*/

?>







<script>

jQuery(function() {

   jQuery( "#currencies_tabs" ).tabs({

   });

});

</script>


<? 


$link_link=JURI::root();
$u = JURI::getInstance( $link_link );
$domenka = strtoupper(substr($u->getHost(), 4));

$tokens=@JText::_(COM_CURRENCIES_.$domenka);

$tytulaa = @JText::_(COM_CURRENCIESTYTULAA);
$tytulbb = @JText::_(COM_CURRENCIESTYTULBB);

 $dajtytul="$tytulaa $tokens $tytulbb" ;
      $document = JFactory::getDocument();		
      $document->setTitle($dajtytul);	
      
?><h2><? echo $dajtytul="$tytulaa $tokens $tytulbb"; ?></h2>

Kursy walut - Tabela A kursów średnich walut obcych na podstawie notowań
Narodowego Banku Polskiego (NBP) obowiązujące od dnia <? echo $update_date = $params->get('update_date'); ?>      

<br>   <br> 
 {module Reklama LINKI}   <br> 
<div id="currencies">


<?php 

foreach($cols as $val){

   //echo '<li><a href="'.JRoute::_('index.php?option=com_currencies&task=getdatacurrencies&tmpl=component&format=row&currency='.$val.'&module='.$module->id).'">'.$val.'</a></li>';

   
    echo '<div class="row-fluid" id="tab'.$val.'">';
    echo '<h3>'.$val.'</h3>';
    echo CurrenciesController::getdatacurrencies($val, $currencies);

    echo '</div>';
}
?>



<div class="clear"></div>

<div class="tab-top-border"></div>





<?php 



?>

</div>
{module ReklamaBIG}
<br>
<div>Dane dostarcza:</div>
<a href="http://nbp.pl/" target="blank"><img src="components/com_currencies/tmpl/nbp.jpg" alt="" class="nbp"></a>
<br>
<?php

$update_date = $params->get('update_date');

if(!empty($update_date))

   echo JText::_('KURS_WALUT_NBP_Z').$update_date;




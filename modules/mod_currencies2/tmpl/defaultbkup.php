<?php

defined('_JEXEC') or die('Restricted access');





echo '<table id="currencies2_table"><tr><td class="first_row first_column">&nbsp;</td>';

foreach($currencies as $val)

{

    echo '<td class="first_row"><span class="flagDefault flag'.$val['currency'].'" title="'.JText::_('WALUTA_'.$val['currency']).' '.$val['currency'].'" ></span> ';



    if(!empty($val['link']))

    {

        echo '<a href="'.$val['link'].'" target="_blank">'.$val['currency'].'</a>';

    }

    else

        echo $val['currency'];



    echo '</td>';



}



echo '</tr><tr>';

echo '<td class="first_column" nowrap="nowrap">

   <span class="flagDefault flag'.$maincurrency.'" title="'.JText::_('WALUTA_'.$maincurrency).' '.$maincurrency.'" >&nbsp;</span>1&nbsp;'.str_replace(" ", "&nbsp;", JText::_('WALUTA_'.$maincurrency)).'&nbsp;('.$maincurrency.')&nbsp;=

   <br /></td>';

foreach($currencies as $val)

{

    echo '<td>'.round($val['kurs'], 3).' ';



    if(JText::_('SYMBOL_'.$val['currency']) != 'SYMBOL_'.$val['currency'])

        echo '<span title="'.$val['currency'].'">'.JText::_('SYMBOL_'.$val['currency']).'</span>';



    echo '</td>';

}


echo '</tr><tr>';

echo '<td class="first_column" nowrap="nowrap">'.JText::_('KURS_ODWROTNY').'</td>';

foreach($currencies as $val)

{

    echo '<td>'.round($val['kurs_odwrotny'], 3).' ';



    echo '</td>';

}




echo '</tr></table>';

        


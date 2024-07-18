<?php



defined('_JEXEC') or die;





// no extends JControllerLegacy and no extends JController - to make it working for both j2.5 and j3

class CurrenciesController extends JControllerLegacy

{



    public function canupdate()

    {

        //$date = '2013-06-26';

        $date = date("Y-m-d");

        return true;

        $db = JFactory::getDbo();



        $db->setQuery("SELECT * FROM #__nbp WHERE data='".$date."' LIMIT 1");

        $row = $db->loadObject();

        if (!empty($row))

        {

            return false;

        }

        else

        {

            return true;

        }



        return false;

    }







    public function update()

    {

        //$date = '2013-06-26';

        $date = date("Y-m-d");



        $db = JFactory::getDbo();





        if (!CurrenciesController::canupdate())

        {

            echo 'no update';

        }

        else

        {

            $data1 = date("ymd", strtotime($date) );



            $lista = file_get_contents('http://www.nbp.pl/kursy/xml/dir.aspx?tt=A');

            //preg_match('/a[0-9]{1,3}z'.$data1.'.xml/', $lista, $matches);

            preg_match('/'.$date.'  (?P<time>\d\d:\d\d)(.*)(?P<file>a[0-9]{1,3}z'.$data1.'.xml)/', $lista, $matches);



            if(!empty($matches['time']))

            {



                $db->setQuery("SELECT * FROM #__modules where module='mod_currencies3' ");

                $modules = $db->loadObjectList();

                foreach($modules as $module)

                {

                    $params = new JRegistry;

                    $params->loadString($module->params);



                    $params->set('update_date', $date.' '.$matches['time']);



                    $db->setQuery("UPDATE #__modules set params='".$params->toString()."' WHERE id=".$module->id);

                    $db->query();

                }



                $url = 'http://www.nbp.pl/kursy/xml/' . $matches['file'];



                $waluty = simplexml_load_file($url);



                foreach($waluty->pozycja as $val)

                {

                    $db->setQuery("SELECT * FROM #__nbp where data='$date' and kod='".$val->kod_waluty[0]->__toString()."' ");

                    $testRow = $db->loadObject();



                    if(empty($testRow))

                    {

                        $query = "INSERT INTO #__nbp (data, przelicznik, kod, kurs) 

                           VALUES('".$date."', '".$val->przelicznik[0]->__toString()."', '".$val->kod_waluty[0]->__toString()."', '".str_replace(",", ".", $val->kurs_sredni[0]->__toString())."') ";

                        $db->setQuery($query);

                        $db->query();

                    }

                }

                echo 'update ' . $date;

            }

            else

            {

                echo 'no file';

            }

        }

    }





    public function data()

    {

        $db = JFactory::getDbo();



        $allowedCurrencies = Array('EUR', 'USD');



        $subqueries = array();

        foreach($allowedCurrencies as $currency)

        {

            $subqueries[] = "(SELECT kod, kurs, data from #__nbp 

         WHERE kod='".$currency."' GROUP BY kod, data ORDER BY data desc LIMIT 2)";

        }



        $query = implode(' UNION ALL ', $subqueries);





        $db->setQuery($query);



        $rows = $db->loadObjectList();



        return $rows;

    }







    public function getdatacurrencies($maincurrency, $currencies)

    {
        $db = JFactory::getDbo();

        unset($currencies[$maincurrency]);



        $allowedCurrencies = array($maincurrency);

        foreach($currencies as $val)

        {

            $allowedCurrencies[] = $val['currency'];

        }



        $subqueries = array();

        foreach($allowedCurrencies as $currency1)

        {

            $subqueries[] = "(SELECT kod, przelicznik, kurs, data from #__nbp 

         WHERE kod='".$currency1."' GROUP BY kod, data ORDER BY data desc LIMIT 2)";

        }



        $query = implode(' UNION ALL ', $subqueries);

        $db->setQuery($query);

        $rows = $db->loadObjectList();





        if($maincurrency != 'PLN')

        {

            if($rows[0]->kod == $maincurrency)

            {

                $maincurrencyKurs = $rows[0]->kurs;

            }

            if($rows[1]->kod == $maincurrency)

            {

                $maincurrencyKursPoprzedni = $rows[1]->kurs;

            }



            $maincurrencyPrzelicznik = $rows[1]->przelicznik;

        }

        else

        {

            $maincurrencyKurs = 1;

            $maincurrencyKursPoprzedni = 1;

            $maincurrencyPrzelicznik = 1;

        }



        if(!empty($rows))

        {

            foreach($rows as $key => $row)

            {

                if($row->kod != $maincurrency)

                {

                    if(empty($currencies[$row->kod]['kurs']))

                    {

                        if(!empty($maincurrencyKurs))

                        {

                            $currencies[$row->kod]['kurs'] = ($maincurrencyKurs / $row->kurs)*$row->przelicznik;

                            $currencies[$row->kod]['przelicznik'] = $row->przelicznik;

                        }

                    }

                    else

                    {

                        if(!empty($maincurrencyKursPoprzedni))

                        {

                            $currencies[$row->kod]['kurs_poprzedni'] = ($maincurrencyKursPoprzedni / $row->kurs)*$row->przelicznik;

                            $currencies[$row->kod]['roznica'] = $currencies[$row->kod]['kurs'] - $currencies[$row->kod]['kurs_poprzedni'];



                            if($currencies[$row->kod]['kurs_poprzedni']!=0)

                                $currencies[$row->kod]['roznica_procent'] = round(( $currencies[$row->kod]['roznica']/$currencies[$row->kod]['kurs_poprzedni'])*100, 4);

                            else

                                $currencies[$row->kod]['roznica_procent'] = 0;

                        }

                    }



                }

            }

        }



        echo '<table cellspacing="2" cellpadding="2" class="table_currencies"><tr class="currency_header">';

        // echo '<td>'.JText::_('KURS').' '.JText::_('WALUTA_'.$maincurrency.'1').' ('.$maincurrency.') '.JText::_('WALUTA_DO').'</td>';
        echo '<td>'.JText::_('KURS').' '.JText::_('WALUTA_'.$maincurrency.'1').'                       '.JText::_('WALUTA_DO').'</td>';

        echo '<td class="kod_waluty_currencies">'.JText::_('KOD_WALUTY').'</td>';

        echo '<td class="kod_waluty_currencies">'.JText::_('SREDNI_KURS_NBP').'</td>';

        echo '<td colspan="2" style="text-align:center;">'.JText::_('ZMIANA').'</td>';

        echo '<td>'.JText::_('STRONA_KONWERSJI').'</td>';

        echo '</tr>';



        foreach($currencies as $val)

        {



            if($val['currency'] == 'PLN')

            {

                $val['kurs'] = $maincurrencyKurs;

                $val['kurs_poprzedni'] = $maincurrencyKursPoprzedni;

                $val['przelicznik'] = 1;

                $val['roznica'] = $maincurrencyKurs - $maincurrencyKursPoprzedni;

                if($maincurrencyKursPoprzedni!=0)

                    $val['roznica_procent'] = round(($val['roznica']/$maincurrencyKursPoprzedni)*100, 4);

                else

                    $val['roznica_procent'] = 0;

            }



            echo '<tr>';

            echo '<td><span class="flagDefault flag'.$val['currency'].'"></span>'.JText::_('WALUTA_'.$val['currency']).'</td>';

            echo '<td class="tdcenter">'.$maincurrencyPrzelicznik.'&nbsp;'.$maincurrency.'&nbsp;=</td>';

            echo '<td class="tdcenter">'.sprintf("%.4f", round($val['kurs'], 4)).'&nbsp;'.$val['currency'].'</td>';



            if($val['roznica'] < 0)

            {

                echo '<td class="percentagered tdright" >';

                echo sprintf("%.4f", round($val['roznica'], 4)).'&nbsp;&nbsp;';

                echo '</td>';



                echo '<td class="percentagered tdright">';

                echo sprintf("%.2f", $val['roznica_procent']).'%';

                echo '</td>';

            }

            elseif($val['roznica'] > 0)

            {

                echo '<td class="percentagegreen tdright">';

                echo '+'.sprintf("%.4f", round($val['roznica'], 4)).'&nbsp;&nbsp;';

                echo '</td>';



                echo '<td class="percentagegreen tdright">';

                echo '+'.sprintf("%.2f", $val['roznica_procent']).'%';

                echo '</td>';

            }





            echo '</td>';

            echo '<td>';

            if(!empty($val['link']))

            {

                echo '<a href="'.$val['link'].'">'.JText::_('KONWENTER').$val['currency'].'</a>';

            }

            echo '</td>';

            echo '</tr>';

        }

        echo '</table>';

    }







    public function getdata_currencies2($maincurrency, &$currencies)

    {

        $db = JFactory::getDbo();



        foreach($currencies as $key => $val)

        {

            $db->setQuery("SELECT currency_from, currency_to, value FROM #__rates_history 

         WHERE  (currency_from='".$maincurrency."' and currency_to='".$val['currency']."') 

               OR (currency_from='".$val['currency']."' and currency_to='".$maincurrency."') ORDER BY timestamp desc LIMIT 2 ");

            $rows = $db->loadObjectList();

            if(!empty($rows))

            {

                foreach($rows as $val1)

                {

                    if($val1->currency_from == $maincurrency)

                    {

                        $currencies[$key]['kurs'] = $val1->value;

                    }

                    else

                    {

                        $currencies[$key]['kurs_odwrotny'] = $val1->value;

                    }

                }

            }

        }

    }



}


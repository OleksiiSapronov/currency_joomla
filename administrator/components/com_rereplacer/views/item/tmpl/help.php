<?php
/**
 * @package         ReReplacer
 * @version         13.2.0PRO
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            https://regularlabs.com
 * @copyright       Copyright © 2023 Regular Labs All Rights Reserved
 * @license         GNU General Public License version 2 or later
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory as JFactory;
use Joomla\CMS\Language\Text as JText;
use RegularLabs\Library\Alias as RL_Alias;

$user    = JFactory::getApplication()->getIdentity() ?: JFactory::getUser();
$contact = (object) [];

$db         = JFactory::getDbo();
$table_name = $db->getPrefix() . 'contact_details';

if (in_array($table_name, $db->getTableList()))
{
    $query = 'SHOW FIELDS FROM ' . $db->quoteName($table_name);
    $db->setQuery($query);
    $columns = $db->loadColumn();

    if (in_array('misc', $columns))
    {
        $query = $db->getQuery(true)
            ->select('c.misc')
            ->from('#__contact_details as c')
            ->where('c.user_id = ' . (int) $user->id);
        $db->setQuery($query);
        $contact = $db->loadObject();
    }
}

$yes = '<td align="center"><span class="icon-save"></span> ' . JText::_('JYES') . '</td>';
$no  = '<td align="center" class="ghosted"><span class="icon-cancel"></span> ' . JText::_('JNO') . '</td>';

?>
<div class="container-fluid">

    <p><?php echo JText::_('RR_DYNAMIC_TAGS_DESC'); ?></p>

    <table class="table table-striped">
        <thead>
            <tr>
                <th>
                    <?php echo JText::_('RL_INPUT_SYNTAX'); ?>
                </th>
                <th class="left">
                    <span><?php echo JText::_('RL_OUTPUT_EXAMPLE'); ?></span>
                </th>
                <th class="left">
                    <span><?php echo JText::_('JGLOBAL_DESCRIPTION'); ?></span>
                </th>
                <th>
                    <span rel="tooltip" title="<?php echo JText::_('RR_USE_IN_SEARCH'); ?>"><?php echo JText::_('RR_SEARCH'); ?></span>
                </th>
                <th>
                    <span rel="tooltip" title="<?php echo JText::_('RR_USE_IN_REPLACE'); ?>"><?php echo JText::_('RR_REPLACE'); ?></span>
                </th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="font-family:monospace">[[comma]]</td>
                <td style="font-family:monospace">,</td>
                <td><?php echo JText::_('RR_USE_INSTEAD_OF_A_COMMA,RR_TREAT_AS_LIST'); ?></td>
                <?php echo $yes; ?>
                <?php echo $yes; ?>
            </tr>
            <tr>
                <td style="font-family:monospace">[[space]]</td>
                <td></td>
                <td><?php echo JText::_('RR_USE_FOR_LEADING_OR_TRAILING_SPACES'); ?></td>
                <?php echo $yes; ?>
                <?php echo $yes; ?>
            </tr>
            <tr>
                <td style="font-family:monospace">[[user:id]]</td>
                <td><?php echo $user->id; ?></td>
                <td>
                    <?php echo JText::_('RL_DYNAMIC_TAG_USER_ID'); ?>
                    <br><em class="ghosted"><?php echo JText::_('RL_DYNAMIC_TAG_USER_TAG_DESC'); ?></em>
                </td>
                <?php echo $no; ?>
                <?php echo $yes; ?>
            </tr>
            <tr>
                <td style="font-family:monospace">[[user:username]]</td>
                <td><?php echo $user->username; ?></td>
                <td>
                    <?php echo JText::_('RL_DYNAMIC_TAG_USER_USERNAME'); ?>
                    <br><em class="ghosted"><?php echo JText::_('RL_DYNAMIC_TAG_USER_TAG_DESC'); ?></em>
                </td>
                <?php echo $no; ?>
                <?php echo $yes; ?>
            </tr>
            <tr>
                <td style="font-family:monospace">[[user:name]]</td>
                <td><?php echo $user->name; ?></td>
                <td>
                    <?php echo JText::_('RL_DYNAMIC_TAG_USER_NAME'); ?>
                    <br><em class="ghosted"><?php echo JText::_('RL_DYNAMIC_TAG_USER_TAG_DESC'); ?></em>
                </td>
                <?php echo $no; ?>
                <?php echo $yes; ?>
            </tr>
            <tr>
                <td style="font-family:monospace">[[user:misc]]</td>
                <td><?php echo $contact->misc ?? ''; ?></td>
                <td>
                    <?php echo JText::_('RL_DYNAMIC_TAG_USER_OTHER'); ?>
                    <br><em class="ghosted"><?php echo JText::_('RL_DYNAMIC_TAG_USER_TAG_DESC'); ?></em>
                </td>
                <?php echo $no; ?>
                <?php echo $yes; ?>
            </tr>
            <tr>
                <td style="font-family:monospace">[[article:id]]</td>
                <td>123</td>
                <td>
                    <?php echo JText::_('RL_DYNAMIC_TAG_ARTICLE_ID'); ?>
                    <br><em class="ghosted">
                        <?php echo JText::_('RR_ONLY_AVAILABLE_IN_SEARCH_AREA,RR_ENABLE_IN_AREA,RR_AREA_CONTENT'); ?>
                    </em>
                </td>
                <?php echo $no; ?>
                <?php echo $yes; ?>
            </tr>
            <tr>
                <td style="font-family:monospace">[[article:title]]</td>
                <td>My Article</td>
                <td>
                    <?php echo JText::_('RL_DYNAMIC_TAG_ARTICLE_TITLE'); ?>
                    <br><em class="ghosted">
                        <?php echo JText::_('RR_ONLY_AVAILABLE_IN_SEARCH_AREA,RR_ENABLE_IN_AREA,RR_AREA_CONTENT'); ?>
                    </em>
                </td>
                <?php echo $no; ?>
                <?php echo $yes; ?>
            </tr>
            <tr>
                <td style="font-family:monospace">[[article:alias]]</td>
                <td>my-article</td>
                <td>
                    <?php echo JText::_('RL_DYNAMIC_TAG_ARTICLE_OTHER'); ?>
                    <br><em class="ghosted">
                        <?php echo JText::_('RR_ONLY_AVAILABLE_IN_SEARCH_AREA,RR_ENABLE_IN_AREA,RR_AREA_CONTENT'); ?>
                    </em>
                </td>
                <?php echo $no; ?>
                <?php echo $yes; ?>
            </tr>
            <tr>
                <td nowrap="nowrap" style="font-family:monospace">
                    [[date:%A, %d %B %Y]]<br>
                    [[date:%Y-%m-%d]]
                </td>
                <td>
                    <?php echo strftime('%A, %d %B %Y'); ?><br>
                    <?php echo strftime('%Y-%m-%d'); ?>
                </td>
                <td><?php echo JText::sprintf('RL_DYNAMIC_TAG_DATE', '<a href="http://www.php.net/manual/function.strftime.php" target="_blank">', '</a>', '<span style="font-family:monospace">[[date: %A, %d %B %Y]]</span>'); ?></td>
                <?php echo $no; ?>
                <?php echo $yes; ?>
            </tr>
            <tr>
                <td style="font-family:monospace">
                    [[random:0-100]]<br>
                    [[random:1000-9999]]
                </td>
                <td>
                    <?php echo rand(0, 100); ?><br>
                    <?php echo rand(1000, 9999); ?>
                </td>
                <td><?php echo JText::_('RL_DYNAMIC_TAG_RANDOM'); ?></td>
                <?php echo $no; ?>
                <?php echo $yes; ?>
            </tr>
            <tr>
                <td style="font-family:monospace">
                    [[random:this,that]]<br>
                    [[random:1-10,20,50,100]]
                </td>
                <td>
                    <?php
                    $values = ['this', 'that'];
                    echo $values[rand(0, count($values) - 1)];
                    ?>
                    <br>

                    <?php
                    $values = [rand(1, 10), 20, 50, 100];
                    echo $values[rand(0, count($values) - 1)];
                    ?>
                </td>
                <td><?php echo JText::_('RL_DYNAMIC_TAG_RANDOM_LIST'); ?></td>
                <?php echo $no; ?>
                <?php echo $yes; ?>
            </tr>
            <tr>
                <td style="font-family:monospace">[[counter]]</td>
                <td>1</td>
                <td><?php echo JText::_('RL_DYNAMIC_TAG_COUNTER'); ?></td>
                <?php echo $no; ?>
                <?php echo $yes; ?>
            </tr>
            <tr>
                <td style="font-family:monospace">[[escape]]\1[[/escape]]</td>
                <td><?php echo addslashes(html_entity_decode(JText::_('RL_DYNAMIC_TAG_STRING_EXAMPLE'))); ?></td>
                <td><?php echo JText::_('RL_DYNAMIC_TAG_ESCAPE'); ?></td>
                <?php echo $no; ?>
                <?php echo $yes; ?>
            </tr>
            <tr>
                <td style="font-family:monospace">[[uppercase]]\1[[/uppercase]]</td>
                <td><?php echo strtoupper(JText::_('RL_DYNAMIC_TAG_STRING_EXAMPLE')); ?></td>
                <td><?php echo JText::_('RL_DYNAMIC_TAG_UPPERCASE'); ?></td>
                <?php echo $no; ?>
                <?php echo $yes; ?>
            </tr>
            <tr>
                <td style="font-family:monospace">[[lowercase]]\1[[/lowercase]]</td>
                <td><?php echo strtolower(JText::_('RL_DYNAMIC_TAG_STRING_EXAMPLE')); ?></td>
                <td><?php echo JText::_('RL_DYNAMIC_TAG_LOWERCASE'); ?></td>
                <?php echo $no; ?>
                <?php echo $yes; ?>
            </tr>
            <tr>
                <td style="font-family:monospace">[[notags]]\1[[/notags]]</td>
                <td><?php echo strip_tags(JText::_('RL_DYNAMIC_TAG_STRING_EXAMPLE')); ?></td>
                <td><?php echo JText::_('RL_DYNAMIC_TAG_NOTAGS'); ?></td>
                <?php echo $no; ?>
                <?php echo $yes; ?>
            </tr>
            <tr>
                <td style="font-family:monospace">[[nowhitespace]]\1[[/nowhitespace]]</td>
                <td><?php echo str_replace(' ', '', strip_tags(JText::_('RL_DYNAMIC_TAG_STRING_EXAMPLE'))); ?></td>
                <td><?php echo JText::_('RL_DYNAMIC_TAG_NOWHITESPACE'); ?></td>
                <?php echo $no; ?>
                <?php echo $yes; ?>
            </tr>
            <tr>
                <td style="font-family:monospace">[[toalias]]\1[[/toalias]]</td>
                <td><?php echo RL_Alias::get(JText::_('RL_DYNAMIC_TAG_STRING_EXAMPLE')); ?></td>
                <td><?php echo JText::_('RL_DYNAMIC_TAG_TOALIAS'); ?></td>
                <?php echo $no; ?>
                <?php echo $yes; ?>
            </tr>
        </tbody>
    </table>
</div>

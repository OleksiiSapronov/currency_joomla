<?php
/*------------------------------------------------------------------------
  Solidres - Hotel booking extension for Joomla
  ------------------------------------------------------------------------
  @Author    Solidres Team
  @Website   http://www.solidres.com
  @Copyright Copyright (C) 2013 - 2014 Solidres. All Rights Reserved.
  @License   GNU General Public License version 3, or later
------------------------------------------------------------------------*/

defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');
JHtml::_('bootstrap.tooltip');

$user	= JFactory::getUser();
$userId	= $user->get('id');
$listOrder	= $this->state->get('list.ordering');
$listDirn	= $this->state->get('list.direction');
$saveOrder	= $listOrder == 'a.ordering';
/*if ($saveOrder)
{
	$saveOrderingUrl = 'index.php?option=com_solidres&task=reservationassets.saveOrderAjax&tmpl=component';
	JHtml::_('sortablelist.sortable', 'reservationassetList', 'adminForm', strtolower($listDirn), $saveOrderingUrl);
}*/

$uri = JUri::getInstance();

$navBar = new JLayoutFile('hub.navbar');
?>
<script type="text/javascript">
	Joomla.orderTable = function()
	{
		table = document.getElementById("sortTable");
		direction = document.getElementById("directionTable");
		order = table.options[table.selectedIndex].value;
		if (order != '<?php echo $listOrder; ?>') {
			dirn = 'asc';
		}
		else {
			dirn = direction.options[direction.selectedIndex].value;
		}
		Joomla.tableOrdering(order, dirn, '');
	}

</script>
<div id="solidres">
    <div class="row">
		<?php echo $navBar->render(array('activeview' => $this->getName())) ?>
	</div>

	<div class="row">
		<div class="col-sm-12">
			<?php echo JToolbar::getInstance('solidrestoolbar')->render('solidrestoolbar');; ?>
		</div>
	</div>

	<div class="row">
		<div id="" class="sr_list_view col-sm-12">
			<form action="<?php echo JRoute::_('index.php?option=com_solidres&view=reservationassets'); ?>" method="post" name="adminForm" id="adminForm">
				<table class="table table-striped" id="reservationassetList">
					<thead>
						<tr>
							<th width="1%" class="nowrap center hidden-phone">
								<?php echo JHtml::_('grid.sort', '<i class="icon-menu-2"></i>', 'a.ordering', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING'); ?>
							</th>
							<th>
								<input type="checkbox" name="checkall-toggle" value="" onclick="Joomla.checkAll(this)" />
							</th>
							<!--<th class="nowrap">
								<?php /*echo JHtml::_('grid.sort',  'SR_HEADING_ID', 'a.id', $listDirn, $listOrder); */?>
							</th>-->
							<th>
								<?php echo JHtml::_('grid.sort',  'SR_HEADING_NAME', 'a.name', $listDirn, $listOrder); ?>
							</th>
							<th>
								<?php echo JHtml::_('grid.sort',  'SR_HEADING_PUBLISHED', 'a.state', $listDirn, $listOrder); ?>
							</th>

							<th class="category_name">
								<?php echo JHtml::_('grid.sort',  'SR_HEADING_CATEGORY', 'category_name', $listDirn, $listOrder); ?>
							</th>

							<th class="center">
								<?php echo JHtml::_('grid.sort',  'SR_HEADING_NUMBERROOMTYPE', 'number_of_roomtype', $listDirn, $listOrder); ?>
							</th>
              <th class="city_name">
								<?php echo JHtml::_('grid.sort',  'SR_HEADING_CITY', 'a.city', $listDirn, $listOrder); ?>
							</th>
						</tr>
					</thead>
					<tfoot>
						<tr>
							<td colspan="7">
								<?php echo $this->pagination->getListFooter(); ?>
							</td>
						</tr>
					</tfoot>
					<tbody>
					<?php foreach ($this->items as $i => $item) :
						$canCreate	= $user->authorise('core.create',		'com_solidres.reservationasset.'.$item->id);
						$canEdit	= $user->authorise('core.edit',			'com_solidres.reservationasset.'.$item->id);
						$canCheckin	= $user->authorise('core.manage',		'com_checkin') || $item->checked_out==$user->get('id') || $item->checked_out==0;
						$canChange	= $user->authorise('core.edit.state',	'com_solidres.reservationasset.'.$item->id);
						?>
						<tr class="row<?php echo $i % 2; ?>" sortable-group-id="<?php echo $item->category_id ?>">
							<td class="order nowrap center hidden-phone">
								<?php
								$iconClass = '';
								if (!$canChange)
								{
									$iconClass = ' inactive';
								}
								elseif (!$saveOrder)
								{
									$iconClass = ' inactive tip-top hasTooltip" title="' . JHtml::tooltipText('JORDERINGDISABLED');
								}
								?>
								<span class="sortable-handler<?php echo $iconClass ?>">
								<i class="icon-menu"></i>
								</span>
								<?php if ($canChange && $saveOrder) : ?>
									<input type="text" style="display:none" name="order[]" size="5" value="<?php echo $item->ordering ?>" class="width-20 text-area-order "/>
								<?php endif; ?>
							</td>
							<td>
								<?php echo JHtml::_('grid.id', $i, $item->id); ?>
							</td>
							<!--<td class="center">
								<?php /*echo (int) $item->id; */?>
							</td>-->
							<td style="width: 35%">
								<?php if ($item->checked_out) : ?>
									<?php echo JHtml::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'reservationassets.', $canCheckin); ?>
								<?php endif; ?>
								<?php if ($canCreate || $canEdit) : ?>
									<a href="<?php echo JRoute::_('index.php?option=com_solidres&task=reservationassetform.edit&id='.(int) $item->id.'&return='.base64_encode($uri));
; ?>">
										<?php echo $this->escape($item->name); ?></a>
								<?php else : ?>
										<?php echo $this->escape($item->name); ?>
								<?php endif; ?>
								<?php if ($item->default == 1) : ?>
                                <a href="#" title="<?php echo JText::_('SR_HEADING_DEFAULT') ?>"><i class="icon-star"></i></a>
								<?php endif ?>
							</td>
							<td class="center">
								<?php echo JHtml::_('jgrid.published', $item->state, $i, 'reservationassets.', $canChange);?>
							</td>
							<td>
								<?php echo $item->category_name;?>
							</td>
							<td class="center">
								<?php echo $item->number_of_roomtype?>
							</td>
              <td class="">
								<?php echo $item->city?>
							</td>
						</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
				<input type="hidden" name="task" value="" />
				<input type="hidden" name="return" value="<?php echo base64_encode(JRoute::_('index.php?option=com_solidres&view=reservationassets', false)) ?>" />
				<input type="hidden" name="boxchecked" value="0" />
				<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
				<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
				<?php echo JHtml::_('form.token'); ?>
			</form>
		</div>
	</div>
  <?php if ($this->showPoweredByLink) : ?>
		<div class="row-fluid">
			<div class="span12 powered">
				<p>Powered by <a href="http://wwww.solidres.com" target="_blank">Solidres</a></p>
			</div>
		</div>
	<?php endif ?>
</div>

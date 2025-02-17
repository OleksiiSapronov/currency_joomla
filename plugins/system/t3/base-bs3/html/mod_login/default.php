<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_login
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Component\ComponentHelper;

JLoader::register('UsersHelperRoute', JPATH_SITE . '/components/com_users/helpers/route.php');

if (version_compare(JVERSION, '4', 'ge')) {
	Factory::getDocument()->getWebAssetManager()
	->useScript('core')
	->useScript('keepalive');
	// HTMLHelper::_('bootstrap.tooltip');
}elseif (version_compare(JVERSION, '3.0', 'ge')) {
	HTMLHelper::_('behavior.keepalive');
	HTMLHelper::_('bootstrap.tooltip');
}
?>
<?php if ($type == 'logout') : ?>
	<form action="<?php echo Route::_('index.php', true, $params->get('usesecure')); ?>" method="post" id="login-form"
		  class="form-vertical">
		<?php if ($params->get('greeting')) : ?>
			<div class="login-greeting">
				<?php if ($params->get('name') == 0) : {
					echo Text::sprintf('MOD_LOGIN_HINAME', htmlspecialchars($user->get('name')));
				} else : {
					echo Text::sprintf('MOD_LOGIN_HINAME', htmlspecialchars($user->get('username')));
				} endif; ?>
			</div>
		<?php endif; ?>
		<div class="logout-button">
			<input type="submit" name="Submit" class="btn btn-primary" value="<?php echo Text::_('JLOGOUT'); ?>"/>
			<input type="hidden" name="option" value="com_users"/>
			<input type="hidden" name="task" value="user.logout"/>
			<input type="hidden" name="return" value="<?php echo $return; ?>"/>
			<?php echo HTMLHelper::_('form.token'); ?>
		</div>
	</form>
<?php else : ?>
	<form action="<?php echo Route::_('index.php', true, $params->get('usesecure')); ?>" method="post" id="login-form">
		<?php if ($params->get('pretext')): ?>
			<div class="pretext">
				<p><?php echo $params->get('pretext'); ?></p>
			</div>
		<?php endif; ?>
		<fieldset class="userdata">
			<div id="form-login-username" class="form-group">
				<?php if (!$params->get('usetext')) : ?>
					<div class="input-group">
						<span class="input-group-addon">
							<span class="fa fa-user tip" title="<?php echo Text::_('MOD_LOGIN_VALUE_USERNAME') ?>"></span>
						</span>
						<input id="modlgn-username" type="text" name="username" class="input form-control" tabindex="0" size="18"
							   placeholder="<?php echo Text::_('MOD_LOGIN_VALUE_USERNAME') ?>" aria-label="username" />
					</div>
				<?php else: ?>
					<label for="modlgn-username"><?php echo Text::_('MOD_LOGIN_VALUE_USERNAME') ?></label>
					<input id="modlgn-username" type="text" name="username" class="input-sm form-control" tabindex="0"
						   size="18" placeholder="<?php echo Text::_('MOD_LOGIN_VALUE_USERNAME') ?>"/>
				<?php endif; ?>
			</div>
			<div id="form-login-password" class="form-group">
				<?php if (!$params->get('usetext')) : ?>
				<div class="input-group">
						<span class="input-group-addon">
							<span class="fa fa-lock tip" title="<?php echo Text::_('JGLOBAL_PASSWORD') ?>"></span>
						</span>
					<input id="modlgn-passwd" type="password" name="password" class="input form-control" tabindex="0"
						   size="18" placeholder="<?php echo Text::_('JGLOBAL_PASSWORD') ?>" aria-label="password" />
				</div>
			<?php else: ?>
				<label for="modlgn-passwd"><?php echo Text::_('JGLOBAL_PASSWORD') ?></label>
				<input id="modlgn-passwd" type="password" name="password" class="input-sm form-control" tabindex="0"
					   size="18" placeholder="<?php echo Text::_('JGLOBAL_PASSWORD') ?>"/>
			<?php endif; ?>
			</div>
			<?php if(version_compare(JVERSION, '4.2', 'ge')):?>
				<?php foreach ($extraButtons as $button) :
						$dataAttributeKeys = array_filter(array_keys($button), function ($key) {
								return substr($key, 0, 5) == 'data-';
						});
						?>
						<div class="mod-login__submit form-group">
								<button type="button"
												class="btn btn-secondary w-100 <?php echo $button['class'] ?? '' ?>"
												<?php foreach ($dataAttributeKeys as $key) : ?>
														<?php echo $key ?>="<?php echo $button[$key] ?>"
												<?php endforeach; ?>
												<?php if ($button['onclick']) : ?>
												onclick="<?php echo $button['onclick'] ?>"
												<?php endif; ?>
												title="<?php echo Text::_($button['label']) ?>"
												id="<?php echo $button['id'] ?>"
												>
										<?php if (!empty($button['icon'])) : ?>
												<span class="<?php echo $button['icon'] ?>"></span>
										<?php elseif (!empty($button['image'])) : ?>
												<?php echo $button['image']; ?>
										<?php elseif (!empty($button['svg'])) : ?>
												<?php echo $button['svg']; ?>
										<?php endif; ?>
										<?php echo Text::_($button['label']) ?>
								</button>
						</div>
				<?php endforeach; ?>

			<?php else:?>

				<?php if (isset($twofactormethods) && count($twofactormethods) > 1): ?>
				<div id="form-login-secretkey" class="form-group">
					<?php if (!$params->get('usetext')) : ?>
					<div class="input-group">
						<span class="input-group-addon">
							<span class="fa fa-star hasTooltip" title="<?php echo Text::_('JGLOBAL_SECRETKEY'); ?>"></span>
						</span>
						<label for="modlgn-secretkey" class="element-invisible"><?php echo Text::_('JGLOBAL_SECRETKEY'); ?></label>
						<input id="modlgn-secretkey" autocomplete="off" type="text" name="secretkey" class="input form-control" tabindex="0" size="18" placeholder="<?php echo Text::_('JGLOBAL_SECRETKEY') ?>" />
						<span class="input-group-addon hasTooltip" title="<?php echo Text::_('JGLOBAL_SECRETKEY_HELP'); ?>">
							<span class="fa fa-question-circle"></span>
						</span>
					</div>
					<?php else: ?>
						<label for="modlgn-secretkey"><?php echo Text::_('JGLOBAL_SECRETKEY') ?></label>
						<input id="modlgn-secretkey" autocomplete="off" type="text" name="secretkey" class="input-small" tabindex="0" size="18" placeholder="<?php echo Text::_('JGLOBAL_SECRETKEY') ?>" />
						<span class="btn btn-default width-auto hasTooltip" title="<?php echo Text::_('JGLOBAL_SECRETKEY_HELP'); ?>">
							<span class="fa fa-question-circle"></span>
						</span>
					<?php endif; ?>
				</div>
				<?php endif; ?>
			<?php endif; ?>
		
			<?php if (PluginHelper::isEnabled('system', 'remember')) : ?>
				<div id="form-login-remember" class="form-group">
					<input id="modlgn-remember" type="checkbox"
							name="remember" class="input"
							value="yes" aria-label="remember"/> <?php echo Text::_('MOD_LOGIN_REMEMBER_ME') ?>
				</div>
			<?php endif; ?>
			<div class="control-group">
				<input type="submit" name="Submit" class="btn btn-primary" value="<?php echo Text::_('JLOGIN') ?>"/>
			</div>

			<?php $usersConfig = ComponentHelper::getParams('com_users'); ?>
			<ul class="unstyled">
				<?php if ($usersConfig->get('allowUserRegistration')) : ?>
				<li>
					<a href="<?php echo Route::_('index.php?option=com_users&view=registration'); ?>">
						<?php echo Text::_('MOD_LOGIN_REGISTER'); ?> <span class="fa fa-arrow-right"></span></a>
				</li>
				<?php endif; ?>
				<li>
					<a href="<?php echo Route::_('index.php?option=com_users&view=remind'); ?>">
						<?php echo Text::_('MOD_LOGIN_FORGOT_YOUR_USERNAME'); ?></a>
				</li>
				<li>
					<a href="<?php echo Route::_('index.php?option=com_users&view=reset'); ?>"><?php echo Text::_('MOD_LOGIN_FORGOT_YOUR_PASSWORD'); ?></a>
				</li>
			</ul>

			<input type="hidden" name="option" value="com_users"/>
			<input type="hidden" name="task" value="user.login"/>
			<input type="hidden" name="return" value="<?php echo $return; ?>"/>
			<?php echo HTMLHelper::_('form.token'); ?>
		</fieldset>
		<?php if ($params->get('posttext')): ?>
			<div class="posttext">
				<p><?php echo $params->get('posttext'); ?></p>
			</div>
		<?php endif; ?>
	</form>
<?php endif; ?>

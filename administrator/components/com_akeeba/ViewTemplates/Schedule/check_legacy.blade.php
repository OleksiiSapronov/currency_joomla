<?php
/**
 * @package   akeebabackup
 * @copyright Copyright (c)2006-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

/** @var \Akeeba\Backup\Admin\View\Schedule\Html $this */

// Protect from unauthorized access
defined('_JEXEC') || die();

?>
<div class="akeeba-panel--information">
    <header class="akeeba-block-header">
        <h3>@lang('COM_AKEEBA_SCHEDULE_LBL_FRONTENDCHECK')</h3>
    </header>

    @if(!$this->checkinfo->info->legacyapi)
        <div class="akeeba-block--failure">
            <p>
                @lang('COM_AKEEBA_SCHEDULE_LBL_LEGACYAPI_DISABLED')
            </p>
        </div>
    @elseif(!trim($this->checkinfo->info->secret))
        <div class="akeeba-block--failure">
            <p>
                @lang('COM_AKEEBA_SCHEDULE_LBL_FRONTEND_SECRET')
            </p>
        </div>
    @else
        <p>
			@lang('COM_AKEEBA_SCHEDULE_LBL_FRONTENDBACKUP_MANYMETHODS')
        </p>

        <h4>
			@lang('COM_AKEEBA_SCHEDULE_LBL_FRONTENDBACKUP_TAB_WEBCRON', true)
        </h4>

        <p>
            @lang('COM_AKEEBA_SCHEDULE_LBL_FRONTEND_WEBCRON')
        </p>

        <table class="akeeba-table--striped" width="100%">
            <tr>
                <td></td>
                <td>
                    @lang('COM_AKEEBA_SCHEDULE_LBL_FRONTEND_WEBCRON_INFO')
                </td>
            </tr>
            <tr>
                <td>
					@lang('COM_AKEEBA_SCHEDULE_LBL_FRONTEND_WEBCRON_NAME')
                </td>
                <td>
					@lang('COM_AKEEBA_SCHEDULE_LBL_FRONTEND_WEBCRON_NAME_INFO')
                </td>
            </tr>
            <tr>
                <td>
					@lang('COM_AKEEBA_SCHEDULE_LBL_FRONTEND_WEBCRON_TIMEOUT')
                </td>
                <td>
					@lang('COM_AKEEBA_SCHEDULE_LBL_FRONTEND_WEBCRON_TIMEOUT_INFO')
                </td>
            </tr>
            <tr>
                <td>
					@lang('COM_AKEEBA_SCHEDULE_LBL_FRONTEND_WEBCRON_URL')
                </td>
                <td>
					{{{ $this->checkinfo->info->root_url }}}/{{{ $this->checkinfo->frontend->path }}}
                </td>
            </tr>
            <tr>
                <td>
					@lang('COM_AKEEBA_SCHEDULE_LBL_FRONTEND_WEBCRON_LOGIN')
                </td>
                <td>
					@lang('COM_AKEEBA_SCHEDULE_LBL_FRONTEND_WEBCRON_LOGINPASSWORD_INFO')
                </td>
            </tr>
            <tr>
                <td>
					@lang('COM_AKEEBA_SCHEDULE_LBL_FRONTEND_WEBCRON_PASSWORD')
                </td>
                <td>
					@lang('COM_AKEEBA_SCHEDULE_LBL_FRONTEND_WEBCRON_LOGINPASSWORD_INFO')
                </td>
            </tr>
            <tr>
                <td>
					@lang('COM_AKEEBA_SCHEDULE_LBL_FRONTEND_WEBCRON_EXECUTIONTIME')
                </td>
                <td>
					@lang('COM_AKEEBA_SCHEDULE_LBL_FRONTEND_WEBCRON_EXECUTIONTIME_INFO')
                </td>
            </tr>
            <tr>
                <td>
					@lang('COM_AKEEBA_SCHEDULE_LBL_FRONTEND_WEBCRON_ALERTS')
                </td>
                <td>
					@lang('COM_AKEEBA_SCHEDULE_LBL_FRONTEND_WEBCRON_ALERTS_INFO')
                </td>
            </tr>
            <tr>
                <td></td>
                <td>
					@lang('COM_AKEEBA_SCHEDULE_LBL_FRONTEND_WEBCRON_THENCLICKSUBMIT')
                </td>
            </tr>
        </table>

        <h4>@lang('COM_AKEEBA_SCHEDULE_LBL_FRONTENDBACKUP_TAB_WGET', true)</h4>

        <p>
			@lang('COM_AKEEBA_SCHEDULE_LBL_FRONTEND_WGET')
            <code>
                wget --max-redirect=10000 "{{{ $this->checkinfo->info->root_url }}}/{{{ $this->checkinfo->frontend->path }}}" -O - 1>/dev/null 2>/dev/null
            </code>
        </p>

        <h4>@lang('COM_AKEEBA_SCHEDULE_LBL_FRONTENDBACKUP_TAB_CURL', true)</h4>

        <p>
			@lang('COM_AKEEBA_SCHEDULE_LBL_FRONTEND_CURL')
            <code>
                curl -L --max-redirs 1000 -v "{{{ $this->checkinfo->info->root_url }}}/{{{ $this->checkinfo->frontend->path }}}" 1>/dev/null 2>/dev/null
            </code>
        </p>

        <h4>@lang('COM_AKEEBA_SCHEDULE_LBL_FRONTENDBACKUP_TAB_SCRIPT', true)</h4>

        <p>
			@lang('COM_AKEEBA_SCHEDULE_LBL_FRONTEND_CUSTOMSCRIPT')
        </p>
        <pre>
&lt;?php
    $curl_handle=curl_init();
    curl_setopt($curl_handle, CURLOPT_URL, '{{{ $this->checkinfo->info->root_url }}}/{{{ $this->checkinfo->frontend->path }}}');
    curl_setopt($curl_handle,CURLOPT_FOLLOWLOCATION, TRUE);
    curl_setopt($curl_handle,CURLOPT_MAXREDIRS, 10000);
    curl_setopt($curl_handle,CURLOPT_RETURNTRANSFER, 1);
    $buffer = curl_exec($curl_handle);
    curl_close($curl_handle);
    if (empty($buffer))
        echo "Sorry, the backup didn't work.";
    else
        echo $buffer;
?&gt;
            </pre>

        <h4>@lang('COM_AKEEBA_SCHEDULE_LBL_FRONTENDBACKUP_TAB_URL', true)</h4>

        <p>
			@lang('COM_AKEEBA_SCHEDULE_LBL_FRONTEND_RAWURL')
            <code>
				{{{ $this->checkinfo->info->root_url }}}/{{{ $this->checkinfo->frontend->path }}}
            </code>
        </p>
    @endif
</div>

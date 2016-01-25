{if null != $tmanager}
<div id="tmanager_popup">
    <h2>Текущий график</h2>
    {foreach from=$days key=day item=name}
        {assign var=freeday value=''}
        {if 0 == $day}
            {assign var=freeday value=freeday}
        {/if}
        {if 6 == $day}
            {assign var=freeday value=freeday}
        {/if}
    <div class="day-row day{$day}">
        <div class="row day-cell name">
            {$name}
        </div>
        <div class="row day-cell">
            {assign var=s value="s`$day`"}
            {if isset($tmanager.$s)}
                {$tmanager.$s|date_format:"H:i"}
            {/if}
        </div>
        <div class="row day-cell">
            {assign var=e value="e`$day`"}
            {if isset($tmanager.$e)}
                {$tmanager.$e|date_format:"H:i"}
            {/if}
        </div>
    </div>
    {/foreach}
</div>
{else}
    <h2>График не задан</h2>
{/if}
        
<div class="filter">
    {$filter->open()}
    <div class="date_widget">
        <div class="mounts">
        {foreach from=$period.mounts item=month}
            {assign var=mstr value="2015-`$month`-01"}
            <div class="mount">
                {$filter->label("month-`$month`", $mstr|date_format:'%B')}
                {$filter->radiobox('month', $month,['class' => 'radio'])}
            </div>
        {/foreach}
        </div>
        <div class="years">
        {foreach from=$period.years item=year}
            <div class="year">
                {$filter->label("year-`$year`", $year)}
                {$filter->radiobox('year', $year,['class' => 'radio'])}
            </div>
        {/foreach}
        </div>
    </div>
    {$filter->security()}
    {$filter->close()}
</div>
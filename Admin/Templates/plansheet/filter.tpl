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
        <div class="period">
            <div class="start">
                {$filter->label('start','С')}
                {$filter->text('start',['class' => 'txt datepicker'])}
            </div>
            <div class="end">
                {$filter->label('end','По')}
                {$filter->text('end',['class' => 'txt datepicker'])}
            </div>
            <div class="buttons">
                {$filter->hidden('allyear')}
                {$filter->submit('Применить', ['class' => 'btn'])}
            </div>
        </div>
    </div>
    {if null}
    <div class="countfilter">
        <div class="cell">
            Показывать
        </div>
        <div class="cell">
            {$filter->select('count_year', $filter->htmlOptions($count_year),['class' => 'select'])}
        </div>
        <div class="cell">
            {$filter->select('count_month', $filter->htmlOptions($count_mouth),['class' => 'select'])}
        </div>
        <div class="cell">
            <span class="show_filter btn" id="show_filter">Показать</span>
        </div>
    </div>
    {/if}
    <div>
        {$filter->hidden('department_id')}
        {$filter->security()}
    </div>
    {$filter->close()}
</div>
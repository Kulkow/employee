    {$filter->open()}
    <div class="filter_form" style="overflow: hidden;">
        <div class="row" style="float: left; width:200px; margin: 0 20px 0 0; clear:none;">
            {$filter->label('start','С')}
            {$filter->text('start',['class' => 'txt datepicker'])}
        </div>
        <div class="row" style="float: left; width:200px; margin: 0 20px 0 0; clear:none;">
            {$filter->label('end','По')}
            {$filter->text('end',['class' => 'txt datepicker'])}
        </div>
        <div class="row" style="float: left; width:200px; margin: 0 20px 0 0; clear:none; padding: 15px 0 0 0;">
            {$filter->submit('Фильтр', ['class' => 'btn'])}
        </div>
    </div>
    <div>
    {$filter->security()}
    </div>
    {$filter->close()}

<br>
<div class="logs" style="width:600px;">
    <table class="table">
        <tr>
            <th width="70px">Расход</th>
            <th width="70px">Создан</th>
            <th width="70px">Проведен</th>
            <th>Сотрудник</th>
            <th width="70px">Аванс</th>
            <th width="70px">ЗП</th>
            <th width="70px">за месяц</th>
        </tr>
    {foreach from=$logs item=expense}
        {assign var=index value=0}
        {assign var=count value=$expense.logs|@count}
        {if 0 < $count }
            {foreach from=$expense.logs item=log}
            <tr>
                {if 0 == $index}
                    <td rowspan="{$count}">
                        <p>{$expense.text} [{$expense.expense_id}]</p>
                    </td>
                    <td rowspan="{$count}"><span title="{$expense.creation_date|date_format:"H:i"}">{$expense.creation_date|date_format:"d.m.Y"}</td>
                    <td rowspan="{$count}"><span title="{$expense.approval_date|date_format:"H:i"}">{$expense.approval_date|date_format:"d.m.Y"}</span></td>
                {/if}
                <td>{$log.name}</td>
                <td>{$log.avans|price_format}</td>
                <td>{$log.out|price_format}</td>
                <td>{$log.date|date_format:"d.m.Y"}</td>
            </tr>
            {assign var=index value=index+1}
            {/foreach}
        {/if}
    {/foreach}
    </table>
</div>
{literal}
    <script>
        $(function(){
            $('.datepicker').datepicker();
        })
    </script>
{/literal}
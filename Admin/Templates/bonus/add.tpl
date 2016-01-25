{$form->open('/admin/employee/bonus/add')}
<div class="row inline variables">
    {foreach from=$valiebles key=variable item=key}
        <div class="btn {if $key>0}red{else}blue{/if} variable">{$variable}</div>
    {/foreach}
</div>
<div class="row inline">
    <div class="row inline" style="float:left; clear:none;">
        {$form->label('type', 'Причина')}
        {$form->text('type', ['class' => 'txt'])}
    </div>
    <div class="row inline" style="float:left; clear:none;">
        {$form->label('amount', 'Сумма')}
        {$form->text('amount', ['class' => 'txt price'])}
    </div>
    <div class="row inline" style="float:left; clear:none;">
        {$form->submit('Добавить', ['class' => 'btn green'])}
    </div>
</div>
<div>
    {$form->hidden('manager_id')}
    {$form->hidden('id')}
    {$form->security()}
</div>
{$form->close()}

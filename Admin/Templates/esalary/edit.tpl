<div class="employee-form">
{if null != $esalary}
    {$form->open('/admin/employee/esalary/edit')}
{else}
    {$form->open("/admin/employee/esalary/user/`$user_id`")}
{/if}
    <div class="row">
        {$form->label('base', 'Ставка', true)}
        {$form->text('base', ['class' => 'txt', "style" => "width:100px"])}
    </div>
    <div class="row">
        {$form->label('start', 'Действует с', true)}
        {$form->text('start', ['class' => 'txt datepicker', "style" => "width:100px"])}
    </div>
    {if null != $esalary}
    <div class="row">
        {$form->label('new_start', 'Начало действия новой ставки')}
        {$form->text('new_start', ['class' => 'txt datepicker', "style" => "width:100px"])}
    </div>
    {/if}
    <div>
        {$form->hidden('user_id')}
        {$form->hidden('id')}
        {$form->security()}
    </div>
    {$form->submit('Сохранить', ['class' => 'btn'])}
{$form->close()}
</div>
{literal}
<script>
    $(function(){
        $('.datepicker').datepicker();
    })
</script>
{/literal}
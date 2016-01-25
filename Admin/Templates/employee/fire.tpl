<div>
    {$form->open("/admin/employee/employee/fire/`$employee.id`")}
    <div class="row">
        {$form->label('fire_date', 'Дата увольнения')}
        {$form->text('fire_date', ['class' => 'txt datepicker'])}
    </div>
    <div class="row">
        {$form->label('expire_date', 'До какого дня показывать в списке')}
        {$form->text('expire_date', ['class' => 'txt datepicker'])}
    </div>
    <div class="row">
        {$form->label('close', 'с этого числа больше не будут учитываться в общих планах', false, ['title' => "не будут учитываться в общих планах"])}
        {$form->checkbox('close')}
    </div>
    <div>
    {$form->hidden('user_id')}
    {$form->security()}
    </div>
    <div>
    {$form->submit('Уволить', ['class' => 'btn'])}
    {$form->close()}
    </div>
</div>
{literal}
<script>
    $(function(){
        $('.datepicker').datepicker();
    })
</script>
{/literal}
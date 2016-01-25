<div class="employee-form">
    {if $bonus == null}
        {$form->open('/admin/employee/bonus/add')}
    {else}
        {$form->open('/admin/employee/bonus/edit')}
    {/if}
    <div class="row inline">
        {$form->label('type', 'Причина')}
        {$form->text('type', ['class' => 'txt'])}
    </div>
    <div class="row inline">
        {$form->label('amount', 'Сумма')}
        {$form->text('amount', ['class' => 'txt price'])}
    </div>
    {if false != $is_rule}
    <div class="row inline">
        {$form->label('date', 'Дата')}
        {$form->text('date', ['class' => 'txt datepick-bonus'])}
    </div>
    {/if}
    <div class="row inline">
        {$form->label('comment', 'Комментарий')}
        {$form->textarea('comment', ['class' => 'txt price'])}
    </div>
    <div class="row inline">
        {$form->submit('Сохранить', ['class' => 'btn'])}
    </iv>
    <div>
        {$form->hidden('manager_id')}
        {$form->hidden('creator_id')}
        {$form->hidden('salary_id')}
        {$form->hidden('id')}
        {$form->security()}
    </div>
    {$form->close()}
</div>
<script>
$(function() {
    $('#EmployeeBonusEdit').form({
        submit: function(response) {
            if (!response.errors){
                console.log(response);
                //window.location.reload();
            }else{
                console.log(response.errors);
            }
        }
    });
    /*$('.datepick-bonus').datepicker();*/
});
</script>
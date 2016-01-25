<div class="employee-form">
    {if null == $plan}
        {$form->open("/admin/employee/planorg/add/")}
    {else}
        {$form->open("/admin/employee/planorg/edit/`$plan.id`")}
    {/if}
    <div class="row inline">
        {$form->label('profit', 'Баланс')}
        {$form->text('profit', ['class' => 'txt price'])}
    </div>
    <div class="row inline">
        {$form->label('date', 'Дата')}
        {$form->text('date', ['class' => 'txt datepicker'])}
    </div>
    <div>
        {$form->hidden('owner_id')}
        {$form->hidden('id')}
        {$form->security()}
    </div>
    <div>
        {$form->submit('Сохранить', ['class' => 'btn'])}
    </div>
    {$form->close()}
</div>
{literal}
<script>
$(function() {
    $('.datepicker').datepicker();
    $('#EmployeePlanEdit').form({
        submit: function(response) {
            if (!response.errors){
                console.log(response);
            }else{
                console.log(response.errors);
            }
        }
    });
});
</script>
{/literal}
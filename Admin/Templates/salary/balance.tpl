{$form->open("/admin/employee/salary/balance/`$_id`")}
<div class="row">
    {$form->label('balance', 'Деньги')}
    {$form->text('balance')}
</div>
<div class="row">
    {$form->submit('Сохранить', ['class' => 'btn'])}
</div>
<div>
{$form->security()}
{$form->hidden('id')}
</div>
{$form->close()}
{literal}
<script>
    $(function(){
        $('#SalaryBalance').form({
            submit: function(response) {
                if (!response.errors){
                    console.log(response);
                }else{
                    console.log(response.errors);
                }
            }
        })
    })
</script>
{/literal}
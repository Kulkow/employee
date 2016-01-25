{$form->open("/admin/employee/salary/vax/`$_id`")}
<div class="row">
    {$form->label('vax', 'Налог')}
    {$form->text('vax')}
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
        $('#SalaryVax').form({
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
{$form->open("/admin/employee/salary/avans/`$_id`")}
<div class="row">
    {$form->label('avans', 'Аванс')}
    {$form->text('avans')}
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
        $('#SalaryAvans').form({
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
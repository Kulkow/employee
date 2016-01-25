{$form->open("/admin/employee/salary/edit/`$_id`")}
<div class="row">
    {$form->label('balance', 'Осталось выдать')}
    <span id="SalaryEdit-balance">{$form->getData('balance')}</span>
</div>
<div class="row">
    {$form->label('out', 'Выдано')}
    {$form->text('out', ['class' => 'txt', 'style' => 'width:200px'])}
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
        $('#SalaryEdit').form({
            submit: function(response) {
                if (!response.errors){
                    //console.log(response);
                }else{
                    console.log(response.errors);
                }
            }
        })
    })
</script>
{/literal}
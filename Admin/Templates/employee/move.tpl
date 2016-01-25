{$form->open()}
<div class="row">
    {foreach from=$edepartments item=department}
        {if $department_id == $department.department_id}
            <p>Перевести из подразделения: {$department.department}</p>
        {/if}
    {/foreach}
</div>
<div class="row">
    {$form->label('start', 'с какого дня')}
    {$form->text('start', ['class' => 'txt datepicker', 'style' => 'width:150px'])}
</div>
<div class="row">
    {$form->label('move_id', 'Перевести в подразделение')}
    {$form->select('move_id', [0 => '--'] + $form->htmlOptions($departments), ['class' => 'select'])}
</div>
<div class="row">
    {$form->submit('Перевести', ['class' => 'btn'])}
</div>
<div>
{$form->hidden('user_id')}
{$form->hidden('department_id')}
{$form->security()}
{$form->close()}
</div>
{literal}
<script>
$(function(){
    $('.datepicker').datepicker();
    $('#EmployeeMove').form({
        submit: function(response) {
            if (!response.errors){
                console.log(response);
                /*window.location.reload();*/
            }else{
                console.log(response.errors);
            }
        }
    });
})
</script>
{/literal}
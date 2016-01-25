{$form->open()}
<div class="row">
{$form->label('department_id', 'Добавить в подразделение')}
{$form->select('department_id', $form->htmlOptions($departments), ['class' => 'select'])}
</div>
<div class="row">
{$form->label('start', 'с какого дня')}
{$form->text('start', ['class' => 'txt datepicker', 'style' => 'width:150px'])}
</div>
<div class="row">
{$form->submit('Добавить', ['class' => 'btn'])}
</div>
<div>
{$form->hidden('user_id')}
{$form->security()}
{$form->close()}
</div>
{literal}
<script>
$(function(){
    $('.datepicker').datepicker();
    $('#EmployeeAdd').form({
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
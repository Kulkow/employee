{if isset($department)}
    <h2>{$department.name}</h2>
{/if}
{$form->open('/admin/employee/employee/remove/')}
<div class="row">
{$form->label('end', 'с какого дня')}
{$form->text('end', ['class' => 'txt datepicker', 'style' => 'width:150px'])}
</div>
<div class="row">
{$form->submit('Вывести', ['class' => 'btn'])}
</div>
<div>
{$form->hidden('id')}
{$form->security()}
{$form->close()}
</div>
{literal}
<script>
$(function(){
    $('.datepicker').datepicker();
    $('#EmployeeRemove').form({
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
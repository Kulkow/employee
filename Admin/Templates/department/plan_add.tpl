{$form->open('/admin/employee/department/addplan')}
<div class="row inline">
    {$form->select('plan_id',$form->htmlOptions($plans) ,['class' => 'select', 'style' => 'width:200px'])}
    {$form->submit('Добавить Показатель', ['class' => 'btn'])}
</div>
<div>
    {$form->hidden('department_id')}
    {$form->security()}
</div>
{$form->close()}
<script>
$(function() {
    $('#DepartmentPlanAdd').form({
        submit: function(response) {
            if (!response.errors)
                window.location.reload();
        }
    });
});
</script>
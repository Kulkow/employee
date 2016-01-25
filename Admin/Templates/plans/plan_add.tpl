{$form->open('/admin/employee/plan/addplan')}
    <div class="row inline">
        {$form->select('plan_pid',$form->htmlOptions($plans) ,['class' => 'select', 'style' => 'width:200px'])}
        {$form->submit('Добавить Показатель', ['class' => 'btn'])}
    </div>
    <div>
        {$form->hidden('plan_id')}
        {$form->security()}
    </div>
{$form->close()}
<script>
$(function() {
    $('#PlanPlanAdd').form({
        submit: function(response) {
            if (!response.errors)
                window.location.reload();
        }
    });
});
</script>
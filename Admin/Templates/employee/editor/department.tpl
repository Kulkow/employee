{$form->open("/admin/employee/employee/updatedepartment/`$user_id`")}
<div class="row">
    {$form->label('department_id', 'Подразделение')}
    {$form->select('department_id', [0 => '--'] + $form->htmlOptions($departments), ['class' => 'select'])}
</div>
{$form->hidden('user_id')}
{$form->security()}
{$form->close()}
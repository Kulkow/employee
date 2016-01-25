<h2>{$employee.name}</h2>
<div class="employee-form">
    {$form->open("/admin/employee/employee/edit/`$employee.id`")}
    <div class="row">
        {$form->label('number', 'Номер в системе')}</td>
        {$form->text('number', ['class' => 'txt', 'style' => "width:50px"])}
    </div>
    <div class="row">
        {$form->label('department_id', 'Подразделение')}
        {$form->select('department_id', [0 => '--'] + $form->htmlOptions($departments), ['class' => 'select'])}
    </div>
    <div class="row">
        {$form->label('status', 'Статус')}
        {$form->select('status',$form->htmlOptions($statuses),[])}
    </div>
    <div class="row">
        {$form->label('phone', 'Телефон')}
        {$form->text('phone', ['class' => 'txt', 'style' => "width:100px"])}
    </div>
    <div class="row">
        {$form->label('skype', 'Skype')}
        {$form->text('skype', ['class' => 'txt', 'style' => "width:100px"])}
    </div>
    <div class="row">
        {$form->label('is_vax', 'Налоги')}
        {$form->checkbox('is_vax', ['class' => 'checkbox'])}
    </div>
    <div>
    {$form->hidden('user_id')}
    {$form->security()}
    </div>
    {$form->submit('Сохранить', ['class' => 'btn'])}
    {$form->close()}
</div>
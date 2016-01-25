<div class="employee-form">
    {$form->open("/admin/employee/bonus/request/`$id`")}
    <div class="row inline">
        {$form->textarea('comment_user', ['class' => 'area txt', 'style' => "width:100%"])}
    </div>
    <div class="row inline">
        {$form->submit('Отправить', ['class' => 'btn'])}
    </iv>
    <div>
        {$form->hidden('id')}
        {$form->security()}
    </div>
    {$form->close()}
</div>

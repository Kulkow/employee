{if null != $user_id}
    {$form->open("/admin/employee/employee/edit/`$user_id`")}
        <div class="row">
            {$form->label('name', 'ФИО')}
            {$form->text('name', ['class' => 'txt', 'style' => 'width:500px'])}
        </div>
        <div>
            {$form->security()}
            {$form->hidden('user_id')}
            {$form->hidden('department_id')}
            {$form->hidden('number')}
            {$form->hidden('is_vax')}
            {$form->submit('Сохранить', ['class' => 'btn submit'])}
        </div>
    {$form->close()}
    <div>
        <ul>
            <li><b>Email</b>: {$employee.email}</li>
            <li><b>Пароль</b>: {$employee.password}</li>
            <li><b>Личный телефон</b>: {$employee.personal_phone}</li>
            <li><b>Рабочий телефон</b>: {if isset($econtacts.phone)}{$econtacts.phone}{/if}</li>
        </ul>
    </div>
{/if}
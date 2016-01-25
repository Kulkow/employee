<h2>Задать пароль для личного кабинета</h2>
<div class="employee-form" style="width:300px">
    {$form->open()}
    <div class="row">
        {$form->label('password', 'Пароль', true)}
        {$form->password('password', ['class' => 'txt'])}
        <span class="error">{$form->error('password')}</span>
    </div>
    <div>
        {$form->security()}
        {$form->hidden('user_id')}
        {$form->submit('Задать', ['class' => 'btn'])}
    </div>
    <div>
        <a href="/admin/employee/cabinet/" title="Войти в кабинет">Войти в кабинет</a>
    </div>
    {$form->close()}
</div>
<h2>Войти в личный кабинет</h2>
<div class="employee-form" style="width:300px">
    {$form->open('/admin/employee/cabinet/','post',['autocomplete' => 'off'])}
    <div class="row">
        {$form->label('password', 'Пароль', true)}
        {$form->password('password', ['class' => 'txt', 'autocomplete' => 'nope', 'placeholder' => "Введите пароль"])}
        <span class="error">{$form->error('password')}</span>
    </div>
    <div>
        {$form->security()}
        {$form->submit('Войти', ['class' => 'btn'])}
    </div>
    <div>
        <a href="/admin/employee/cabinet/edit/" title="Создать пароль">Создать пароль</a>
    </div>
    {$form->close()}
</div>
{literal}
<script>
    $(function(){
        setTimeout(function(){
            $('#Login-password').val('');
        },200);
    })
</script>
{/literal}
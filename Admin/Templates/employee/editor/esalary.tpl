<div class="wrapper">
    <label for="Employee-is-vax">Есть налог</label>
    <input type="checkbox" id="Employee-is-vax" name="is_vax" value="1" {if 1 == $employee.is_vax}checked="checked"{/if}  />
</div>
<div class="wrapper form-block" style="width:350px;">
    {if null != $esalary}
        {$form->open('/admin/employee/esalary/edit')}
    {else}
        {$form->open("/admin/employee/esalary/user/`$user_id`")}
    {/if}
        <div class="row txt-row">
            {$form->label('base', 'Ставка', true)}
            {$form->text('base', ['class' => 'txt', "style" => "width:100px"])}
        </div>
        <div class="row txt-row">
            {$form->label('start', 'Действует с', true)}
            {$form->text('start', ['class' => 'txt datepicker', "style" => "width:100px"])}
        </div>
        {if null != $esalary}
        <div class="row txt-row">
            {$form->label('new_start', 'Начало действия новой ставки')}
            {$form->text('new_start', ['class' => 'txt datepicker', "style" => "width:100px"])}
        </div>
        {/if}
        <div>
            {$form->hidden('user_id')}
            {$form->hidden('id')}
            {$form->security()}
        </div>
        {$form->submit('Сохранить', ['class' => 'btn submit'])}
    {$form->close()}
</div>

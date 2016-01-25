<div class="employee-form">
    {if null == $plan}
        {assign var=user_id value=$form->getData('user_id')}
        {$form->open("/admin/employee/eplan/add/`$user_id`")}
    {else}
        {$form->open('/admin/employee/eplan/edit')}
    {/if}
    {if null == $plan}
        <div class="row inline">
            {$form->label('plan_id', 'Показатель')}
            <select name="EmployeePlanEdit[plan_id]" id="EmployeePlanEdit-plan_id" style="width:200px">
                <option value="0">--</option>
                {foreach from=$plans item=_plan_group}
                    <optgroup label="{$_plan_group.name}">
                    {foreach from=$_plan_group.items item=_plan}
                        <option value="{$_plan.id}">{$_plan.name}</option>
                    {/foreach}
                    </optgroup>
                {/foreach}
            </select>
        </div>
    {else}
        <div class="row inline">
            <h3>{$plan.plan}</h3>
            {$form->hidden('plan_id')}
        </div>
    {/if}
    <div class="row inline _value">
        {if null == $help}
            {$form->label('value', 'Процент влияния')}
        {else}
            {$form->label('value', $help.label)}
        {/if}
        {$form->text('value', ['class' => 'txt'])}
        <span class="prefix">
            {if null != $help}
                {$help.prefix}
            {/if}
        </span>
    </div>
    <div class="row inline">
        {$form->label('start', 'Действует с')}
        {$form->text('start', ['class' => 'txt datepicker'])}
    </div>
    {if null != $plan.end}
        <p>Не дейсвует с {$plan.end}</p>
        <div class="row inline">
            {$form->label('restore', "восстановить")}
            {$form->checkbox('restore')}
        </div>
    {/if}
    {if null != $plan}
        <div class="row inline">
            {$form->label('new_start', 'Начало действия новой ставки')}
            {$form->text('new_start', ['class' => 'txt datepicker'])}
        </div>
    {/if}
    <div class="row inline {if null == $plan or 0 == $plan.is_common}hidden{/if}">
        {$form->label('department_id', 'Подразделение')}
        {$form->select('department_id', [0 => '--']+ $form->htmlOptions($departments), ['style' => "width:200px"])}
    </div>
    <div>
        {$form->hidden('user_id')}
        {$form->hidden('id')}
        {$form->security()}
    </div>
    <div>
        {$form->submit('Сохранить план', ['class' => 'btn'])}
    </div>
    {$form->close()}
</div>
<script>
$(function() {
    $('.datepicker').datepicker();
    $('#EmployeePlanEdit').form({
        submit: function(response) {
            if (!response.errors){
                console.log(response);
            }else{
                console.log(response.errors);
            }
        }
    });
    $('#EmployeePlanEdit-plan_id').change(function(){
        var value = this.value, _form = $(this).closest('form'), url = window.location.protocol + '//' + window.location.hostname + '/admin/employee/eplan/help/'+value;
        var _row = $('._value',_form);
        $.get(url, function(response){
            if (response.help) {
                $('label', _row).text(response.help.label);
                $('.prefix', _row).text(response.help.prefix);
                var _department_select = $('#EmployeePlanEdit-department_id'), _department_row = _department_select.closest('.row');
                if (0 < response.help.is_common) {
                    _department_select.prop("disabled", false );
                    _department_row.removeClass('hidden');
                }else{
                    _department_select.prop("disabled", true);
                    _department_select.val(0);
                    _department_row.addClass('hidden');
                }
            }
        })
    })
});
</script>
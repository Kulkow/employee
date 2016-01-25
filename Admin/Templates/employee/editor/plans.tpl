<div style="margin:0 0 10px 0; width:500px;" class="form-block">
    {assign var=help value=null}
    {$form->open("/admin/employee/eplan/add/`$user_id`")}
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
        <div class="row txt-row inline _value">
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
        <div class="row txt-row inline">
            {$form->label('start', 'Действует с')}
            {$form->text('start', ['class' => 'txt datepicker'])}
        </div>
        <div class="row hidden txt-row inline">
            {$form->label('department_id', 'Подразделение')}
            {$form->select('department_id', [0 => '--']+ $form->htmlOptions($departments), ['style' => "width:200px", 'disabled' => true])}
        </div>
        <div>
            {$form->hidden('user_id')}
            {$form->security()}
        </div>
        <div>
            {$form->submit('Добавить показатель', ['class' => 'btn submit'])}
        </div>
    {$form->close()}
</div>

</div> <!-- End Wrapper FORM -->

<table class="table">
    <tr>
        <th>Показатели</th>
		<th width="70px">Влияние/ставка</th>
        <th></th>
    </tr>
    {foreach from=$eplans item=_plan}
        <tr class="it-plan plan_indicator_{$_plan.id}">
            <td>
                {$_plan.name}
				{if 0 < $_plan.department_id}
					{assign var=did value=$_plan.department_id }
					{if isset($edepartments.$did)}
						[{$edepartments.$did.name}]
					{else}
						[{$_plan.department_id}]
					{/if}
				{/if}
            </td>
    		<td>
                {if $_plan.is_plan_based}
                    {$_plan.value|round} %
                {else}
                    {if 0 == $_plan.is_discrete}
						{$_plan.value*100} %
					{else}
						{$_plan.value|price_format} за {$_plan.measurement}
					{/if}
                {/if}
            </td>
            <td>
                <a href="/admin/employee/eplan/edit/{$_plan.id}" title="Редактировать" class="icon-edit"></a>
				{if null == $_plan.end}
					<a href="/admin/employee/eplan/remove/{$_plan.id}" title="Прекратить действие с текущего дня" class="plan-close icon-closelock"></a>
				{/if}
				<a href="/admin/employee/eplan/clear/{$_plan.id}" title="Удалить" class="plan-remove icon-delete"></a>
            </td>
        </tr>
    {/foreach}
</table>
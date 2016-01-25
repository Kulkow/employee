<tr class="it-plan plan_indicator_{$plan.id}">
    <td>
        {$plan.name}
        {if 0 < $plan.department_id}
            {assign var=did value=$plan.department_id }
            {if isset($edepartments.$did)}
                [{$edepartments.$did.name}]
            {else}
                [{$plan.department_id}]
            {/if}
        {/if}
    </td>
    <td>
        {if $plan.is_plan_based}
            {$plan.value|round} %
        {else}
            {if 0 == $plan.is_discrete}
                {$plan.value*100} %
            {else}
                {$plan.value|price_format} за {$plan.measurement}
            {/if}
        {/if}
    </td>
    <td>
        <a href="/admin/employee/eplan/edit/{$plan.id}" title="Редактировать" class="icon-edit"></a>
        {if null == $plan.end}
            <a href="/admin/employee/eplan/remove/{$_plan.id}" title="Прекратить действие с текущего дня" class="plan-close icon-closelock"></a>
        {/if}
        <a href="/admin/employee/eplan/clear/{$_plan.id}" title="Прекратить действие с текущего дня" class="plan-remove icon-delete"></a>
    </td>
</tr>
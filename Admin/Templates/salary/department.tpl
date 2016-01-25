<h1>Зарплаты подразделения "{$department.name}"</h1>
<p></p>

{foreach from=$salarys item=salary}
<div class="wrap">
    <h3>{$salary.esalary.name}</h3>
    <div class="esalary">
        <h4>Ставка</h4>
        <ul>
            <li>Базовая ставка {$salary.esalary.base|price_format}</li>
            <li>Ставка на испытательный срок {$salary.esalary.learning|price_format}</li>
        </ul>
    </div>
    <table class="table">
        <tr>
            <th>Показатель</th>
            <th>План</th>
            <th>Текущее</th>
            <th>Влияние на план</th>
        </tr>
        {foreach from=$salary.salary item=indentifier}
        <tr>
            <td>{$indentifier.name}</td>
            <td>
            {if $indentifier.is_plan_based == 1}
                {$indentifier.plan|price_format}
            {/if}
            </td>
            <td>
            {if $indentifier.is_plan_based == 0}
                {$indentifier.total|price_format}
            {else}
                {$indentifier.fact|price_format}
            {/if}
            </td>
            <td>
                {if $indentifier.is_plan_based == 1}
                    {$indentifier.value|round}%
                {/if}
            </td>
        </tr>
        {/foreach}
        <tr>
            <td></td>
            <td>При 100% - выполнении плана <b>{$salary.max|price_format}</b></td>
            <td>Итого:<b>{$salary.total|price_format}</b></td>
            <td></td>
        </tr>
    </table>
</div>
{/foreach}
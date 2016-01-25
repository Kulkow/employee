<h1>Бонусы и депремирования пользователя</h1>
<div class="list">
    <table class="table" id="bonus">
        <tr>
            <th>Дата</th>
			<th></th>
            <th>Сумма</th>
			<th>Коммент</th>
            <th></th>
        </tr>
        {foreach from=$bonuses item=bonus}
        <tr class="it-bonus">
			<td>{$bonus.date}</td>
            <td>{$bonus.type}</td>
            <td>{$bonus.amount|price_format}</td>
            <td>{$bonus.comment}</td>
            <td class="actions">
                <a href="/admin/employee/bonus/up/{$bonus.id}" class="icon-play" title="перенести на следующий месяц"></a>
                <a href="/admin/employee/bonus/remove/{$bonus.id}" class="icon-delete" title="Удалить"></a>
            </td>
        </tr>
        {/foreach}
    </table>
<a href="/admin/employee/bonus/add/{$user_id}" title="Добавить Бонус или депремировать" class="btn icon-add">Добавить бонус</a>
</div>
{literal}
<script>
    $(function(){
        $('#bonus .icon-edit')
    })
</script>
{/literal}
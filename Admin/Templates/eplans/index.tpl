<h1>Зарплатные планы пользователей</h1>
<div class="list" style="width:800px;">
    <table class="table" id="plans" data-user="{$user_id}">
        <tr>
            <th></th>
			<th>План</th>
            <th>Процент/ставка</th>
			<th></th>
            <th>Дата изменения</th>
            <th></th>
        </tr>
        {foreach from=$plans item=plan}
        <tr class="it-plan">
			<td>{$plan.alias}</td>
            <td>{$plan.plan}</td>
            <td>
                {if $plan.plan_id == 10}
                    {$plan.value|price_format}
                {else}
                    {$plan.value}
                {/if}
                {$plan.measurement}
			</td>
			<td>
				{if $plan.is_plan_based == 1}Имеет план{/if}
				{if $plan.is_common == 1}<br />Общий{/if}
			</td>
            <td>{$plan.updated|date_format}</td>
            <td class="actions">
                <a href="/admin/employee/eplan/edit/{$plan.id}" class="icon-edit" title="Изменить"></a>
                <a href="/admin/employee/eplan/remove/{$plan.id}" class="icon-delete" title="Удалить"></a>
            </td>
        </tr>
        {/foreach}
    </table>
<a href="/admin/employee/eplan/add/{$user_id}" title="Добавить показатель пользователю" class="btn icon-add">Добавить показатель</a>
</div>
<table class="table" id="esalary" style="width:800px">
	<tr>
		<th>Базовые показатели</th>
		<th></th>
	</tr>
	<tr>
		<td>Базовая ставка</td>
		<td>{$esalary.base|price_format}</td>
	</tr>
</table>	
<table class="table" id="salarys"  style="width:800px">
	<tr>
		<th></th>
		<th>План</th>
		<th>Фактические</th>
		<th>По плану</th>
		<th></th>
	</tr>
	{foreach from=$salary.salary item=salar}
	<tr class="it-plan">
		<td>{$salar.alias}</td>
		<td>{$salar.name}</td>
		<td>
			{$salar.fact|price_format}
		</td>
		<td>
			{$salar.plan|price_format}
		</td>
		<td>
			{if 0 !== $salar.plan}
				{(($salar.fact/$salar.plan)*100)|round}
			{/if}
		</td>
	</tr>
	{/foreach}
</table>
<div>{$salary.total|price_format}</div>
<div>{$salary.max|price_format}</div>
{literal}
<script>
    $(function(){
        $('.it-plan .icon-edit, .icon-add').click(function(){
		var a = $(this), id = a.data('id'), _url = a.attr('href');
            a.ajaxpopup({
                form: {
                    submit: function(response) {
                        location.reload();
                    }
                },
                title: 'Изменить ',
                url: _url,
                dialog: {
                    minWidth: 700,
                    width: 700
                },
            });
            return !1;
        })
        $('.it-plan .icon-delete').click(function(){
            var tr = $(this).closest('tr');
            $.get(this.href, function(data){
                tr.remove();
            })
            return !1;
        })
    })
</script>
{/literal}
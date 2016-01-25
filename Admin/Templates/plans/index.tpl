{include file='menu.tpl'}
<h1>Зарплатные показатели пользователей</h1>
<div class="list" style="width:800px;">
    <table class="table" id="plans">
        <tr>
            <th>Показатель</th>
			<th>measurement</th>
            <th>Положительная / отрицательная динамика</th>
            <th width="50px">Зависит от плана</th>
            <th width="50px">Персональные или для подразделения</th>
            <th width="50px"></th>
        </tr>
        {foreach from=$plans item=plan}
        <tr class="it-plan">
            <td>
				<b>{$plan.id}</b> {$plan.name}
				{if 0 == $plan.is_plan_based}
					{if 0 == $plan.is_discrete}
					 - %
					{/if}
				{/if}
				<div class="hidden">
					{$plan.alias}-{$plan.id} - {$plan.is_discrete}
				</div>
			</td>
            <td>{$plan.measurement}</td>
            <td>{if $plan.is_negative}-{else}+{/if}</td>
            <td>{if 1 == $plan.is_plan_based}+{else}-{/if}</td>
            <td>
				{if 1 == $plan.is_plan_based}
					{if 1 == $plan.is_common}
						Общий
						{if 0 < $plan.pid}
							{assign var=pid value=$plan.pid}
							({$plan.pid})
						{/if}
					{else}
						Персональный
					{/if}
					
				{else}
					-
				{/if}
			</td>
            <td class="actions">
                <a href="/admin/employee/plan/edit/{$plan.id}" class="icon-edit" title="Изменить"></a>
                <a href="/admin/employee/plan/remove/{$plan.id}" class="icon-delete" title="Удалить"></a>
            </td>
        </tr>
        {/foreach}
    </table>
<a href="/admin/employee/plan/add/" title="Добавить показатель" class="btn icon-add">Добавить показатель</a>
</div>
{literal}
<script>
    $(function(){
        $('.icon-add').click(function(){
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
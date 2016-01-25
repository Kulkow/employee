<div style="overflow: hidden;margin: 0 0 10px 0;">
	<a href="/admin/employee/eplan/add/{$user_id}" class="icon-add btn" style="float: right;" title="Добавить">Добавить</a>
</div>
<table class="table" id="plans" data-user="{$user_id}">
    <tr>
        <th>План</th>
        <th>Процент/ставка</th>
        <th></th>
    </tr>
    {foreach from=$plans item=plan}
    <tr class="it-plan">
        <td>
            {if $plan.is_common == 1}
                <b>{$plan.name}</b>
            {else}
                {$plan.name}
            {/if}
        </td>
        <td>
            {if $plan.is_plan_based == 0}
                {$plan.value|price_format}
                {$plan.measurement}
            {else}
                {$plan.value|round}%
            {/if}
        </td>
        <td class="actions">
            <a href="/admin/employee/eplan/edit/{$plan.id}" class="icon-edit" title="Изменить"></a>
            <a href="/admin/employee/eplan/remove/{$plan.id}" class="icon-delete" title="Удалить"></a>
        </td>
    </tr>
    {/foreach}
</table>
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
                    minWidth: 350,
                    width: 350
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
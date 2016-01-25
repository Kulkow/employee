<h1>Бонусы и депремирования пользователя</h1>
<div class="list" style="width:100%;">
	<div class="wrapper">
		{$app->forward('/admin/employee/bonus/add/', ['id' => $user_id])}
	</div>
    <table class="exceltable" id="bonus">
        <tr>
            <th>Дата</th>
			<th></th>
            <th width="100px">Сумма</th>
            <th width="70px"></th>
        </tr>
        {foreach from=$bonuses item=bonus}
        <tr class="it-bonus status{if 0 == $bonus.creator_id}1{else}{$bonus.is_approved}{/if}">
			<td>
				{$bonus.date}
				{if 0 < $bonus.creator_id}
				<br /><i>{$bonus.creater_name}</i>
				{/if}
			</td>
            <td class="tdtype">
				<p class="name">
					{$bonus.type}
				</p>
				<i>{$bonus.comment}</i>
			</td>
            <td>
				{if $bonus.amount > 0}
					<p class="icon-up">{$bonus.amount|price_format}</p>
				{else}
					<p class="icon-down">{$bonus.amount|price_format}</p>
				{/if}
			</td>
            <td class="actions">
				{if 1 == $bonus.approved}
					<a href="/admin/employee/bonus/remove/{$bonus.id}/" class="icon-delete" title="Отменить"></a>
				{else}
					{if null !== $is_rule}
						<a href="/admin/employee/bonus/approved/{$bonus.id}" class="icon-add" title="Одобрить"></a>
					{else}
						{if $auth_user == $bonus.creator_id}
						<a href="/admin/employee/bonus/remove/{$bonus.id}" class="icon-delete" title="Отменить"></a>
						{/if}
					{/if}
					<a href="/admin/employee/bonus/up/{$bonus.id}" class="icon-play" title="перенести на следующий месяц"></a>
				{/if}
            </td>
        </tr>
        {/foreach}
    </table>
</div>
{literal}
<script>
	$(function(){
		$('.it-bonus .icon-edit').click(function(){
			var a = $(this), id = a.data('id'), _url = a.attr('href');
			a.ajaxpopup({
                form: {
                    submit: function(response) {
						location.reload();
                    }
                },
                title: '',
                url: _url,
                dialog: {
                    minWidth: 700,
                    width: 700
                },
            });
			return !1;
		})
		$('.it-bonus .icon-add').click(function(){
			var a = $(this), id = a.data('id'), _url = a.attr('href'), tr = a.closest('tr');
			$.get(_url,function(json){
				if (json.errors) {
					alert(json.errors);
				}
				else{
					tr.attr('class', 'it-bonus status0');
				}
			});
			return !1;
		})
		$('.it-bonus .icon-delete').click(function(){
			var a = $(this), id = a.data('id'), _url = a.attr('href'), tr = a.closest('tr');
			$.get(_url,function(json){
				if (json.errors) {
					alert(json.errors);
				}
				else{
					tr.attr('class', 'it-bonus status0');
				}
			});
			return !1;
		})
		$('.it-bonus .icon-play').click(function(){
			var a = $(this), id = a.data('id'), _url = a.attr('href'), tr = a.closest('tr');
			$.get(_url,function(json){
				if (json.errors) {
					alert(json.errors);
				}
				else{
					tr.remove();
				}
			});
			return !1;
		})
		$('#BonusEdit').form({
			submit: function(response) {
				if (!response.errors){
					console.log(response);
					//window.location.reload();
				}else{
					console.log(response.errors);
				}
			}
		})
		$('.variables .variable').click(function(){
			var _form = $(this).closest('form');
			$('#BonusEdit-type').val($(this).text());
			return !1;
		})
	})
</script>
{/literal}
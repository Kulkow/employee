<table class="table">
    <tr class="off">
        <th>Причина</th>
        <th>Дата</th>
        <th width="100px"></th>
        <th width="100px"></th>
        <th {if null != $is_rule}colspan="2"{/if}>Кто добавил</th>
        <th width="100px">Сумма</th>
        <th width="70px" class="actions">
			<div id="show_cancel" class="icon-toggle" title="Показать отмененные"></div>
		</th>
    </tr>
{foreach from=$bonuses item=bonus}
    <tr class="it-bonus {if 2 == $bonus.is_approved} cancel hidden{/if} {if 0 > $bonus.amount}minus{else}plus{/if} {if 1 == $bonus.approved}approved{/if}">
        <td class="tdtype">
            <p class="name">
                {$bonus.type}
            </p>
            <i>{$bonus.comment}</i>
        </td>
        <td class="tddate">
            {$bonus.date|date_format:"%e.%m.%Y "}
        </td>
        <td></td>
        <td></td>
        <td class="tdcreater" {if null != $is_rule}colspan="2"{/if}>
            {if 0 < $bonus.creator_id}
            <i>{$bonus.creater_name}</i>
            {/if}
        </td>
        <td class="tdamount">
            {if 0 < $bonus.amount}
                <p class="iconp-up icon">{$bonus.amount|price_format}</p>
            {else}
                <p class="iconp-down icon">{$bonus.amount|price_format}</p>
            {/if}
        </td>
        <td class="actions">
            {if 1 == $bonus.approved}
                <a href="/admin/employee/bonus/edit/{$bonus.id}" data-id="{$bonus.id}" class="icon-edit" title="Редактировать"></a>
                <a href="/admin/employee/bonus/remove/{$bonus.id}/" data-id="{$bonus.id}" class="icon-delete" title="Отменить"></a>
            {else}
                {if null !== $is_rule}
                    <a href="/admin/employee/bonus/approved/{$bonus.id}" data-id="{$bonus.id}" class="icon-check" title="Одобрить"></a>
                    <a href="/admin/employee/bonus/up/{$bonus.id}" data-id="{$bonus.id}" class="icon-play" title="перенести на следующий месяц"></a>
                    <a href="/admin/employee/bonus/edit/{$bonus.id}" data-id="{$bonus.id}" class="icon-edit" title="Редактировать"></a>
                {else}
                    {if $auth_user == $bonus.creator_id}
                        <a href="/admin/employee/bonus/remove/{$bonus.id}" data-id="{$bonus.id}" class="icon-delete" title="Отменить"></a>
                    {/if}
                {/if}
            {/if}
        </td>
    </tr>
{/foreach}
</table>
<div class="wrapper">
    {$form->open('/admin/employee/bonus/add')}
    <div class="row inline variables">
        {foreach from=$valiebles key=variable item=key}
            <div class="btn {if $key>0}red{else}blue{/if} variable">{$variable}</div>
        {/foreach}
    </div>
    <div class="row inline">
        <div class="row inline" style="float:left; clear:none;">
            {$form->label('type', 'Причина')}
            {$form->text('type', ['class' => 'txt'])}
        </div>
        <div class="row inline" style="float:left; clear:none;">
            {$form->label('amount', 'Сумма')}
            {$form->text('amount', ['class' => 'txt price'])}
        </div>
        <div class="row inline" style="float:left; clear:none;">
            {$form->submit('Добавить', ['class' => 'btn green'])}
        </div>
    </div>
    <div>
        {$form->hidden('manager_id')}
        {$form->hidden('date')}
        {$form->security()}
    </div>
    {$form->close()}
    {if null != $is_rule}
        <div class="row inline">
            <div class="row inline" style="float:left; clear:none;">
                Цена часа <span id="price_hour" data-price="{$price_hour|price_format:0}"><b>{$price_hour|price_format:0}</b><span> р/ч
            </div>
            <div class="row inline" style="float:left; clear:none; margin: 0 10px 0 0;">
                <label for="bonus-hour">кол-во</label>
                <input type="text" id="bonus-hour" name="hour" class="txt" style="width:80px"> часов
            </div>
            <div class="row inline" style="float:left; clear:none;">
                <button type="submit" id="calculate_hour" class="btn green">Рассчитать</button>
            </div>
        </div>
    {/if}
</div>
{literal}
<script>
    $(function(){
		$('#show_cancel').click(function(){
			var _div = $(this), _tr = _div.closest('tr'), _cancel = $('.it-bonus.cancel');
			if (_tr.hasClass('off')) {
				_tr.removeClass('off');
				_cancel.removeClass('hidden');
				_div.attr('title', 'Скрыть отмененные');
			}else{
				_tr.addClass('off');
				_cancel.addClass('hidden');
				_div.attr('title', 'Показать отмененные');
			}
		})
		$('.it-bonus').on('click', '.icon-check', function(){
			var a = $(this), id = a.data('id'), _url = a.attr('href'), tr = a.closest('tr'), _td_actions = a.closest('.actions');
			$.get(_url,function(json){
				if (json.errors) {
					alert(json.errors);
				}
				else{
					a.remove();
					tr.removeClass('cancel').addClass('approved');
					_td_actions.append('<a href="/admin/employee/bonus/remove/'+id+'" data-id="'+id+'" class="icon-delete" title="Отменить"></a>');
				}
			});
			return !1;
		})
		$('.it-bonus').on('click', '.icon-edit', function(){
		var a = $(this), id = a.data('id'), _url = a.attr('href'), _tr = a.closest('tr');
            a.ajaxpopup({
                form: {
                    submit: function(response) {
					   var _bonus = response.bonus;
					   if (_bonus) {
						  var _amount =  _bonus.amount;
						  _amount = +_amount/100;
						  $('.tdamount .icon', _tr).text(_amount);
						  var _type =  _bonus.type;
						  $('.tdtype', _tr).text(_type);
					   }
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
		$('.it-bonus').on('click', '.icon-delete', function(){
			var a = $(this), id = a.data('id'), _url = a.attr('href'), tr = a.closest('tr'), _td_actions = a.closest('.actions');
            $.get(this.href, function(data){
                if (data.errors) {
				}else{
					console.log('cancel');
					a.remove();
					tr.addClass('cancel').removeClass('approved');
					_td_actions.append('<a href="/admin/employee/bonus/approved/'+id+'" data-id="'+id+'" class="icon-check" title="Одобрить"></a>');
				}
            })
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
					//console.log(response);
					/*window.location.reload();*/
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
        $('#calculate_hour').click(function(){
			var button = $(this), _hour = $('#bonus-hour'), _ammount = $('#BonusEdit-amount'), _value = _hour.val(), _price = $('#price_hour').data('price');
			_value = parseInt(_value);
			if(_value) {
				_value = _value*_price;
				_ammount.val(_value);
			}
		})
    })
</script>
{/literal}
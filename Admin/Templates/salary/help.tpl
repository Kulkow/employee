<div class="list" style="width:100%;">
	<div style="overflow: hidden; position: relative;">
		<a href="/admin/employee/eplan/add/{$user_id}" title="Добавить показатель пользователю" class="add-plan btn icon-add">Добавить показатель</a>
	</div>
	<table class="table suser">
		<thead class="thead" data-section="plans">
			<tr>
				<th colspan="8" class="th {if false !== $is_rule}expanded{/if}"><span class="icon-expand"></span>Показатели</th>
			</tr>
			<tr>
				<th>Показатели</th>
				<th width="70px">Влияние/ставка</th>
				<th class="pf-td" width="70px">План</th>
				<th class="pf-td" width="70px">Факт</th>
				<th width="100px">Выполнение</th>
				<th width="150px">Ставка/общий процент</th>
				<th class="price-td" width="50px">Начислено</th>
				{if false != $is_rule}
				<th class="actions" width="50px"></th>
				{/if}
			</tr>
		</thead>
		<tbody class="tbody section-plans">
		{assign var=plan_based value=$salary.salary.is_plan_based}
		{assign var=plan_nobased value=$salary.salary.no_plan_based}
		{assign var=cplan_isbased value=$salary.salary.is_plan_based|@count}
		{assign var=cplan_nobased value=$salary.salary.no_plan_based|@count}
		{assign var=summary_percent value=$salary.summary_percent}
		{if 0 < $cplan_isbased}
			<tr class="it-plan heads">
				<td colspan="8">Плановые</td>
			</tr>
			{foreach from=$plan_based item=_plan}
				<tr class="it-plan">
					<td class="tdname">
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
					<td class="tdvalue">
						{$_plan.value|round} %
					</td>
					<td class="tdplan">{$_plan.plan|price_format}</td>
					<td class="tdfact">{$_plan.fact|price_format:0}</td>
					<td class="tdtempo">
						{if $_plan.plan > 0}
							{(($_plan.fact/$_plan.plan)*100)|round} %
						{else}
							0 %
						{/if}
						{if 0 < $_plan.plan}
							{if $_plan.tempo > 100}
								<span class="icon-up" title="Опережение плана">{($_plan.tempo - 100)|round}%</span>
							{else}
								<span class="icon-down" title="Отставание от плана">{($_plan.tempo - 100)|round}%</span>
							{/if}
						{/if}
					</td>
					{if null !== $summary_percent}
						<td rowspan="{$cplan_isbased}" class="tdtempo">
							<div class="base">
							{if false != $is_rule}
								<a href="/admin/employee/esalary/edit/{$esalary.id}/" class="editbase" title="редактирование базовой ставки">
									{$esalary.base|price_format}
								</a>
							{else}
								<span>{$esalary.base|price_format}</span>
							{/if}
							</div>
							<div class="percents">
								<span class="percent">{$summary_percent}%</span>
								{if $summary_percent > 100}
									<span class="icon-up" title="Опережение плана">{($summary_percent - 100)}%</span>
								{else}
									<span class="icon-down" title="Отставание от плана">{($summary_percent - 100)}%</span>
								{/if}
							</div>
						</td>
						{assign var=summary_percent value=null}
					{/if}
					{if 0 < $cplan_isbased}
						<td rowspan="{$cplan_isbased}" class="price-td">
							<span>{$salary.total_plan|price_format:0}</span>
						</td>
						{assign var=cplan_isbased value=0}
					{/if}
					{if false != $is_rule}
					<td class="actions">
						{if null == $is_generate}
							<a href="/admin/employee/eplan/edit/{$_plan.id}" title="Редактировать" class="icon-edit"></a>
							{if null == $_plan.end}
								<a href="/admin/employee/eplan/remove/{$_plan.id}" title="Удалить" class="icon-delete"></a>
							{/if}
						{/if}
					</td>
					{/if}
				</tr>
			{/foreach}
		{/if}
		{if 0 < $cplan_nobased}
			<tr class="it-plan heads">
				<td colspan="{if null != $is_rule}8{else}7{/if}">Сдельные</td>
			</tr>
			{foreach from=$plan_nobased item=_plan}
				<tr class="it-plan">
					<td class="tdname">
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
					<td class="tdvalue">
						{if 0 == $_plan.is_discrete}
							{$_plan.value*100} %
						{else}
							{$_plan.value|price_format} за {$_plan.measurement}
						{/if}
					</td>
					<td></td>
					<td class="tdfact">
						{if 0 < $_plan.fact}
							{if 1 == $_plan.is_discrete}
								{$_plan.fact}
							{else}
								{$_plan.fact|price_format}
							{/if}
						{/if}
					</td>
					<td colspan="2"></td>
					<td class="price-td">
						{if 0 < $_plan.total}
							<span>{$_plan.total|price_format:0}</span>
						{/if}
					</td>
					{if null != $is_rule}
					<td class="actions">
						{if null == $is_generate}
							<a href="/admin/employee/eplan/edit/{$_plan.id}" title="Редактировать" class="icon-edit"></a>
							{if null == $_plan.end}
								<a href="/admin/employee/eplan/remove/{$_plan.id}" title="Удалить" class="icon-delete"></a>
							{/if}
						{/if}
					</td>
					{/if}
				</tr>
			{/foreach}
		{/if}
		</tbody>
	</table>
	
	<!-- TASK -->
	<table class="table suser">
		<thead class="thead" data-section="task">
			<tr class="it-task heads">
				<th colspan="{if false != $is_rule}8{else}7{/if}" class="th {if false !== $is_rule}expanded{/if}"><span class="icon-expand"></span> Задания</th>
			</tr>
		</thead>
	</table>
	<!-- BONUS -->
	<table class="table suser">
		<thead class="thead" data-section="bonus">
			<tr class="it-plan heads">
				<th colspan="{if false != $is_rule}8{else}7{/if}" class="th {if false !== $is_rule}expanded{/if}"><span class="icon-expand"></span> Депремирования и бонусы</th>
			</tr>
			<tr class="off">
				<th>Причина</th>
				<th>Дата</th>
				<th width="150px">Кто добавил</th>
				<th width="70px">Сумма</th>
				<th class="actions" width="50px">
					<div id="show_cancel" class="icon-toggle" title="Показать отмененные"></div>
				</th>
			</tr>
		</thead>
		<tbody class="tbody {if false == $is_rule}hidden{/if} section-bonus">
		{foreach from=$bonuses item=bonus}
			<tr class="it-bonus {if 2 == $bonus.is_approved} cancel hidden{/if} {if 0 > $bonus.amount}minus{else}plus{/if} {if 1 == $bonus.approved}approved{else}{/if}">
				<td class="tdtype">
					<p class="name">
						{$bonus.type}
					</p>
					<i>{$bonus.comment}</i>
				</td>
				<td class="tddate">
					{$bonus.date|date_format:"%e.%m.%Y "}
				</td>
				<td class="tdcreater">
					{if 0 < $bonus.creator_id}
					<i>{$bonus.creater_name}</i>
					{/if}
				</td>
				<td class="tdamount edit_cell">
					{if 0 < $bonus.amount}
						<span class="iconp-up icon value">{$bonus.amount|price_format}</span>
					{else}
						<span class="iconp-down icon value">{$bonus.amount|price_format}</span>
					{/if}
					<input name="Bonus[amount]" value="{$bonus.amount|price_format}" class="txt bonus" data-bonus="{$bonus.id}" data-salary="{$salary_id}" />
				</td>
				<td class="actions">
						{if ! isset($bonus.virtual)}
							{if 1 == $bonus.approved}
								{if false !== $is_rule}
									<a href="/admin/employee/bonus/edit/{$bonus.id}?salary_id={$salary_id}" class="icon-edit" title="Редактировать"></a>
									<a href="/admin/employee/bonus/remove/{$bonus.id}?salary_id={$salary_id}" class="icon-delete" title="Отменить"></a>
								{else}
									{if $auth_user == $bonus.creator_id}
										<a href="/admin/employee/bonus/edit/{$bonus.id}" class="icon-edit" title="Редактировать"></a>
										<a href="/admin/employee/bonus/remove/{$bonus.id}/" class="icon-delete" title="Отменить"></a>
									{/if}
								{/if}
							{else}
								{if false !== $is_rule}
									<a href="/admin/employee/bonus/approved/{$bonus.id}?salary_id={$salary_id}" data-id="{$bonus.id}" class="icon-check" title="Одобрить"></a>
									<a href="/admin/employee/bonus/up/{$bonus.id}?salary_id={$salary_id}" data-id="{$bonus.id}" class="icon-play" title="перенести на следующий месяц"></a>
									<a href="/admin/employee/bonus/edit/{$bonus.id}?salary_id={$salary_id}" data-id="{$bonus.id}" class="icon-edit" title="Редактировать"></a>
								{else}
									{if $auth_user == $bonus.creator_id}
										<a href="/admin/employee/bonus/remove/{$bonus.id}" class="icon-delete" title="Отменить"></a>
									{/if}
								{/if}
							{/if}
						{else}
							<a href="/admin/employee/bonus/request/{$bonus.id}" class="add-request" title="Запрос на отмену"></a>
							<a href="/admin/employee/bonus/timesheet/{$bonus.id}" class="add-request-time" data-date="{$bonus.date}" data-start="{$bonus.start}" data-end="{$bonus.start}" title="Запрос на изменения графика"></a>
						{/if}
				</td>
			</tr>
        {/foreach}
		</tbody>
	</table>
	<!-- TOTAL -->
	<table class="table suser">
		<tbody>
			<tr class="it-plan total max">
				<td colspan="{if false != $is_rule}6{else}5{/if}">Мог бы заработать</td>
				<td class="tdsumma"><b id="max_salary">{$salary.max|price_format:0}</b></td>
				<td width="50px"></td>
			</tr>
			<tr class="it-plan total">
				<td colspan="{if false != $is_rule}6{else}5{/if}">Заработал</td>
				<td class="tdsumma" id="total_salary">{$salary.total|price_format:0}</td>
				<td width="50px"></td>
			</tr>
		</tbody>
	</table>
	
	<div class="wrapper">
		{$addform->open('/admin/employee/bonus/add')}
		<div class="row inline variables">
			{foreach from=$valiebles key=variable item=key}
				<div class="btn {if $key>0}red{else}blue{/if} variable">{$variable}</div>
			{/foreach}
		</div>
		<div class="row inline">
			<div class="row inline" style="float:left; clear:none; ">
				{$addform->label('type', 'Причина')}
				{$addform->text('type', ['class' => 'txt', 'style' => 'width:200px'])}
			</div>
			<div class="row inline" style="float:left; clear:none;">
				{$addform->label('amount', 'Сумма')}
				{$addform->text('amount', ['class' => 'txt price', 'style' => 'width:80px'])}
			</div>
			<div class="row inline" style="float:left; clear:none;">
				{$addform->submit('Добавить', ['class' => 'btn green'])}
			</div>
		</div>
		<div>
			{$addform->hidden('manager_id')}
			{$addform->hidden('salary_id', ['value' => $salary_id])}
			{$addform->hidden('date')}
			{$addform->security()}
		</div>
		{$addform->close()}
		{if false != $is_rule}
			<div class="row inline bonus-hour">
				<div class="row inline" style="float:left; clear:none; margin: 0 10px 0 0; line-height: 2;">
					<label for="bonus-hour"></label>
					<input type="text" id="bonus-hour" name="hour" class="txt" style="width:56px"> часов
				</div>
				<div class="row inline" style="float:left; clear:none; line-height: 2; margin: 0 5px 0 0;">
					по ставке <span id="price_hour" data-price="{$price_hour|price_format:0}"><b>{$price_hour|price_format:0}</b><span>
				</div>
				<div class="row inline" style="float:left; clear:none;">
					<button type="submit" id="calculate_hour" class="btn green">Рассчитать</button>
				</div>
			</div>
		{/if}
	</div>
</div>
{literal}
<script>
    $(function(){
		var suser = $('.suser');
		$('.thead',suser).on('click','.th', function(){
			var _th = $(this), _icon = _th.find('.icon-expand'), _thead = _th.closest('thead'), _table = _th.closest('table');
			var _t = _thead.data('section'), _tbody = $('.section-'+_t);
			if (_th.hasClass('expanded')) {
				_th.removeClass('expanded');
				_tbody.addClass('hidden');
			}else{
				_th.addClass('expanded');
				_tbody.removeClass('hidden');
			}
		})
		
		$('.it-bonus .edit_cell').click(function(){
			var td = $(this), input = $('input.txt', td), _value = $('.value', td);
			if (td.hasClass('edit')) {
				
			}else{
				$('.it-bonus .edit_cell.edit').each(function(index, _td){
					var _input = $('input.txt', _td);
					$(_td).removeClass('edit');
					$('.value', _td).text(_input.val());
				})
				td.addClass('edit');
    			
                var yourClick = true;
    			$(document).bind('click.myBonus', function (e) {
    			  if (!yourClick && $(e.target).closest('.edit_cell').length == 0) {
    				  var _bonus = $('.edit_cell.edit .txt.bonus');
    				  if (_bonus.length > 0) {
    					console.log(_bonus);
    					console.log('_bonus');
    					SaveBonus(_bonus);
    					td.removeClass('edit');
    				  }
    				$(document).unbind('click.myBonus');
    			  }
    			  yourClick = false;
    			});
			}
		})
        
        function SaveBonus(_input){
            var td = _input.closest('.edit_cell'),_tr = td.closest('tr'), _value = $('.value', td), _bonus_id = _input.data('bonus');
            var token = sp.security.token;
			var data = {'BonusEdit' :{
				'amount' : _input.val(),
				'user_id' : _input.data('user'),
				'id' : _bonus_id,
				'salary_id' : _input.data('salary'),
				},
				'token' : token,
				'submit' : 1
			};
			
			$.ajax({
				type: "POST",
				dataType: "json",
				data: data,
				url: '/admin/employee/bonus/edit/'+_bonus_id,
				success: function(response){
					if (response.errors) {
						var _error = viewerror(response.errors);
						alert(_error);
						_input.val('');
					}else{
						var _bonus = response.bonus;
						if (_bonus) {
						   var _amount =  _bonus.amount;
						   _amount = +_amount/100;
						   $('.tdamount .icon', _tr).text(_amount);
						   var _type =  _bonus.type;
						   $('.tdtype', _tr).text(_type);
						}
						var _salary = response.salary;
						if (_salary) {
							var _total =  _salary.total;
						    _total = +_total/100;
						   $('#total_salary').text(_total);
						   var _max =  _salary.max;
						    _max = +_max/100;
							_max = Math.round(_max);
						   $('#max_salary').text(_max);
						}
					}
				}
			})
			_value.text(_input.val());
			td.removeClass('edit');
			var _cells = $('.edit_cell');
			var _index = _cells.index(td);
			if((+(_cells.length) - 1) >= _index){
				_index++;
				var _next = _cells.eq(_index);
				_next_input = $('input.txt.bonus', _next);
				if (_next_input.length) {
					_next_input.click();
					_next_input.focus();
				}
			}
        }
        
        

		$('.it-bonus .edit_cell input.txt.bonus').keyup(function(e){
			var code = e.keyCode || e.which;
			if(code == 13) {
				var _input = $(this);
                 SaveBonus(_input);
			}
		})
		
		$('.add-plan').click(function(){
		var a = $(this), id = a.data('id'), _url = a.attr('href');
            a.ajaxpopup({
                form: {
                    submit: function(response) {
                        location.reload();
                    }
                },
                title: 'Добавить',
                url: _url,
                dialog: {
                    minWidth: 350,
                    width: 350
                },
            });
            return !1;
        })
		$('.editbase').click(function(){
		var a = $(this), id = a.data('id'), _url = a.attr('href');
            a.ajaxpopup({
                form: {
                    submit: function(response) {
                        location.reload();
                    }
                },
                title: 'Редактировать базовую ставку',
                url: _url,
                dialog: {
                    minWidth: 350,
                    width: 350
                },
            });
            return !1;
        })
		$('.add-timemanager').click(function(){
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
                    minWidth: 450,
                    width: 450
                },
            });
            return !1;
        })
		
		$('.it-timemanager').on('click', '.icon-edit', function(){
		var a = $(this), id = a.data('id'), _url = a.attr('href');
            a.ajaxpopup({
                form: {
                    submit: function(response) {
                        location.reload();
                    }
                },
                title: 'Изменить график',
                url: _url,
                dialog: {
                    minWidth: 450,
                    width: 450
                },
            });
            return !1;
        })
        $('.it-plan .icon-edit').click(function(){
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
			$.get(_url,function(response){
				if (response.errors) {
					alert(response.errors);
				}
				else{
					a.remove();
					tr.removeClass('cancel').addClass('approved');
					_td_actions.append('<a href="/admin/employee/bonus/remove/'+response.bonus.id+'" data-id="'+response.bonus.id+'" class="icon-delete" title="Отменить"></a>');
					var _salary = response.salary;
					if (_salary) {
						var _total =  _salary.total;
						_total = +_total/100;
					   $('#total_salary').text(_total);
					   var _max =  _salary.max;
						_max = +_max/100;
						_max = Math.round(_max);
					   $('#max_salary').text(_max);
					}
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
					    var _salary = response.salary;
						if (_salary) {
							var _total =  _salary.total;
							_total = +_total/100;
						   $('#total_salary').text(_total);
						   var _max =  _salary.max;
							_max = +_max/100;
							_max = Math.round(_max);
						   $('#max_salary').text(_max);
						}
                    }
                },
                title: 'Изменить ',
                url: _url,
                dialog: {
                    minWidth: 400,
                    width: 400
                },
            });
            return !1;
        })
		$('.it-bonus').on('click', '.icon-delete', function(){
			var a = $(this), id = a.data('id'), _url = a.attr('href'), tr = a.closest('tr'), _td_actions = a.closest('.actions');
            $.get(this.href, function(response){
                if (response.errors) {
				}else{
					a.remove();
					tr.addClass('cancel').removeClass('approved');
					_td_actions.append('<a href="/admin/employee/bonus/approved/'+response.bonus.id+'" data-id="'+response.bonus.id+'" class="icon-check" title="Одобрить"></a>');
					var _salary = response.salary;
					if (_salary) {
						var _total =  _salary.total;
						_total = +_total/100;
					   $('#total_salary').text(_total);
					   var _max =  _salary.max;
						_max = +_max/100;
						_max = Math.round(_max);
					   $('#max_salary').text(_max);
					}
				}
            })
            return !1;
        })
		$('.it-bonus').on('click', '.icon-play', function(){
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
					window.location.reload();
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
        $('.it-plan').on('click', '.icon-delete',function(){
            var tr = $(this).closest('tr');
			if (confirm('Подтвердите удаление')){
				$.get(this.href, function(data){
					tr.remove();
				})
			}
            return !1;
        })
        var date_widget = $('.date_widget');
		var mounts = $('.mount', date_widget);
		var years = $('.year', date_widget);
		mounts.each(function(index,div){
			var radio = $('input',div);
			if (radio.get(0).checked) {
				$(div).addClass('active');
			}
		})
		years.each(function(index,div){
			var radio = $('input',div);
			if (radio.get(0).checked) {
				$(div).addClass('active');
			}
		})
		mounts.click(function(){
			var check = $(this).find('input').get(0);
			$('.mount',date_widget).removeClass('active');
			$(this).addClass('active');
			check.checked = true;
			$('.period input').val('');
            $(this).closest('form').submit();
		})
		years.click(function(){
			var check = $(this).find('input').get(0);
			$('.year',date_widget).removeClass('active');
			$(this).addClass('active');
			check.checked = true;
			$('.period input').val('');
            $(this).closest('form').submit();
		})
		$('#calculate_hour').click(function(){
			var button = $(this), _hour = $('#bonus-hour'), _ammount = $('#BonusEdit-amount'), _value = _hour.val(), _price = $('#price_hour').data('price');
			_value = parseInt(_value);
			if(_value) {
				_value = _value*_price;
				_ammount.val(_value);
			}
		})
		
		$('.it-department').on('click', '.icon-add', function(){
			var a = $(this), id = a.data('id'), _url = a.attr('href'), _tr = a.closest('.it-department');
			a.ajaxpopup({
                form: {
                    submit: function(response) {
						location.reload();
                    }
                },
                title: 'Добавить подразделение в котором состоит сотрудник',
                url: _url,
                dialog: {
                    minWidth: 400,
                    width: 400
                },
            });
			return !1;
		})
		$('.it-department .move').click(function(){
			var a = $(this), id = a.data('id'), _url = a.attr('href'), _tr = a.closest('.it-department');
			a.ajaxpopup({
                form: {
                    submit: function(response) {
						location.reload();
                    }
                },
                title: 'Перемещение в другое подразделение',
                url: _url,
                dialog: {
                    minWidth: 400,
                    width: 400
                },
            });
			return !1;
		})
		$('.it-department').on('click', '.icon-check', function(){
			var a = $(this), id = a.data('id'), _url = a.attr('href'), _tr = a.closest('.it-department');
			$.post(_url,function(data){
				if (data.department_id) {
					$('.it-department').find('.actions').removeClass('checked');
					$("[data-department="+data.department_id+"]").find('.actions').addClass('checked');
				}
			})
			return !1;
		})
		$('.it-department').on('click', '.icon-delete', function(){
			var a = $(this), id = a.data('id'), _url = a.attr('href'), tr = a.closest('tr');
			$.get(_url,function(json){
				if (json.errors) {
					alert(json.errors);
				}
				else{
					console.log(json);
					tr.remove();
				}
			});
			return !1;
		})
    })
</script>
{/literal}
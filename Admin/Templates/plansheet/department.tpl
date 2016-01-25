{include file='menu.tpl'}
{include file='plansheet/filter.tpl'}
<h1>Плановые показатели  {if isset($cdepartment)}в подразделении {$cdepartment.name}{/if} выбранный период</h1>

<h2>Общая прибыль</h2>
<div style="width:90%;">
    <table id="totalProfit" class="exceltable">
        <tr>
            <td></td>
            {foreach from=$months item=col}
                {assign var=_col value=strtotime("2015-`$col`-01")}
                <th class="thcol a-center all-{$col}" data-class="all-{$col}" style="min-width:50px;">{$_col|date_format:"%B"}</th>
            {/foreach}
        </tr>
        {assign var=prev value=0}
        {foreach from=$all_col key=year item=m}
            <tr>
                <th class="a-right">{$year}</th>
                {foreach from=$months item=_col}
                    {assign var=col value=strtotime("`$year`-`$_col`-01")}
                    <td class="thcol a-right all-{$col} col-" data-class="all-{$col}">
                        {if isset($all.$col)}
                            {assign var=psheets value=$all.$col}
                            {if isset($psheets.profit)}
                                {$psheets.profit|price_format:0}{if $prev}<span class="icon-{if $psheets.profit < $prev}down">{else}up">+{/if}{(($psheets.profit/$prev-1)*100)|round}%</span>{/if}
                            {/if}
                        {/if}
                    </td>
                    {assign var=prev value=$psheets.profit|default:0}
                {/foreach}
            </tr>
        {/foreach}
        </tbody>
    </table>
</div>

{$form->open('/admin/employee/plansheet/month/')}
{assign var=call1 value=$call_helpers.call1}
{assign var=call2 value=$call_helpers.call2}
{assign var=prev value=$call_helpers.prev}
<div style="width:90%;">
	<div class="managecols">
		<div class="managecols-row">
			<span class="firstcol col">Показать колонки</span>
			<span class="col showcol hidden showcol-first"  data-class="col-first" title="Показать">
				{$form->getData('start')|date_format:"%B %Y"}
			</span>
			{foreach from=$info_col item=col}
				<span data-class="col-{$col}" class="hidden showcol col showcol-{$col}" title="Показать">{$col|date_format:"%B %Y"}</span>
			{/foreach}
		</div>
	</div>
    {assign var=commons value=$form->getData('common')}
	{assign var=f_users value=$form->getData('user')}
    <table class="exceltable" id="plansheet">
		<thead>
			<tr>
				<th></th>
				{foreach from=$info_col item=col}
					<th class="thcol col-{$col}" colspan="3" data-class="col-{$col}" style="min-width:150px;">{$col|date_format:"%B %Y"}</th>
				{/foreach}
				<th class="thcol col-first currmonth" data-class="col-first" colspan="3">
					{$form->getData('start')|date_format:"%B %Y"}
				</th>
			</tr>
			<tr>
				<th></th>
				{foreach from=$info_col item=col}
					<th class="thcol a-center col-{$col}" data-class="col-{$col}">план</th>
                                        <th class="thcol a-center col-{$col}" data-class="col-{$col}">%</th>
					<th class="thcol a-center col-{$col}" data-class="col-{$col}">факт</th>
				{/foreach}
				<th class="thcol a-center col-first currmonth" data-class="col-first">
					план
				</th>
				<th class="thcol a-center col-first currmonth" data-class="col-first">
                                    %
                                </th>
                                <th class="thcol a-center col-first currmonth" data-class="col-first">
					факт
				</th>
			</tr>
		</thead>
		{foreach from=$tree item=department}
			{assign var=did value=$department.id} 
			<tbody class="department level{$department.level}" data-id="{$department.id}">
				<tr class="dname">
					<td class="firsttd name active" data-department="{$department.id}">
						<a href="/admin/employee/plansheet/department/{$department.id}?month={$cmonth}&year={$cyear}" class="it-d"><b>{$department.number}</b> {$department.name}</a>
                        <a href="/admin/employee/employee/department/{$department.id}" class="employee-add icon-add"></a>
					</td>
					<td colspan="{$info_col|@count + 1}"></td>
				</tr>
				<tbody class="department{$department.id}">
				{if isset($department.plans)}
					{foreach from=$department.plans item=dplan}
						{assign var=dplanid value=$dplan.plan_id}
						{if 1 == $dplan.is_common}
							{foreach from=$dplan.users item=employee}
								{assign var=key_user value="`$employee.user_id`_`$employee.plan_id`"}
								
								{assign var=_users value=$department.users}
								{assign var=_user_id value=$employee.user_id}
								{assign var=employee_user value=$_users.$_user_id}
								
								<tr class="it-employee it-plansheet {if null != $employee.end}end{/if}">
									<td class="firsttd">
										<a href="/admin/employee/eplan/user/{$employee.user_id}" title="{$employee.u_name}" class="employee-name">
											{$employee.u_name}
										</a>
										{if null == $employee.end}
											<a href="/admin/employee/employee/changedepartment/{$employee.user_id}?department_id={$employee.department_id}" title="Перенести из подразделения" class="move-employee icon-move-employee "></a>
										{/if}
										
									</td>
									{assign var=key_sheet value=$employee.key}
									{foreach from=$info_col item=col}
										{assign var=psheets value=$info.$col}
										{assign var=tdclass value=''}
										{if $call1 == $col}
											{assign var=tdclass value=prevyear}
										{/if}
										{if $call2 == $col}
											{assign var=tdclass value=prevyear}
										{/if}
										{if $prev == $col}
											{assign var=tdclass value=prevmonth}
										{/if}
										{if isset($psheets.$key_sheet)}
											{assign var=sheet value=$psheets.$key_sheet}
											<td class="col-{$col} {$tdclass}">
												<span class="span"> 
													{$sheet.plan_amount|price_format:0}
												</span>
											</td>
                                                                                    <td class="col-{$col} {$tdclass}" >
                                                                                        {if 0 < $sheet.plan_amount}
                                                                                            {assign var=_percent value=((($sheet.fact_amount/$sheet.plan_amount) - 1)*100)}
                                                                                            {if 0 < $_percent}
                                                                                                <span class="icon-up" title="{$_percent|round}%">+{$_percent|round}%</span>
                                                                                            {else}
                                                                                                <span class="icon-down" title="{$_percent|round}%">{$_percent|round}%</span>
                                                                                            {/if}	
                                                                                        {/if}
                                                                                    </td>
											<td class="col-{$col} {$tdclass}" >
												{if 0 < $sheet.fact_amount}
													<span class="span">
														{$sheet.fact_amount|price_format:0}
													</span>
												{else}
													-
												{/if}
											</td>
										{else}
											<td class="col-{$col} {$tdclass}">
												-
											</td>
											<td class="col-{$col} {$tdclass}">
												-
											</td>
                                            <td class="col-{$col} {$tdclass}">
												-
											</td>
										{/if}
									{/foreach}
									<td class="col-first edit_cell currmonth">
										{if null == $employee.end}
											<span class="value" title="Нажать для редактирования">
												{assign var=edit_cell value=$form->getData("user")}
												{if isset($edit_cell.$key_user)}
													{$edit_cell.$key_user}
                                                {/if}
												
												{if isset($helpers.$key_sheet)}
													{assign var=helper value=$helpers.$key_sheet}
												{else}
													{assign var=helper value=null}
												{/if}
                                                {assign var=placeholder value=$plansheet->getHelpInfoAll($help_percent, $helper)}
											</span>
											{$form->text("user[`$key_user`]", ["class" => "txt", "placeholder"=> $placeholder])}
										{/if}
									</td>
                                                <td class="col-first currmonth">
										{if isset($f_users.$key_user)}
										
                                            {if isset($facts.$key_user)}
												{if 0 < $f_users.$key_user}
													{assign var=_percent value=((($facts.$key_user/($f_users.$key_user*$c_percent*100)) - 1)*100)}
												{else}
													{assign var=_percent value=100}
												{/if}
                                                {if 0 < $_percent}
                                                    <span class="icon-up" title="{$_percent|round}%">{$_percent|round}%</span>
                                                {else}
                                                    <span class="icon-down" title="{$_percent|round}%">{$_percent|round}%</span>
                                                {/if}
                                            {/if}
                                        {/if}
									</td>
                                    <td class="col-first currmonth">
										{if isset($facts.$key_user)}
											{$facts.$key_user|price_format:0}
										{/if}
									</td>
								</tr>
							{/foreach}
							<tr class="tr-summa it-plansheet">
								{if 0 < $dplan.department_id}
									{assign var=key_sheet value="common_`$dplan.department_id`_`$dplan.plan_id`"}
									{assign var=key_common value="`$dplan.department_id`_`$dplanid`"}
								{else}
									{assign var=key_sheet value="common_`$dplan.plan_id`"}
									{assign var=key_common value=$dplanid}
								{/if}
								<td class="name">
									{if 0 == $dplan.pid}
										<b title="{$dplan.name}">{$dplan.name}:</b>
									{else}
										<b title="{$dplan.name}">Итого:</b>
									{/if}
								</td>
								{foreach from=$info_col item=col}
									{assign var=psheets value=$info.$col}
									{assign var=tdclass value=''}
									{if $call1 == $col}
										{assign var=tdclass value=prevyear}
									{/if}
									{if $call2 == $col}
										{assign var=tdclass value=prevyear}
									{/if}
									{if $prev == $col}
										{assign var=tdclass value=prevmonth}
									{/if}
									{if isset($psheets.$key_sheet)}
										{assign var=sheet value=$psheets.$key_sheet}
										<td class="col-{$col} {$tdclass}">
											<span class="span">
												{$sheet.plan_amount|price_format:0}
											</span>
										</td>
                                        <td class="col-{$col} {$tdclass}">
                                            {if 0 < $sheet.plan_amount}
                                                {assign var=_percent value=((($sheet.fact_amount/$sheet.plan_amount) - 1)*100)}
                                                {if 0 < $_percent}
                                                    <span class="icon-up" title="{$_percent|round}%">{$_percent|round}%</span>
                                                {else}
                                                    <span class="icon-down" title="{$_percent|round}%">{$_percent|round}%</span>
                                                {/if}	
                                            {/if}
                                        </td>
										<td class="col-{$col} {$tdclass}">
											{if 0 < $sheet.fact_amount}
												<span class="span">
													{$sheet.fact_amount|price_format:0}
												</span>
											{else}
											{/if}
										</td>
									{else}
										<td class="col-{$col} {$tdclass}">-</td>
										<td class="col-{$col} {$tdclass}">-</td>
                                        <td class="col-{$col} {$tdclass}">-</td>
									{/if}											
									</td>
								{/foreach}
								<td class="col-first edit_cell currmonth">
									<span class="value" title="Нажать для редактирования">
										{assign var=edit_cell value=$form->getData("common")}
										{if isset($edit_cell.$key_common)}
											{$edit_cell.$key_common}
										{/if}
										{if isset($edit_cell.$dplanid)}
											{$edit_cell.$dplanid}
										{/if}
										{if isset($helpers.$key_sheet)}
											{assign var=helper value=$helpers.$key_sheet}
										{else}
											{assign var=helper value=null}
										{/if}
										{assign var=placeholder value=$plansheet->getHelpInfoAll($help_percent,$helper)}
									</span>
									{$form->text("common[`$key_common`]", ["class" => "txt", "placeholder"=> $placeholder])}
								</td>
                                <td class="col-first currmonth">
									{if isset($commons.$key_common)}
                                        {if isset($facts.$key_common)}
											{if 0 < $commons.$key_common}
												{assign var=_percent value=((($facts.$key_common/($commons.$key_common * $c_percent *100)) - 1)*100)}
											{else}
												{assign var=_percent value=100}
											{/if}
                                            {if 0 < $_percent}
                                                <span class="icon-up" title="{$_percent|round}%">{$_percent|round}%</span>
                                            {else}
                                                <span class="icon-down" title="{$_percent|round}%">{$_percent|round}%</span>
                                            {/if}
                                        {/if}
                                    {/if}
								</td>
                                <td class="col-first currmonth">
									{if isset($facts.$key_common)}
										{$facts.$key_common|price_format:0}
									{/if}
								</td>
							</tr>
						{/if}
					{/foreach}
				{/if}
				</tbody>
			</tbody>
		{/foreach}
    </table>
</div>
{$form->hidden('month')}
{$form->hidden('year')}
{$form->hidden('start')}
{$form->hidden('end')}
{$form->security()}
{$form->submit('Сохранить', ['class' => 'btn'])}
{$form->close()}
{literal}
<style>
    .exceltable .a-center { text-align: center; }
    .exceltable .a-right { text-align: right; }
    .exceltable TH { background-color: #D0EDFF; }
    .exceltable TD { vertical-align: middle; }
    #totalProfit .icon-down { float:left; color:red; margin-left: 5px; }
    #totalProfit .icon-up { float:left; color:green; margin-left: 5px; }
    #totalProfit { margin-bottom:30px; }
    .exceltable .icon-down::before,
    .exceltable .icon-up-down::before, 
    .exceltable .icon-up::before { content: ""; }
</style>
<script>
	$(function(){
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
		$('.datepicker').datepicker();
		$('#reset-period').click(function(){
			$('.period input').val('');
			return !1;
		})
		$('#show_filter').click(function(){
			$(this).closest('form').submit();
		})
		$('#plansheet.exceltable .dname .name').click(function(e){
			if (e.target.nodeName != 'A') {
				var tr = $(this), d_id = tr.data('department'),tbody = $('.department'+d_id), tlevel = tr.closest('.department');
				if (tr.hasClass('active')) {
					tbody.addClass('hidden');
					tr.removeClass('active');
					tlevel.removeClass('expanded');
				}else{
					tbody.removeClass('hidden');
					tr.addClass('active');
					tlevel.addClass('expanded');
				}
			}
		})
		$('#PlanSheetMonth').form({
			susses:function(json){
				
			}
		});
		$('.exceltable .edit_cell').click(function(){
			var td = $(this), input = $('input.txt', td), _value = $('.value', td);
			if (td.hasClass('edit')) {
				/*td.removeClass('edit');
				_value.text(input.value());*/
			}else{
				$('.exceltable .edit_cell.edit').each(function(index, _td){
					var _input = $('input.txt', _td);
					$(_td).removeClass('edit');
					$('.value', _td).text(_input.val());
				})
				td.addClass('edit');
                var _input = $(this).find('input.txt');
                _input.focus();
			}
		})/*
		$('.exceltable .edit_cell input.txt').focusout(function(){
			var input = $(this), td = input.closest('.edit_cell'), _value = $('.value', td);
			_value.text(input.val());
			td.removeClass('edit');
		})
		*/
		$('.exceltable .edit_cell input.txt').focusout(function(){
			var input = $(this), td = input.closest('.edit_cell'), _value = $('.value', td);
			_value.text(input.val());
			td.removeClass('edit');
		})
		$('.exceltable .edit_cell input.txt').keyup(function(e){
			var code = e.keyCode || e.which;
			if(code == 13) {
			 console.log('Enter');
				var input = $(this), td = input.closest('.edit_cell'),_tr = td.closest('tr'), _value = $('.value', td);
				_value.text(input.val());
				td.removeClass('edit');
				var _cells = $('.edit_cell');
				var _index = _cells.index(td);
				if((+(_cells.length) - 1) >= _index){
					_index++;
					var _next = _cells.eq(_index);
					_next_input = $('input.txt', _next);
					if (_next_input.length) {
						_next_input.click();
						_next_input.focus();
					}
				}
			}
		})
		$('.exceltable .edit_cell input.txt').keyup(function(e){
			var code = e.keyCode || e.which;
			if(code == 27) {
				var input = $(this), td = input.closest('.edit_cell'), _tr = td.closest('tr'), _value = $('.value', td);
				//_value.text(input.val());
				input.val(_value.text());//
				td.removeClass('edit');
			}
		})
		$(".thcol").click(function(){
			var _th = $(this), col = _th.data('class');
			$('.'+col).addClass('hidden');
			$('.show'+col).removeClass('hidden');
		})
		$('.managecols .showcol').click(function(){
			var _td = $(this), col = _td.data('class');
			$('.'+col).removeClass('hidden');
			$('.show'+col).addClass('hidden');
		})
		$('.department').on('click', '.employee-add', function(){
			var a = $(this), _url = a.attr('href');
            a.ajaxpopup({
                form: {
                    submit: function(response) {
                        location.reload();
                    }
                },
                title: 'Добавить сотрудника в подразделение '+a.text(),
                url: _url,
                dialog: {
                    minWidth: 700,
                    width: 700
                },
            });
            return !1;
		})
		
		$('.employee-name').click(function(){
			var a = $(this), _url = a.attr('href');
            a.ajaxpopup({
                form: {
                    submit: function(response) {
                        location.reload();
                    }
                },
                title: 'Посмотреть показатели '+a.text(),
                url: _url,
                dialog: {
                    minWidth: 700,
                    width: 700
                },
            });
            return !1;
		})
		$('.move-employee').click(function(){
			var a = $(this), _url = a.attr('href');
            a.ajaxpopup({
                form: {
                    submit: function(response) {
                        location.reload();
                    }
                },
                title: 'Перенести из подразделения',
                url: _url,
                dialog: {
                    minWidth: 500,
                    width: 500
                },
            });
            return !1;
		})
		$('.remove-department').click(function(){
			var a = $(this), _url = a.attr('href');
            a.ajaxpopup({
                form: {
                    submit: function(response) {
                        location.reload();
                    }
                },
                title: 'Вывести из подразделения',
                url: _url,
                dialog: {
                    minWidth: 700,
                    width: 700
                },
            });
            return !1;
		})
	})
</script>
{/literal}
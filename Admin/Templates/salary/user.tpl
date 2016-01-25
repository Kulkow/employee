{if false == $isajax}
	{include file='menu.tpl'}
	{include file='timeout.tpl'}
	{include file='salary/filter.tpl'}
	<h1>Зарплаты</h1>
	{if null !== $employee}
		<h2>{$employee.name}</h2>
	{/if}
{else}
	<div id="small_user_filter" data-user="{$user_id}" class="small_filter">
			{$filter->open()}
				<div class="mounts">
				{foreach from=$period.mounts item=month}
					{assign var=mstr value="2015-`$month`-01"}
					<div class="mount">
						{$filter->label("month-`$month`", $mstr|date_format:'%B')}
						{$filter->radiobox('month', $month,['class' => 'radio'])}
					</div>
				{/foreach}
				</div>
				<div class="years">
				{foreach from=$period.years item=year}
					<div class="year">
						{$filter->label("year-`$year`", $year)}
						{$filter->radiobox('year', $year,['class' => 'radio'])}
					</div>
				{/foreach}
				</div>
			</div>
			{$filter->security()}
			{$filter->close()}
	</div>
{/if}
<div id="wrapper_user_salary">
	<div class="list" style="width:{if false == $isajax}1000px;{else}100%;{/if}">
		{if false !== $is_rule}
			<div style="position: relative; overflow: hidden; margin:0 0 7px 0;">
                <a href="/admin/employee/calculator/base/{$user_id}?month={$cmount}&amp;year={$cyear}" title="Рассчитать оклад за испытательный срок" style="float:left;" class="btn it-calculator">Калькулятор</a>
				<a href="/admin/employee/eplan/add/{$user_id}" title="Добавить показатель пользователю" class="add-plan btn icon-add">Добавить показатель</a>
			</div>
		{/if}
		<table class="table suser">
			<thead class="thead" data-section="plans">
				<tr>
					<th colspan="8" class="th expanded"><span class="icon-expand"></span>Показатели</th>
				</tr>
				<tr>
					<th>Показатели</th>
					<th width="70px">Влияние/ставка</th>
					<th class="pf-td" width="70px">План</th>
					<th class="pf-td" width="70px">Факт</th>
					<th width="100px">Выполнение</th>
					<th width="150px">Ставка/общий процент</th>
					<th class="price-td" width="50px">
						{if false !== $is_rule}
						    Начислено
						{else}
						    {if false !== $is_main}
						        <span id="show_all_money" title="Посмотреть все цифры">Начислено</span>
						    {/if}	
						{/if}
					</th>
					<th class="actions" width="70px"></th>
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
					{if ! isset($_plan.hidden)}
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
						<td class="tdvalue td-edit-value">
							<div class="value"><span class="digital">{$_plan.value|round}</span> %</div>
                            {if false != $is_rule}
                                <div class="hidden _editor">
                                    <input class="txt" data-id="{$_plan.id}" name="Eplan[value]" value="{$_plan.value|round}"  />
                                </div>
                            {/if}
						</td>
						<td class="tdplan">{$_plan.plan|price_format}</td>
						<td class="tdfact">{$_plan.fact|price_format:0}</td>
						<td class="tdtempo">
							{if $_plan.plan > 0}
								{if 0 == $_plan.is_negative}
								    {assign var=_percent value=(($_plan.fact/$_plan.plan)*100|round)} 
                                    {$_percent|round}%
								{else}
								    {assign var=_percent value=((2-$_plan.fact/$_plan.plan)*100)|round}
                                    {$_percent|round}%
								{/if}
							{else}
								0 %
							{/if}
                            {if 0 == $_plan.is_negative}
    							{if 0 < $_plan.plan}
    								{if 0 > $_plan.tempo}
    									<span class="icon-down" title="отставание от плана">{($_plan.tempo)|round}%</span>
    								{else}
    									<span class="icon-up" title="Опережение плана">{($_plan.tempo)|round}%</span>
    								{/if}
    							{/if}
                            {else}
                                {if $_plan.plan > 0}
                                    {assign var=_tempo value=round(100-(($_plan.fact/$_plan.plan)*100))}
                                    {if 0 > $_tempo}
    									<span class="icon-down" title="отставание от плана">{$_tempo}%</span>
    								{else}
    									<span class="icon-up" title="Опережение плана">{$_tempo}%</span>
    								{/if}
                                {/if}
                            {/if}
						</td>
						{if null !== $summary_percent}
							<td rowspan="{$cplan_isbased}" class="tdtempo">
								<div class="base">
								{if false != $is_rule}
									{if null == $esalary.id}
									<a href="/admin/employee/esalary/user/{$user_id}/" class="editbase" title="выставить базовой ставки">
										0
									</a>
									{else}
										<a href="/admin/employee/esalary/edit/{$esalary.id}/" class="editbase" title="редактирование базовой ставки">
											{$esalary.base|price_format}
										</a>
									{/if}
								{else}
										{if false != $is_main}
												<span class="hidden money">{$esalary.base|price_format}</span>
										{/if}
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
								{if false != $is_rule}
										<span>{$salary.total_plan|price_format:0}</span>
								{else}
										{if false != $is_main}
												<span class="hidden money">{$salary.total_plan|price_format:0}</span>
										{/if}
								{/if}
							</td>
							{assign var=cplan_isbased value=0}
						{/if}
						<td class="actions">
							{if false != $is_rule}
								<a href="/admin/employee/eplan/edit/{$_plan.id}" title="Редактировать" class="icon-edit"></a>
								{if null == $_plan.end}
									<a href="/admin/employee/eplan/remove/{$_plan.id}" title="Прекратить действие с текущего дня" class="plan-close icon-closelock"></a>
								{/if}
								<a href="/admin/employee/eplan/clear/{$_plan.id}" title="Удалить" class="plan-remove icon-delete"></a>
							{/if}
						</td>
					</tr>
					{/if}
				{/foreach}
			{/if}
			{if 0 < $cplan_nobased}
				<tr class="it-plan heads">
					<td colspan="8">Сдельные</td>
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
						<td class="tdvalue td-edit-value">
							{if 0 == $_plan.is_discrete}
								<div class="value"><span class="digital">{$_plan.value*100}</span> %</div>
								{assign var=p_v value=$_plan.value*100}
							{else}
								{assign var=p_v value=$_plan.value|price_format}
								{if false != $is_rule}
								   <div class="value"><span class="digital">{$_plan.value|price_format}</span> за {$_plan.measurement}</div>
								{else}
									{if false != $is_main}
										<span class="hidden money">{$_plan.value|price_format} за {$_plan.measurement}</span>
									{/if}
								{/if}
							{/if}
							{if false != $is_rule}
								<div class="hidden _editor">
									<input class="txt" data-id="{$_plan.id}" name="Eplan[value]" value="{$p_v}"  />
								</div>
							{/if}
						</td>
						<td></td>
						<td class="tdfact">
							{if 0 < $_plan.fact}
								{if 0 == $_plan.is_discrete}
									{if false != $is_rule}
									   {$_plan.fact|price_format}
									{else}
									   <span class="hidden money">{$_plan.fact|price_format}</span>
									{/if}
								{else}
									{if false != $is_rule or 10 == $_plan.plan_id}
									   {$_plan.fact}
									{else}
										{if false != $is_main}
											 <span  class="hidden money">{$_plan.fact}</span>
										{/if}
									{/if}
								{/if}
							{/if}
						</td>
						<td colspan="2"></td>
						<td class="price-td">
							{if 0 < $_plan.total}
								{if false != $is_rule}
								    <span>{$_plan.total|price_format:0}</span>
								{else}
								    {if false != $is_main}
										<span class="hidden money">{$_plan.total|price_format:0}</span>
								    {/if}
								{/if}
							{/if}
						</td>
						<td class="actions">
						    {if null != $is_rule}
								<a href="/admin/employee/eplan/edit/{$_plan.id}" title="Редактировать" class="icon-edit"></a>
								{if null == $_plan.end}
									<a href="/admin/employee/eplan/remove/{$_plan.id}" title="Прекратить действие с текущего дня" class="plan-close icon-closelock"></a>
								{/if}
								<a href="/admin/employee/eplan/clear/{$_plan.id}" title="Удалить" class="plan-remove icon-delete"></a>
							{/if}
						</td>
						
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
					<th colspan="{if false != $is_rule}8{else}7{/if}" class="th expanded"><span class="icon-expand"></span> Депремирования и бонусы
						{if false !== $is_rule}
						<a href="/admin/employee/bonus/removeall/{$user_id}?month={$cmount}&amp;year={$cyear}&amp;salary_id={$salary_id}" data-salary="{$salary_id}" title="Отменить все опоздания и неотработки" id="remove_all_bonus" class="small icon-clear" style="float:right; margin:0 19px 0 0;"></a>
						{/if}
					</th>
				</tr>
				<tr class="off">
					<th>Причина</th>
					<th width="250px">Кто добавил</th>
					<th width="150px">Дата</th>
					<th width="65px">Сумма</th>
					<th class="actions" width="70px">
						<div id="show_cancel" class="icon-toggle" title="Показать отмененные"></div>
					</th>
				</tr>
			</thead>
			<tbody class="tbody section-bonus">
			{foreach from=$bonuses item=bonus}
				{include file='bonus/tr.tpl'}
			{/foreach}
			</tbody>
		</table>
		<!-- TOTAL -->
		{if false != $is_rule or false != $is_main}
		<table class="table suser">
			<tbody>
				<tr class="it-plan total max">
					<td colspan="{if false != $is_rule}6{else}5{/if}">Мог бы заработать</td>
					<td class="tdsumma" width="65px">
						{if false != $is_rule}
						    <b id="max_salary">{$salary.max|price_format:0}</b>
						{else}
						    {if false != $is_main}
							    <b id="max_salary" class="hidden money">{$salary.max|price_format:0}</b>
							{/if}
						{/if}
					</td>
					<td width="70px"></td>
				</tr>
				<tr class="it-plan total">
					<td colspan="{if false != $is_rule}6{else}5{/if}">Заработал</td>
					<td class="tdsumma" width="65px">
						{if false != $is_rule}
						    <span id="total_salary">{$salary.total|price_format:0}</span>
						{else}
						    {if false != $is_main}
							    <span id="total_salary" class="hidden money">{$salary.total|price_format:0}</span>
							{/if}
						{/if}
					</td>
					<td width="70px"></td>
				</tr>
			</tbody>
		</table>
		{/if}
		{if true == $salary.allow_out}
			{assign var=allow_out value=true}
		{else}
			{assign var=allow_out value=false}
			{assign var=allow_errors value=$salary.allow_out_errors}
		{/if}
		{if false == $allow_out}
			<p style="color:red;margin: 10px 0;">{$allow_errors}</p>
		{/if}
		
		<table class="table suser">
			<tbody class="tbody">
			{if 0 != $employee.is_vax}
				<tr class="section-vax">
					<td>Налог</td>
					<td class="tdvax" width="65px" style="text-align:right">
						{if false !== $is_rule}
							{if isset($salary.id)}
								{if 0 == $salary.vax}
									<span class="icon-vax red">не указан</span>
								{else}
									<span class="icon-vax green">{$salary.vax|price_format}</span>
								{/if}
								<div class="dvax hidden">
									<input class="txt vax" data-salary="{$salary.id}" data-user="{$user_id}" name="vax" style="width:40px" value="{$salary.vax|price_format}" />
								</div>
							{/if}
						{else}
							{if false != $is_main}
								<span class="value money hidden">{$salary.vax|price_format}</span>
						    {/if}
						{/if}
					</td>
					<td width="70px">
					</td>
				</tr>
			{/if}
			{if isset($salary.id)}
				<tr>
					<td>Выдано</td>
					<td width="65px" class="tdout" style="text-align:right">
						{if isset($salary.out_all)}
							{assign var=_out value=$salary.out+$salary.out_all}
							{if false !== $is_rule}
								<span class="icon-out">{$_out|price_format}</span>
						    {else}
								{if false != $is_main}
								   <span class="icon-out money hidden">{$_out|price_format}</span>
								{/if}
						    {/if}
						{else}
							{if false !== $is_rule}
							    <span class="icon-out">{$salary.out|price_format}</span>
						    {else}
							    {if false != $is_main}
								   <span class="icon-out money hidden">{$salary.out|price_format}</span>
								{/if}
							{/if}
						{/if}
					</td>
					<td width="70px">
					</td>
				</tr>
				<tr>
					<td>Осталось выдать</td>
					<td width="65px" class="tdbalance" style="text-align:right">
						{if false !== $is_rule}
							<span class="icon-balance">{$salary.balance|price_format}</span>
						{else}
						   {if false != $is_main}
						       <span class="icon-balance money hidden">{$salary.balance|price_format}</span>
						    {/if}
						{/if}
					</td>
					<td width="50px">
					</td>
				</tr>
			{/if}
			</tbody>
		</table>
		
		<div class="wrapper">
			<a href="/admin/faq/4/" title="FAQ" target="_blank" style="float:right;">FAQ</a>
			{$addform->open('/admin/employee/bonus/add')}
			<div class="row inline variables">
				{foreach from=$valiebles item=variable}
					<div class="btn {if $variable.amount>0}red{else}blue{/if} variable" data-ammount="{$variable.amount}" data-action="{$variable.action}">{$variable.name}</div>
				{/foreach}
				{if false != $is_main}
					<div class="btn red variable add-plus-btn" data-action="add-plus">Прошу оплатить .... часов за</div>
				{/if}
			</div>
			<div class="row inline">
				<div class="row inline BonusAdd-type-block" style="float:left; clear:none; ">
					{$addform->label('type', 'Причина')}
					{$addform->text('type', ['class' => 'txt', 'style' => 'width:200px'])}
				</div>
				<div class="row inline BonusAdd-amount-block" style="float:left; clear:none;">
					{$addform->label('amount', 'Сумма')}
					{$addform->text('amount', ['class' => 'txt price', 'style' => 'width:80px'])}
				</div>
				<div class="row inline BonusAdd-skype_time-block hidden" style="float:left; clear:none;">
					{$addform->label('skype_time', 'часов')}
					{$addform->text('skype_time', ['class' => 'txt price', 'style' => 'width:80px'])}
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
						<input type="text" id="bonus-hour" name="hour" class="txt" style="width:56px" /> часов
					</div>
					<div class="row inline" style="float:left; clear:none; line-height: 2; margin: 0 5px 0 0;">
						по ставке <span id="price_hour" data-price="{($price_hour/100)|round}"><b>{$price_hour|price_format:0}</b><span>
					</div>
					<div class="row inline" style="float:left; clear:none;">
						<button type="submit" id="calculate_hour" class="btn green">Рассчитать</button>
					</div>
					
					<div class="row inline" style="float:left; clear:none; margin: 0 10px 0 42px; line-height: 2;">
						<input type="text" id="bonus-percent" name="percent" class="txt" style="width:56px" /> %
					</div>
					<div class="row inline" style="float:left; clear:none; line-height: 2; margin: 0 5px 0 0;">
						от оклада <span id="bonus-percent-oklad" data-oklad="{($oklad/100)|round}"><b>{$oklad|price_format:0}</b><span>
					</div>
					<div class="row inline" style="float:left; clear:none;">
						<button type="submit" id="calculate_percent_d" class="btn green">Рассчитать</button>
					</div>
				</div>
			{/if}
		</div>
	</div>
	
	{if false == $isajax}			
		{if false != $is_rule}
		<div class="managertimesheet" style="overflow:hidden; width:1000px; margin: 10px 0;">
			<h3>Подразделения в которых числиться сотрудник для рассчета планов</h3>
			<table class="table">
				<tr class="it-department">
					<th>Подразделение</th>
					<th> 
						<a href="/admin/employee/employee/adddepartment/{$user_id}" class="icon-add" title="Добавить подраделение"></a>
					</th>
				</tr>
				{foreach from=$departments item=department}
				<tr class="it-department" data-department="{$department.department_id}">
					<td>{$department.department}{if null != $department.end}<b>(до {$department.end})</b>{/if}</td>
					<td class="actions {if $employee.department_id == $department.department_id}checked{/if}">
						{if null}
							<a href="/admin/employee/employee/defaultdepartment/{$user_id}?department_id={$department.department_id}" title="Отображать в этом подраделении" class="icon-check"></a>
						{/if}
						<a href="/admin/employee/employee/changedepartment/{$user_id}" class="icon-play move" title="Перенести в другое подраделение"></a>
                        {if null == $department.end}
                            <a href="/admin/employee/employee/remove/{$department.rowid}" title="Прекратить действие" class="plan-close icon-closelock"></a>
						{else}

                        {/if}
						<a href="/admin/employee/employee/clear/{$department.rowid}" class="icon-delete small_mini" title="удалить из этого подраделения"></a>
					</td>
				</tr>
				{/foreach}
				{if null}
				<tr>
					<th>Начальник в подразделениях</th>
					<th></th>
				</tr>
				{foreach from=$dchief item=department}
				<tr class="it-department" data-department="{$department.id}">
					<td>{$department.name}</td>
					<td class="actions {if $employee.department_id == $department.id}checked{/if}">
						<a href="/admin/employee/employee/defaultdepartment/{$user_id}?department_id={$department.id}" title="Отображать в этом подраделении" class="icon-check"></a>
					</td>
				</tr>
				{/foreach}
				{/if}
			</table>
		</div>
		{/if}
	{/if}
	
	{if false == $isajax}
	<div class="managertimesheet" style="overflow:hidden; width:{if false == $isajax}1000px;{else}100%;{/if}">
			<h2>Расписание</h2>
			<div style="overflow: hidden; margin: 0 0 15px 0;">
				{if false != $is_rule}
				    <a href="/admin/employee/timemanager/add/{$user_id}" class="icon-time btn add-timemanager">Задать график работы</a>
				{else}
				    {if false != $is_chief}
					    {if false == $is_main}
						    <a href="/admin/employee/timemanager/add/{$user_id}" class="icon-time btn add-timemanager">Задать график работы</a>
						{/if}
				    {/if}
				{/if}
			</div>
			<table class="table">
			{foreach from=$tmanager item=sheet}
				<thead>
					<tr>
						<th>Расписание</th>
						<th colspan="8">с {$sheet.since|date_format:"%d.%m.%Y"} по {if '' == $sheet.till}настоящее время{else}{$sheet.till}{/if}</th>
					</tr>
				</thead>
				<tr class="it-timemanager">
					<td></td>
					{foreach from=$sheet.lang item=day}
						<td>{$day.lang}</td>	
					{/foreach}
					<td>
						
					</td>
				</tr>
				<tr class="it-timemanager">
					<td>с {$sheet.since|date_format:"%d.%m.%Y"}</td>
					{foreach from=$sheet.lang item=day}
						<td>{$day.start|date_format:"H:i"}-{$day.end|date_format:"H:i"}</td>	
					{/foreach}
					<td>
						{if false != $is_rule}
						    <a href="/admin/employee/timemanager/edit/{$sheet.id}" class="icon-edit"></a>
						{else}
					        {if false != $is_chief}
								{if false == $is_main}
								    <a href="/admin/employee/timemanager/edit/{$sheet.id}" class="icon-edit"></a>
								{/if}
							{/if}			
						{/if}
					</td>
				</tr>
			{/foreach}
			</table>
			
			<table class="table" style="width:1000px">
				<tr>
					<th width="283px">Дата</th>
					<th colspan="2" width="100px">График/Факт</th>
					<th>Опоздания или ранний уход</th>
				</tr>
			{foreach from=$times item=_time}
				{assign var=class_time value=''}
				{assign var=is_plus value=0}
				{if '' == $_time.s and '' == $_time.e}
					{if '' != $_time.start}	
						{assign var=class_time value='plus'}
						{assign var=is_plus value=1}
					{/if}					
				{/if}
				{if isset($_time.recast) && 0 < $_time.recast}
					{assign var=class_time value='plus'}
				{/if}
				{assign var=class_time value=''}{* hack close*}
				<tr class="{if 0 < $_time.skipping|@count}skipping{/if} it-time {$class_time}">
					<td  class="tddate">
						{$_time.date|date_format:"%d.%m.%Y"}<br /><i>{$_time.lang}</i>
					</td>
					<td class="tdtime">
						<b>{$_time.s|date_format:"H:i"}</b>
						<p class="fact">{$_time.start|date_format:"%H:%M:%S"}</p>
					</td>
					<td class="tdtime">
						<b>{$_time.e|date_format:"H:i"}</b>
						{if isset($_time.notclose) && true == $_time.notclose}
							<p class="fact">{$_time.finish|date_format:"%d.%m.%Y %H:%M:%S"}</p>
						{else}
							<p class="fact">{$_time.finish|date_format:"%H:%M:%S"}</p>
						{/if}
					</td>
					<td>
						{if 0 < $_time.skipping|@count}
							<ul>
							{foreach from=$_time.skipping item=_skip}
								<li>{$_skip.type}</li>
							{/foreach}
							</ul>
						{else}
							{if false}
								{assign var=t_date value=$_time.date}
								{if 1 == is_plus and '' != $_time.finish and false !== $is_main}
									{if ! isset($bonus_plus.$t_date)}
										<div class="plus-bonus-block">
											<label for="BonusPlus-hour">Запросить оплату</label>
											<input type="text" id="BonusPlus-hour" class="txt" name="BonusPlus[hour]" value="{floor($_time.hour)}" style="width: 30px;" />
											<select name="BonusPlus[minute]" class="select-minute {($_time.hour - floor($_time.hour))}" id="BonusPlus-minute">
												<option value="0" {if 0 == ($_time.hour - floor($_time.hour))}selected{/if}>00</option>
												<option value="30" {if 0.5 == ($_time.hour - floor($_time.hour))}selected{/if}>30</option>
											</select>
											<a href="/admin/employee/bonus/plus/{$user_id}/" class="add-plus icon-ok" title="Запросить бонус за отработанное время" data-date="{$_time.date}" data-hour="{floatval($_time.hour)}"></a>
										</div>
									{else}
										Отработано {floatval($_time.hour)} ч
									{/if}
								{/if}
								{if isset($_time.recast) && 0 < $_time.recast}
									{if ! isset($bonus_plus.$t_date) && false !== $is_main}
										<div class="plus-bonus-block" title="Переработка">
											<label for="BonusPlus-hour">Запросить оплату</label>
											<input type="text" id="BonusPlus-hour" class="txt" name="BonusPlus[hour]" value="{$_time.recast}" style="width: 30px;" />
											<a href="/admin/employee/bonus/plus/{$user_id}/" class="add-plus icon-ok" title="Запросить бонус за отработанное время" data-date="{$_time.date}" data-hour="{$_time.recast}"></a>
										</div>
									{else}
										Переработка {$_time.recast} ч
									{/if}
								{/if}
							{/if}
						{/if}
					</td>
				</tr>
			{/foreach}
			</table>
	</div>
	{/if}
</div>
<script src="/js/admin/salary_user.js?v={$smarty.now}"></script>

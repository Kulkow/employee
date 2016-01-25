{include file='menu.tpl'}
{include file='timeout.tpl'}
{include file='salary/filter.tpl'}
{if false != $is_rule}
	<h2>Общая прибыль</h2>
	<div style="width:90%;">
		<table id="totalProfit" class="exceltable">
			<tr>
				<td></td>
				{foreach from=$months item=col}
					{assign var=_col value=strtotime("2015-`$col`-01")}
					<th class="thcol a-center all-{$col}" data-class="all-{$col}"  style="min-width:50px; max-width: 50px;">{$_col|date_format:"%B"}</th>
				{/foreach}
			</tr>
			{assign var=prev value=0}
			{foreach from=$all_col key=year item=m}
				<tr>
					<th class="a-right set-all-year">
						{$year}
					</th>
					{foreach from=$months item=_col}
						{assign var=profit value=0}
						{assign var=szp value=0}
						{assign var=col value=strtotime("`$year`-`$_col`-01")}
						<td class="a-right all-{$col} col-" data-class="all-{$col}" style="min-width:50px; max-width: 50px; vertical-align: bottom; position: relative;">
							{if isset($all_salary.$col)}
								{assign var=psheets value=$all_salary.$col}
								{if isset($psheets.summa)}
									{$psheets.summa|price_format:0}
									{assign var=szp value=$psheets.summa}
								{/if}
							{/if}
							{if isset($all.$col)}
								{assign var=psheets value=$all.$col}
								{if isset($psheets.profit)}
									{if 0 < $szp}<br />{/if}
									{if $col == $cstamp}
										{$factOrg|price_format:0}
										{assign var=profit value=round($factOrg)}
									{else}
										{$psheets.profit|price_format:0}
										{assign var=profit value=$psheets.profit}
									{/if}
								{/if}
							{/if}
							{if 0 < $profit and 0 < $szp}
								<span class="percent" style="position:  absolute; left:0; top: 50%; display: block; height: 10px; margin: -10px 0 0 0;"><b>{round(($szp/$profit)*100)}</b>%</span>
							{/if}
						</td>
						{assign var=prev value=$psheets.profit|default:0}
					{/foreach}
				</tr>
			{/foreach}
			</tbody>
		</table>
	</div>
{/if}
<div class="page_module salary">
    <h1>Зарплаты</h1>
    <div id="salary_block">
        <h3>Сотрудники</h3>
			{assign var=_month value=$cmount}
			{assign var=_year value=$cyear}
			{if false != $is_rule}
				{if null !== $is_generate}
					<div class="btn hidesalaryout"><span>Скрыть</span> тех кому выдана зарплата</div>
				{/if}
			{/if}
			<div class="managecols">
				<div class="managecols-row">
					<span class="firstcol col">Показать колонки</span>
					<span data-class="tdbase" class="hidden showcol col showcol-tdbase" title="Показать">Ставка</span>
					<span data-class="tdmax" class="hidden showcol col showcol-tdmax" title="Показать">Мог бы</span>
					<span data-class="tdbonus" class="hidden showcol col showcol-tdbonus" title="Показать">Депремирования</span>
					<span data-class="tdincome" class="hidden showcol col showcol-tdincome" title="Показать">Зарплата</span>
					<span data-class="tdavans" class="hidden showcol col showcol-tdavans" title="Показать">Аванс</span>
					<span data-class="tdtax" class="hidden showcol col showcol-tdtax" title="Показать">Налог</span>
					<span data-class="tdbalance" class="hidden showcol col showcol-tdbalance" title="Показать">Осталось на руки</span>
					<span data-class="tdsout" class="hidden showcol col showcol-tdsout" title="Показать">Выдано</span>
					<span data-class="tdout" class="hidden showcol col showcol-tdout" title="Показать">Дата выдачи</span>
				</div>
			</div>
			
			<div class="hidenlevels">
				<span class="hidelevel active" data-level="1" title="Показать всех сотрудников 1 уровня">1</span>
				<span class="hidelevel" data-level="2" title="Показать всех сотрудников 2 уровня">2</span>
				<span class="hidelevel" data-level="3" title="Показать всех сотрудников">3</span>
                {if false != $is_rule}
					<a class="btn green" id="recruit" href="/admin/employee/recruit/" title="Принять на работу">Принять на работу</a>
				{/if}
				{if false != $is_rule}
					{if false == $is_current}
						{if null == $is_generate}
							<a href="/admin/employee/salary/cron?month={$_month}&amp;year={$_year}" class="btn green" style="float:right;" title="">Зафиксировать данные</a>
						{else}
							<a href="/admin/employee/salary/cron?month={$_month}&amp;year={$_year}" class="btn green" style="float:right;" title="">Обновить данные</a>
							<a href="/admin/employee/plansheet/generate?month={$_month}&amp;year={$_year}" class="btn" style="float:right; margin:0 10px 0 10px;" title="">Обновить факт</a>
							{if null}
							<a href="/admin/employee/salary/remove?month={$_month}&amp;year={$_year}" class="btn green" style="float:right;" title="">Удалить данные</a>
							{/if}
							<a href="/admin/employee/salary/print?month={$_month}&amp;year={$_year}" class="btn hidden" style="float:right;" title="">Печать</a>
						{/if}
					{/if}
				{/if}
			</div>
            <table style="width:100%;" class="table">
                <thead>
				<tr>
                    <th width="350px" class="tdname">Подразделение {if false != $is_rule}<a href="/admin/employee/department/add/" title="Добавить подразделение" class="icon-add department-add"></a>{/if}</th>
                    <th class="th tdprice tdbase" data-width="40px" data-class="tdbase">Ставка</th>
                    <th class="th tdprice tdmax" data-width="40px" data-class="tdmax">Мог бы</th>
                    <th class="th tdprice tdbonus" data-width="40px" data-class="tdbonus">Депремирования</th>
                    <th class="th tdprice tdincome" data-width="40px" data-class="tdincome">Зарплата</th>
					{if false}
						<th class="th tdprice tdavans" data-width="70px" data-class="tdavans">Аванс</th>
					{/if}
                    <th class="th tdprice tdtax" data-width="40px" data-class="tdtax">Налог</th>
                    <th class="th tdprice tdsout" data-width="70px" data-class="tdsout">Выдано</th>
					<th class="th tdprice tdbalance" data-width="70px" data-class="tdbalance">Осталось на руки</th>
					<th class="th tdout" data-width="60px" data-class="tdout">Дата выдачи</th>
					{if null !== $is_generate}
						<th class="th tdcode" data-width="60px" data-class="tdcode">Код</th>
					{/if}
					<th></th>
                </tr>
				</thead>
				{assign var=department_number value=0}
				
				{assign var=total_department_summary value=0}
				{assign var=total_balance value=0}
				
				{assign var=prev_department value=0}
				{assign var=prev_department_level value=0}
				{assign var=prevlevel value=null}
				{assign var=previd value=0}
				{assign var=parent value=0}
				
				{foreach from=$tree item=department}
					{if null == $prevlevel}
						{assign var=parent value=0}
					{else}
						{if $prevlevel < $department.level}
							{assign var=parent value=$previd}
						{else}
							{if $prevlevel > $department.level}
								{assign var=parent value=0}
							{else}
								
							{/if}
						{/if}
					{/if}
					{assign var=prevlevel value=intval($department.level)}
					{assign var=previd value=intval($department.id)}
					{assign var=cpath value="dp_`$parent` dp_`$department.id`"}
					
					{assign var=did value=$department.id}
					
                    {assign var=department_number value=$department.number}
					{assign var=total_department value=0}
					{assign var=total_department_balance value=0}
						{foreach from=$department.users item=user}
							{assign var=user_id value=$user.id}
							{if isset($salarys.$user_id)}
								{assign var=salary value=$salarys.$user_id}
							{else}
								{assign var=salary value=null}
							{/if}
							{assign var=total_department value=($total_department + $salary.total)}
							{assign var=total_department_balance value=($total_department_balance + $salary.balance)}
						{/foreach}
						<tbody>
							<tr class="dname it-department level{$department.level} {$cpath}" data-class="expanded" data-id="{$department.id}">
								<td class="firsttd tdname dname expanded2 active2" data-department="{$department.id}">
									<a href="/admin/employee/department/{$department.id}" class="name icon-expand" title="Редактировать Подразделение">
										{if null != $department.number}<b>{$department.number}.</b>{/if} {$department.name}
									</a>
								</td>
								<td class="tdbase"></td>
								<td class="tdmax"></td>
                                <td class="tdbonus"></td>
                                <td class="tdprice tdincome">
									{if false != $is_rule}
										<span class="tdtotal">{$total_department|price_format:0}</span>
									{/if}
								</td>
								{if false}
									<td class="tdavans"></td>
								{/if}
								<td class="tdtax"></td>
								<td class="tdsout"></td>
								<td class="tdprice tdbalans">
									{if false != $is_rule}<span class="tdtotal">{$total_department_balance|price_format}</span>{/if}
								</td>
                                <td class="tdbalans tdout"></td>
								{if null != $is_generate}
									<td class="tdcode"></td>	
								{/if}
								<td>
									{if false != $is_rule}
										<a href="/admin/employee/department/add/{$department.id}" title="" class="icon-add"></a>
										<a href="/admin/employee/department/edit/{$department.id}" title="" class="icon-edit"></a>
										<a href="/admin/employee/department/move/{$department.id}" class="edit-number-department" title="Редактировать номер">№</a>
										<div class="edit_position hidden">
											<input name="DepartmnetEdit[number]" data-id="{$department.id}" class="txt number" value="{$department.number}"  />
										</div>
										<a href="/admin/employee/department/remove/{$department.id}" title="Удалить подразделение" class="small_mini icon-delete"></a>
									{/if}
								</td>
							</tr>
						</tbody>
						<tbody class="department{$department.id} level{$department.level}">
							{foreach from=$department.users item=user}
								{assign var=user_id value=$user.id}
								{if isset($salarys.$user_id)}
									{assign var=salary value=$salarys.$user_id}
								{else}
									{assign var=salary value=null}
								{/if}
								{if isset($salary.esalary)}
									{assign var=esalary value=$salary.esalary}
								{else}
									{assign var=esalary value=null}
								{/if}
								{if isset($salary)}
								{assign var=isvax value=2}
								{if 1 == $user.is_vax}
									{if isset($salary.vax)}
										{assign var=isvax value=1}
										{if 0 == $salary.vax}
											{assign var=isvax value=0}
										{/if}
									{/if}
								{/if}
								{if isset($salarys.$user_id)}
									{assign var=salary_id value=$salarys.$user_id}
								{else}
									{assign var=salary_id value=0}
								{/if}
								<tr data-user="{$user.id}" data-number="{$user.number}"
									class="it-employee hidden {if null != $user.fire_date}fire{/if} rang{$user.status} {if isset($salary.balance)}{if 0 == $salary.balance}salaryout{/if}{/if}">
									<td class="tdname">
                                        {if 1 == $user.number}
                                            {if 1 == $user.status}
                                                <span class="rang rang1" title="Начальник подразделения"></span>
                                            {else}
                                                <span class="rang rang_1" title="и.о. Начальника подразделения"></span>
                                            {/if}
                                        {/if}
                                        {if 2 == $user.number}
                                            {if 2 == $user.status}
                                                <span class="rang rang2" title="Заместитель начальника подразделения"></span>
                                            {else}
                                                <span class="rang rang_2" title="и.о. заместителя начальника подразделения"></span>
                                            {/if}
                                        {/if}
										{if false != $is_rule}
											<div class="hidden set-rangs">
												<ul>
													<li data-status="1" class="set-variable {if 1 == $user.status}active{/if}">Начальник</li>
													<li data-status="2" class="set-variable {if 2 == $user.status}active{/if}">Зам.</li>
													<li data-status="3" class="set-variable {if 3 == $user.status}active{/if}">Сотрудник</li>
												</ul>
											</div>
										{/if}
										</div>
										{if isset($allow_departments.$did)}
											<a href="/admin/employee/salary/help/{$user.id}?month={$_month}&amp;year={$_year}" title="{$user.name}" data-id="{$user.id}" class="get-employee icon-helps">
												{if '' != $user.number}<b>{if null != $department_number}{$department_number}.{/if}{$user.number}.</b>{/if}
												{$user.family}
											</a>
											<a href="/admin/employee/salary/user/{$user.id}?month={$_month}&amp;year={$_year}" title="{$user.name}" class="a_name">
												{$user.sname}
											</a>
										{else}
											<span  class="icon-helps" title="{$user.name}">
												{if '' != $user.number}<b>{if null != $department_number}{$department_number}.{/if}{$user.number}.</b>{/if}
												{$user.family}
											</span>
											<span  title="{$user.name}" class="a_name">
												{$user.sname}
											</span>
										{/if}
                                        {if 0 < $salary.is_request}<span class="requests">+{$salary.is_request}</span>{/if}
										{if null !== $user.fire_date and false != $is_rule}
											<a href="/admin/employee/employee/fire/{$user.id}" class="employee-fire icon-fire" title="Показывается до {$user.expire_date}">{$user.expire_date}</a>
										{/if}
										
										{if false}
										<div class="hidden">
											{foreach from=$salary.salary item=s}
												<p>{$s.name}:{$s.plan|price_format}-{$s.fact|price_format} ({$s.value})
												{if isset($s.total)}
													<b>{$s.total|price_format}</b>
												{/if}
												{if isset($s.K)}
													<i>K1={$s.K}</i>
												{/if}
												</p>
											{/foreach}
											<p>K={$salary.K}</p>
											<p>summary_percent={$salary.summary_percent}</p>
											<p>total_plan=<b>{$salary.total_plan|price_format}</b></p>
											<p>total_bonus={$salary.total_bonus|price_format}</p>
											<p>plus={$salary.plus|price_format}</p>
											<p>itogo_deprem=<b>{($salary.plus+$salary.total_bonus)|price_format}</b></p>
											<p>base={$salary.base|price_format}</p>
											<p>income={$salary.income|price_format}</p>
										</div>
										{/if}
									</td>
									<td class="tdbase tdprice">
										{if false != $is_rule}
											{if isset($salary.basic)}
												<span class="tooltip-html" title="HELP">{$salary.basic|price_format}
													<div class="hidden">
														<p><b>{$salary.base_hour|price_format}р за ч</b></p>
														{if $salary.base_min != $salary.base_max}
															{$salary.base_min|price_format} - {$salary.base_max|price_format}
														{else}
															{$salary.base_min|price_format}
														{/if}
													</div>
												</span>
											{else} 
												-
											{/if}
										{/if}
									</td>
									<td class="tdmax tdprice">
										{if false != $is_rule}
											{$salary.max|price_format:0}
										{/if}
									</td>
                                    <td class="tdbonus tdprice">
										{if false != $is_rule}
											<a href="/admin/employee/bonus/view/{$user_id}?month={$_month}&amp;year={$_year}" title="Узнать о депремированиях" class="popup bonus">
											{if isset($salary.total_bonus)}
												{($salary.total_bonus + $salary.plus)|price_format:0}
											{else}
												{($salary.plus - $salary.bonus)|price_format:0}
											{/if}
											</a>
										{/if}
									</td>
									<td class="tdincome tdprice">
										{if false != $is_rule}
											<span class="tooltip-html" title="HELP">
											{if null == $is_generate}
												{$salary.total|price_format:0}
											{else}
												{$salary.total|price_format:0}
											{/if}
												<div class="hidden">
													{assign var=plan_based value=$salary.help_plans.is_plan_based}
													{assign var=plan_nobased value=$salary.help_plans.no_plan_based}
													{assign var=cplan_isbased value=$salary.help_plans.is_plan_based|@count}
													{assign var=cplan_nobased value=$salary.help_plans.no_plan_based|@count}
													{assign var=summary_percent value=$salary.summary_percent}
													{if isset($prev_salarys.$user_id)}
														{assign var=prev_salary value=$prev_salarys.$user_id}
													{else}
														{assign var=prev_salary value=null}
													{/if}
													{if isset($salary.total_bonus)}
														{assign var=summary_bonus value=$salary.total_bonus + $salary.plus}
													{else}
														{assign var=summary_bonus value=$salary.plus - $salary.bonus}
													{/if}			
													{include file='salary/tooltip_table.tpl'}
												</div>
											</span>
										{/if}
									</td>
									{if false}
									<td class="tdavans tdprice">
										{if false != $is_rule}
											{if null == $is_generate}
													<div class="tdavansb">
														<input data-salary="0" data-date="{$_year}-{$_month}-01" data-avans="{$salary.avans_id}" data-user="{$user_id}" name="SalaryAvans[avans]" class="txt avans" value="{$salary.avans|price_format}" type="text" />
													</div>
													{if null != $salary.avans_log}
														<span class="tooltip-html" title="HELP">
															{$salary.avans_log_summa|price_format}
															<div class="hidden">
																<p>Выдавался аванс:</p>
																{foreach from=$salary.avans_log item=log}
																<p>{$log.time} - <b>{$log.avans|price_format}</b></p>
																{/foreach}
															</div>
														</span>
													{/if}
											{else}
													{if $salary.balance !== '0'}
														{if $salary.avans !== '0'}
															<div class="hidden2">
																<input data-salary="{$salary.id}" data-user="{$user_id}" name="SalaryAvans[avans]" class="txt avans" value="{$salary.avans|price_format}" type="text" />
															</div>
														{else}
															<div class="hidden2">
																<input data-salary="{$salary.id}" data-user="{$user_id}" name="SalaryAvans[avans]" class="txt avans" type="text" />
															</div>
														{/if}
													{else}
														{$salary.avans|price_format}
													{/if}
													{if null != $salary.avans_log}
														<span class="tooltip-html" title="{$salary.avans_log_summa|price_format}">
															<div class="hidden">
																<p>Выдавался аванс:</p>
																{foreach from=$salary.avans_log item=log}
																<p>{$log.time} - <b>{$log.avans|price_format}</b></p>
																{/foreach}
															</div>
														</span>
													{/if}
											{/if}
										{/if}
									</td>
									{/if}
									<td class="tdvax tdprice">
										{if false != $is_rule}
											{if null == $is_generate}
												{if 1 == $isvax}
														<div class="green icon-vax" data-salary="{$salary.id}" data-user="{$user_id}" title="Изменить">{$salary.vax|price_format:0}</div>
														<div class="hidden">
															<input data-salary="0" data-date="{$_year}-{$_month}-01" data-vax="{$salary.vax_id}" data-user="{$user_id}" name="SalaryVax[vax]" class="txt vax" value="{if 0 < $salary.vax}{$salary.vax|price_format:0}{/if}" type="text" />
														</div>
												{else}
													{if 0 == $isvax}
														<div class="icon-vax" data-salary="0" data-user="{$user_id}" title="не указан">не указан</div>
														<div class="hidden">
															<input data-salary="0" data-date="{$_year}-{$_month}-01" data-vax="{$salary.vax_id}" data-user="{$user_id}" name="SalaryVax[vax]" class="txt vax" value="{if 0 < $salary.vax}{$salary.vax|price_format:0}{/if}" type="text" />
														</div>
													{/if}
												{/if}
											{else}
												{if 1 == $isvax}
													<div class="green icon-vax" data-salary="{$salary.id}" data-user="{$user_id}" title="Изменить">{$salary.vax|price_format:0}</div>
													<div class="hidden">
														<input data-salary="{$salary.id}" data-user="{$user_id}" name="SalaryVax[vax]" class="txt vax" value="{if 0 < $salary.vax}{$salary.vax|price_format:0}{/if}" type="text" />
													</div>
												{else}
													{if 0 == $isvax}
														<div class="icon-vax" data-salary="{$salary.id}" data-user="{$user_id}" title="не указан">не указан</div>
														<div class="hidden">
															<input data-salary="{$salary.id}" data-user="{$user_id}" name="SalaryVax[vax]" class="txt vax" value="{if 0 < $salary.vax}{$salary.vax|price_format:0}{/if}" type="text" />
														</div>
													{/if}
												{/if}
											{/if}
										{/if}
									</td>
									<td class="tdsout tdprice">
										{if false != $is_rule}
											{if null != $salary.outs}
												<span class="tooltip-html" title="HELP">
													<div class="hidden">
														<p>Выдавалось ранее:</p>
														{assign var=_summa_out value=0}
														{foreach from=$salary.outs item=log}
															{if 0 < $log.out}
																<p>{$log.time} - <b>{$log.out|price_format}</b></p>
																{assign var=_summa_out value=$_summa_out+$log.out}
															{/if}
															{if 0 < $log.avans}
																<p>{$log.time} - <b>{$log.avans|price_format}</b></p>
																{assign var=_summa_out value=$_summa_out+$log.avans}
															{/if}
														{/foreach}
													</div>
													<span>{$_summa_out|price_format}</span>
												</span>
											{/if}										
											
										{/if}
									</td>
									<td class="tdbalance tdprice">
										{if true == $salary.allow_out}
											{assign var=allow_out value=true}
										{else}
											{assign var=allow_out value=false}
											{assign var=allow_errors value=$salary.allow_out_errors}
										{/if}
										{if false != $is_rule}
											{if null != $is_generate}
												{if isset($salary.balance)}
													<div class="balanceb {if 0 == $isvax}hidden{/if}" {if false == $allow_out}title="{$allow_errors}"{/if}>
														<input {if false == $allow_out}readonly{/if} data-salary="{$salary.id}" data-user="{$user_id}" name="SalaryBalance[balance]" class="txt balance" value="{$salary.balance|price_format:0}" type="text" />
													</div>
													<div class="outb {if 0 == $isvax}hidden{/if} {if 0 == $salary.out}hidden{/if}" title="Выдано">
														<input {if false == $allow_out}readonly{/if} data-salary="{$salary.id}" data-user="{$user_id}" name="SalaryEdit[out]" class="txt out" value="{$salary.out|price_format:0}" type="text" />
													</div>
													{if 0 == $isvax}
														<p class="novax">не указан налог</p>
													{/if}
												{/if}
											{else}
												{assign var=_balance value=$salary.total-$salary.avans}
												<div class="tdavansb" {if false == $allow_out}title="{$allow_errors}"{/if}>
													<input {if false == $allow_out}readonly{/if} data-salary="0" data-date="{$_year}-{$_month}-01" data-avans="{$salary.avans_id}" data-user="{$user_id}"
														   name="SalaryAvans[avans]" class="txt avans-balance" value="{$_balance|price_format}" type="text" />
												</div>
												<div class="tdavansb-balance {if 0 == $salary.avans_id}hidden{/if}">
													<input {if false == $allow_out}readonly{/if} data-salary="0" data-total="{$salary.total/100}" data-date="{$_year}-{$_month}-01" data-avans="{$salary.avans_id}" data-user="{$user_id}"
														   name="SalaryAvans[avans]" class="txt avans" value="{$salary.avans|price_format}" type="text" />
												</div>
											{/if}
										{/if}
									</td>
									<td class="tdout">
										{if null == $is_generate}
										-
										{else}
											{if false != $is_rule}
												{$salary.outdate|date_format:"%d.%m.%Y"}
											{/if}
										{/if}
									</td>
									{if null != $is_generate}
										<td class="tdcode">
											{if false != $is_rule}
												{$salary.series}
											{/if}
										</td>
									{/if}
									<td class="actions percent_doc_{$salary.doc_status}">
										{if false != $is_rule}
											<span class="space"></span>
											<a href="/admin/employee/employee/editor/{$user.id}" class="employee-edit icon-edit" title="Редактировать"></a>
											<a href="/admin/employee/employee/number/{$user.id}" class="edit-number-employee" title="Редактировать номер">№</a>
											<div class="edit_position hidden">
												<input name="EmployeeNumber[number]" data-id="{$user.id}" class="txt number" value="{$department.number}.{$user.number}"  />
											</div>
											{if null == $user.fire_date}
												<a href="/admin/employee/employee/fire/{$user.id}" class="employee-fire small_mini icon-delete" title="Уволить"></a>
											{/if}
											{if null}
												<a href="/admin/employee/timemanager/user/{$user.id}" class="employee-timemanager icon-time" title="Редактировать График работы"></a>
											{/if}
										{else}
											{if false != $is_bookkeper}
												<a title="Редактировать" class="employee-edit icon-edit" href="/admin/employee/employee/editor2/{$user.id}"></a>
											{else}
												<a href="/admin/employee/employee/info/{$user.id}" class="employee-info icon-info" title="Информация о сотруднике"></a>
											{/if}
											{if isset($allow_departments.$did) and $user.id !== $auth_user}
												{if isset($tmanagers.$user_id)}
													{assign var=tmanager value=$tmanagers.$user_id}
													{assign var=wtime value=$tmanager.percent}
												{else}
													{assign var=wtime value=0}
												{/if}
												<a href="/admin/employee/timemanager/user/{$user.id}" class="employee-timemanager icon-time {if 0 == $wtime}warning{/if}" title="{if 0 == $wtime && isset($tmanager)}Редактировать График работы - рабочих {$tmanager.count} часов{else}Редактировать График работы{/if}"></a>
											{else}
												{if false != $is_chief}
													<a href="/admin/employee/timemanager/view/{$user.id}" class="employee-timemanager icon-time" title="График работы"></a>
												{/if}
											{/if}
										{/if}
									</td>
								</tr>
								{/if}
								{assign var=total_balance value=($total_balance + $salary.balance)}
							{/foreach}
							{assign var=total_department_summary value=($total_department_summary + $total_department)}
					</tbody>
				{/foreach}
				{if false != $is_rule}
				<tfoot>
					<tr class="total_summary">
						<td><b>Итого по всем подразделениям:</b></td>
                        <td></td>
                        <td></td>
						<td></td>
						<td class="tdprice"><b>{$total_department_summary|price_format:0}</b></td>
						<td></td>
						<td></td>
						<td  class="tdprice">{$total_balance|price_format}</td>
						<td></td>
						<td></td>
						{if null != $is_generate}<td></td>{/if}
					</tr>
				</tfoot>
				{/if}
            </table>
    </div>
</div>
{if false != $is_rule}
<div id="srequest">
	<h3>Передача средств на расход</h3>
	{$srequest->open('/admin/employee/salary/request/')}
		<div class="row">
			{$srequest->label('amount', 'Сумма')}
			{$srequest->text('amount', ['class' =>  'txt', 'style' => 'width:150px', 'readonly'])}
		</div>
		<div class="row">
			{$srequest->label('point_id', 'Подразделение', false, ['title' => 'на какую точку будет записан данный расход'])}
			{$srequest->select('point_id', $srequestform->points, [])}
		</div>
		<div class="row">
			{$srequest->label('source_id', 'Откуда', false, ['title' => "откуда будут списаны выдаваемые средства"])}
			{$srequest->select('source_id', $srequestform->sources, [])}
		</div>
		<div class="row">
			{$srequest->label('payment_type_id', 'Способ оплаты')}
			{$srequest->select('payment_type_id', $srequestform->payments, [])}
		</div>
		<div class="row">
			{$srequest->label('accomplish', 'Провести', false, ['title' => "Если стоит расход будет проведен сразу"])}
			{$srequest->checkbox('accomplish')}
		</div>
		<div>
			{$srequest->hidden('category_id')}
			{$srequest->hidden('type_id')}
			{$srequest->hidden('coming_type')}
			{$srequest->security()}
		</div>
		<div class="row">
			{$srequest->submit('Добавить', ['class' => 'btn'])}
		</div>
	{$srequest->close()}
</div>
{/if}
{literal}
<style>
	BODY .ui-widget-overlay{background: rgba(80, 80, 80, 1) none repeat scroll 0 0;}
    .table TBODY TR:nth-child(2n) {background: #fff; }
    .table TBODY TR:hover TD { background: #FCFC9E none repeat scroll 0 0; }
    .table TBODY .it-department TD { background:#c7c7c7; }
    .table THEAD TH { background:#D0EDFF; color:#333333; }
    #salary_block .department-add { position:static; float:left; color:#333; }
</style>
{/literal}
<script src="/js/admin/salary.js?v={$smarty.now}"></script>
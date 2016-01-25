{include file='menu.tpl'}
{include file='timeout.tpl'}
{if false != $is_rule}
	{include file='plansheet/filter.tpl'}
{/if}
{if false != $is_rule}
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
			{assign var=allyear value=$filter->getData('allyear')}
			{foreach from=$all_col key=year item=m}
				<tr>
					<th class="a-right set-all-year" {if $year != $cyear}title="Выбрать год {$year} для рассчета"{/if}>
						{if $year != $cyear}
							{$filter->radiobox('allyear', $year,['class' => ''])}
						{/if}
						{$year}
					</th>
					{foreach from=$months item=_col}
						{assign var=col value=strtotime("`$year`-`$_col`-01")}
						<td class="a-right{if $filter->getData('allyear')==$year && $_col==$cmonth} active{/if} all-{$col} col-" data-class="all-{$col}">
							{if isset($all.$col)}
								{assign var=psheets value=$all.$col}
								{if isset($psheets.profit)}
									{if $col == $cstamp}
										{assign var=_f value=$factOrg}
										{$factOrg|price_format:0}{if $prev}<span title="({round($factOrg/100)}/{round($prev/100)}-1)*100)" class="icon-{if $factOrg < $prev}down">{else}up">+{/if}{(($factOrg/$prev-1)*100)|round}%</span>{/if}
									{else}
										{assign var=_f value=$psheets.profit}
										{$psheets.profit|price_format:0}
                                        {if $psheets.prev}
                                            <span title="({round($psheets.profit/100)}/{round($psheets.prev/100)}-1)*100" class="icon-{if $psheets.profit < $psheets.prev}down">{else}up">+{/if}{(($psheets.profit/$psheets.prev-1)*100)|round}%
                                            </span>
                                        {/if}
									{/if}
								{/if}
								{if isset($psheets.help) and 0 < $psheets.help}
									{if $col == $cstamp}
										{assign var=_pd value=$c_day_percent}
									{else}
										{assign var=_pd value=1}
									{/if}
									{assign var=_p value=$psheets.help}
									{assign var=_percent value=$_f/$_p}
									{if $_pd > $_percent}
										{assign var=_percent2 value=-($_pd-$_percent)*100}
                                        {assign var=_pd value=round($_pd,2)}
                                        {assign var=_f value=round($_f/100)}
                                        {assign var=_p value=round($_p/100)}
                                        {assign var=_title_a value="-(`$_pd`-`$_f`/`$_p`)*100"}
									{else}
										{assign var=_percent2 value=(-$_pd+$_percent)*100}
                                        {assign var=_pd value=round($_pd,2)}
                                        {assign var=_f value=round($_f/100)}
                                        {assign var=_p value=round($_p/100)}
                                        {assign var=_title_a value="-(`$_pd`+`$_f`/`$_p`)*100"}
									{/if}
									<div>
									{if 0 < $_percent2}
										<span class="icon-up differenc" title="{$_title_a}">+{ceil($_percent2)}%</span>
									{else}
										<span class="icon-down differenc" title="{$_title_a}">{ceil($_percent2)}%</span>
									{/if}
									<p title="Могло быть">{$psheets.help|price_format:0}</p>
									</div>
								{/if}
							{/if}
						</td>
						{assign var=prev value=$psheets.profit|default:0}
					{/foreach}
				</tr>
			{/foreach}
		</table>
	</div>
{/if}

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
    <div class="hidenlevels">
        <span class="hidelevel active" data-level="1" title="Показать всех сотрудников 1 уровня">1</span>
        <span class="hidelevel" data-level="2" title="Показать всех сотрудников 2 уровня">2</span>
        <span class="hidelevel" data-level="3" title="Показать всех сотрудников">3</span>
		{if false != $is_rule}
			<span class="hidden-calculate a" style="float:right; cursor: pointer">Показать расчет</span>
		{/if}
    </div>
    <div style="position: relative;">
        <table class="exceltable" id="plansheet">
            <thead>
                <tr>
                    <th class="solid"></th>
						{if false != $is_rule}
							{foreach from=$info_col item=col}
							<th class="thcol solid a-center col-{$col} thmount" colspan="3" data-class="col-{$col}" style="min-width:150px;">{$col|date_format:"%B %Y"}</th>
							{/foreach}
						{/if}
                    <th class="a-center col-first currmonth thmount" data-class="col-first" colspan="3">
                        {$form->getData('start')|date_format:"%B %Y"}
                    </th>
                    <th></th>
                </tr>
                <tr>
                    <th class="solid"></th>
                    {if false != $is_rule}
						{foreach from=$info_col item=col}
							<th class="thcol a-center col-{$col}" data-class="col-{$col}">план</th>
							<th class="thcol a-center col-calculate hidden col-{$col}" data-class="col-{$col}"
								title="{if isset($help_percents.$col)}{(($help_percents.$col-1)*100)|round}%{/if}">расчет</th>
							<th class="thcol a-center col-{$col}" data-class="col-{$col}">%</th>
							<th class="thcol solid a-center col-{$col}" data-class="col-{$col}">факт</th>
						{/foreach}
					{/if}
                    <th class="a-center col-first currmonth" data-class="col-first">
                        план
                    </th>
                    <th class="a-center col-first col-calculate hidden currmonth" data-class="col-first">
						{if false != $is_rule}
                        <span title="Заполнить все {round(($help_percent-1)*100)}%" class="calculate-set" data-class="col-first">расчет</span>
						{/if}
                    </th>
                    <th class="a-center col-first currmonth" data-class="col-first">
                        %
                    </th>
                    <th class="a-center col-first currmonth" data-class="col-first">
                        факт
                    </th>
                    <th></th>
                </tr>
            </thead>
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
                {assign var=bpath value="dpb_`$parent` dpb_`$department.id`"}

                {assign var=did value=$department.id}
                <tbody class="department level{$department.level}" data-id="{$department.id}">
                    <tr class="it-department dname expanded2 {$cpath}" data-class="expanded" data-id="{$department.id}">
                        <td class="firsttd solid name icon-expand active2" data-department="{$department.id}">
                            <a href="/admin/employee/plansheet/department/{$department.id}?month={$cmonth}&amp;year={$cyear}" class="it-d"><b>{$department.number}</b> {$department.name}</a>
                            {if false}
                                <a href="/admin/employee/employee/department/{$department.id}" class="employee-add icon-add"></a>
                            {/if}
                        </td>
                        <td class="colspan" colspan="{($info_col|@count + 1)*3-3}"></td>
                        <td></td>
                        <td class="col-calculate hidden">
                            {if false != $is_rule}
                                <span class="set-calculate-department" data-class="col-first" data-department="{$department.id}" title="Заполнить все планы подразделения">расчет</span>
                            {/if}
                        </td>
                        <td></td>
                        <td></td>
                    </tr>
                </tbody>
                <tbody class="department{$department.id} level{$department.level} {$bpath}">
                    {if isset($department.plans)}
                        {foreach from=$department.plans item=dplan}
                            {assign var=dplanid value=$dplan.plan_id}
                            {if 1 == $dplan.is_common}
                                {if 0 < $dplan.department_id}
                                    {assign var=key_common value="`$dplan.department_id`_`$dplanid`"}
                                {else}
                                    {assign var=key_common value=$dplanid}
                                {/if}

                                {assign var=_calculate value=0}{*текущий месяц*}

                                {foreach from=$dplan.users item=employee}
                                    {assign var=key_user value="`$employee.user_id`_`$employee.plan_id`"}

                                    {assign var=_users value=$department.users}
                                    {assign var=_user_id value=$employee.user_id}
                                    {assign var=employee_user value=$_users.$_user_id}
                                    {assign var=plan_start value=strtotime($employee.start)}
                                    <tr class="it-employee hidden it-plansheet {if null != $employee.end}end{/if}">
                                        <td class="firsttd solid">
                                            <a href="/admin/employee/eplan/user/{$employee.user_id}" title="{$employee.u_name}" class="employee-name">
                                                {$employee.u_name}
                                            </a>
											{if false != $is_rule}
												{if null == $employee.end}
													{if 0 == $department.id}
														<a href="/admin/employee/employee/adddepartment/{$employee.user_id}" title="Перести в подразделение" class="employee-add-department icon-add"></a>
													{else}
														<a href="/admin/employee/employee/changedepartment/{$employee.user_id}?department_id={$employee.department_id}" title="Перенести из подразделения" class="move-employee icon-move-employee "></a>
													{/if}
												{/if}
											{/if}
                                        </td>
                                        {assign var=key_sheet value=$employee.key}
										{if false != $is_rule}
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
												{if isset($psheets.$key_sheet) && $col >= $plan_start}
													{assign var=sheet value=$psheets.$key_sheet}
													<td class="col-{$col} {$tdclass}">
														<span class="span"> 
															{$sheet.plan_amount|price_format:0}
														</span>
													</td>
													<td class="col-{$col} col-calculate hidden {$tdclass}">
														{assign var=_calculate_it value=$plansheet->getCalculate($help_percents, $col, $sheet)}
														<span class="span">{$_calculate_it}</span>
													</td>
													<td class="col-{$col} {$tdclass}" >
														{if 0 < $sheet.plan_amount}
															{assign var=_percent value=((($sheet.fact_amount/$sheet.plan_amount) - 1)*100)}
															{assign var=_percent value=round($_percent)}
															{if 0 <= $_percent}
																{if 0 == $_percent}
																	<span class="icon-gray">{$_percent|round}%</span>
																{else}
																	<span class="icon-up">+{$_percent|round}%</span>
																{/if}
															{else}
																<span class="icon-down">{$_percent|round}%</span>
															{/if}	
														{/if}
													</td>
													<td class="col-{$col} solid {$tdclass}" >
														{if 0 < $sheet.fact_amount}
															<span class="span">
																{$sheet.fact_amount|price_format:0}
															</span>
														{else}
															-
														{/if}
													</td>
												{else}
													<td class="col-{$col} {$tdclass}">-</td>
													<td class="col-{$col} col-calculate hidden {$tdclass}">-</td>
													<td class="col-{$col} {$tdclass}">-</td>
													<td class="col-{$col} solid {$tdclass}">-</td>
												{/if}
											{/foreach}
										{/if}
                                        <td class="col-first edit_cell currmonth">
                                            {if null == $employee.end}
                                                {if 1 == $employee.is_plan_based}
                                                    <span class="value" title="Нажать для редактирования">
                                                        {assign var=edit_cell value=$form->getData("user")}
                                                        {if isset($edit_cell.$key_user)}
                                                            {$edit_cell.$key_user}
                                                        {/if}
                                                        {if isset($employee.calculate)}
                                                            {assign var=placeholder value=$employee.calculate}
                                                        {else}
                                                            {assign var=placeholder value=''}
                                                        {/if}
                                                    </span>
                                                    {$form->text("user[`$key_user`]", ["class" => "txt", "data-common" => $key_common, "data-summary" => 0, "placeholder"=> $placeholder])}
                                                {/if}
                                            {/if}
                                        </td>
                                        <td class="col-first col-calculate hidden currmonth">
                                            {if false != $is_rule}
												{if null == $employee.end}
													{$placeholder}
												{/if}
											{/if}
                                        </td>
                                        <td class="col-first currmonth">
                                            {if isset($f_users.$key_user) and null == $employee.end}
                                                {if isset($facts.$key_user)}
                                                    {if 0 < $f_users.$key_user}
                                                        {assign var=_f value=intval($facts.$key_user/100)}
                                                        {assign var=_p value=intval($f_users.$key_user)}
                                                        {if 1 == $employee.is_negative}
                                                            {assign var=_percent value=2-$_f/$_p}
                                                        {else}
                                                            {assign var=_percent value=$_f/$_p}
                                                        {/if}
                                                        {if $c_day_percent > $_percent}
                                                            {assign var=_percent2 value=-($c_day_percent-$_percent)*100}
                                                        {else}
                                                            {assign var=_percent2 value=(-$c_day_percent+$_percent)*100}
                                                        {/if} 
                                                    {else}
                                                        {assign var=_percent2 value=false}
                                                    {/if}
                                                    {if false == $_percent2}
                                                        -
                                                    {else}
                                                        {assign var=_percent2 value=round($_percent2)}
                                                        {if 0 <= $_percent2}
                                                            {if 0 == $_percent2}
                                                                <span class="icon-gray">{$_percent2|round}%</span>
                                                            {else}
                                                                <span class="icon-up">{$_percent2|round}%</span>
                                                            {/if}
                                                        {else}
                                                            <span class="icon-down">{$_percent2|round}%</span>
                                                        {/if}
                                                    {/if}
                                                {/if}
                                            {/if}
                                        </td>
                                        <td class="col-first currmonth">
                                            {if isset($facts.$key_user) and null == $employee.end}
                                                <span class="span">{$facts.$key_user|price_format:0}</span>
                                            {/if}
                                        </td>
                                        <td>
                                            {if null == $employee.end}
                                                <a href="/admin/employee/employee/remove/{$employee.rowid}" title="Прекратить принадлежность к подразделению" class="pmemployee-close icon-closelock"></a>
                                            {/if}
                                            {if isset($employee.rowid)}
                                                <a href="/admin/employee/employee/clear/{$employee.rowid}" title="Удалить из подразделения" class="pmemployee-clear icon-delete small_mini"></a>
                                            {/if}
                                        </td>
                                    </tr>
                                {/foreach}
                                <tr class="tr-summa hidden it-employee it-plansheet">
                                    {if 0 < $dplan.department_id}
                                        {assign var=key_sheet value="common_`$dplan.department_id`_`$dplan.plan_id`"}
                                        {assign var=key_common value="`$dplan.department_id`_`$dplanid`"}
                                    {else}
                                        {assign var=key_sheet value="common_`$dplan.plan_id`"}
                                        {assign var=key_common value=$dplanid}
                                    {/if}
                                    <td class="name solid">
                                        {if 0 == $dplan.pid}
                                            {if 1 == $dplan.is_plan_based}
                                                <b title="{$dplan.name}">{$dplan.name}:</b>
                                            {else}
                                                <b title="{$dplan.u_name}">{$dplan.name}:</b>
                                            {/if}
                                        {else}
                                            {if 0 == $dplan.department_id}
                                                <b title="{$dplan.name}">{$dplan.name}:</b>
                                            {else}
                                                <b title="{$dplan.name}">{$dplan.name} (Итого):</b>
                                            {/if}
                                        {/if}
                                    </td>
									{if false != $is_rule}
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
												<td class="col-{$col} col-calculate hidden {$tdclass}">
													<span class="span">{$plansheet->getCalculate($help_percents, $col, $sheet)}</span>
												</td>
												<td class="col-{$col} {$tdclass}">
													{if 0 < $sheet.plan_amount}
														{assign var=_percent value=((($sheet.fact_amount/$sheet.plan_amount) - 1)*100)}
														{assign var=_percent value=round($_percent)}
														{if 0 <= $_percent}
															{if 0 == $_percent}
																<span class="icon-gray">{$_percent|round}%</span>
															{else}
																<span class="icon-up">+{$_percent|round}%</span>
															{/if}	
														{else}
															<span class="icon-down">{$_percent|round}%</span>
														{/if}	
													{/if}
												</td>
												<td class="col-{$col} solid {$tdclass}">
													{if 0 < $sheet.fact_amount}
														<span class="span">
															{$sheet.fact_amount|price_format:0}
														</span>
													{else}
													{/if}
												</td>
											{else}
												<td class="col-{$col} {$tdclass}">-</td>
												<td class="col-{$col} col-calculate hidden {$tdclass}">-</td>
												<td class="col-{$col} {$tdclass}">-</td>
												<td class="col-{$col} solid {$tdclass}">-</td>
											{/if}											
										{/foreach}
									{/if}
                                    <td class="col-first {if false != $is_rule}edit_cell{/if} currmonth">
                                        {if false != $is_rule}
                                            {if 1 == $dplan.is_plan_based}
                                                <span class="value" title="Нажать для редактирования">
                                                    {assign var=edit_cell value=$form->getData("common")}
                                                    {if isset($edit_cell.$key_common)}
                                                        {$edit_cell.$key_common}
                                                    {/if}
                                                    {if isset($helpers.$key_sheet)}
                                                        {assign var=helper value=$helpers.$key_sheet}
                                                    {else}
                                                        {assign var=helper value=null}
                                                    {/if}
                                                    {assign var=placeholder value=$plansheet->getHelpInfoAll($help_percent,$helper)}
                                                </span>
                                                <span class="hidden">{$placeholder}</span>
                                                {$form->text("common[`$key_common`]", ["class" => "txt", "data-common" => $key_common, "data-summary" => 1,  "placeholder"=> $placeholder])}
                                                <a href="javascript:;" class="error-summary hidden"></a>
                                            {/if}
                                        {else}
                                            {if 1 == $dplan.is_plan_based}
                                                {assign var=edit_cell value=$form->getData("common")}
                                                {if isset($edit_cell.$key_common)}
                                                    <span class="value">{$edit_cell.$key_common}</span>
                                                    {$form->hidden("hidden[`$key_common`]", ["class" => "txt", "value" => $edit_cell.$key_common, "data-common" => $key_common, "data-summary" => 1])}
                                                    <a href="javascript:;" class="error-summary hidden"></a>
                                                {/if}
                                            {/if}
                                        {/if}
                                    </td>
                                    <td class="col-first col-calculate hidden currmonth">
                                        {if isset($dplan.calculate)}
                                            {assign var=placeholder value=$dplan.calculate}
                                        {else}
                                            {assign var=placeholder value=''}
                                        {/if}
										{if false != $is_rule}
											<span class="span">{$placeholder}</span>
										{/if}
                                    </td>
                                    <td class="col-first currmonth" data-common="{$key_common}">
                                        {assign var=fact_common value=0}
                                            {if ! isset($facts.$key_common)}
                                                {if isset($facts.$dplanid)}
                                                    {assign var=fact_common value=$facts.$dplanid}
                                                {/if}
                                            {else}
                                                {assign var=fact_common value=$facts.$key_common}
                                            {/if}
                                            {if isset($fact_common) && isset($commons.$key_common)}
                                                {if 0 < $commons.$key_common}
                                                    {assign var=_f value=intval($fact_common/100)}
                                                    {assign var=_p value=intval($commons.$key_common)}
                                                    {if 1 == $dplan.is_negative}
                                                        {assign var=_percent value=2-$_f/$_p}
                                                    {else}
                                                        {assign var=_percent value=$_f/$_p}
                                                    {/if}
                                                    {if 1 == $dplan.is_negative}
                                                        {assign var=_percent2 value=100-(($_f/$_p)*100)}
                                                    {else}
                                                        {if $c_day_percent > $_percent}
                                                            {assign var=_percent2 value=-($c_day_percent-$_percent)*100}
                                                        {else}
                                                            {assign var=_percent2 value=(-$c_day_percent+$_percent)*100}
                                                        {/if}
                                                    {/if}
                                                {else}
                                                    {assign var=_percent2 value=false}
                                                {/if}
                                                {if false == $_percent2}
                                                    -
                                                {else}
                                                    {assign var=_percent2 value=round($_percent2)}
                                                    {if 0 <= $_percent2}
                                                        {if 0 == $_percent2}
                                                            <span class="icon-gray">{$_percent2}%</span>
                                                        {else}
                                                            <span class="icon-up">+{$_percent2}%</span>
                                                        {/if}
                                                    {else}
                                                        <span class="icon-down">{$_percent2}%</span>
                                                    {/if}
                                                {/if}
                                            {/if}
                                    </td>
                                    <td class="col-first currmonth">
                                        {if isset($fact_common)}
                                            <span class="span">{$fact_common|price_format:0}</span>
                                        {/if}
                                    </td>
                                    <td></td>
                                </tr>
                            {else}
                                {assign var=employee value=$dplan}

                                {assign var=key_user value="`$employee.user_id`_`$employee.plan_id`"}
                                {assign var=_user_id value=$employee.user_id}

                                <tr class="it-employee hidden it-plansheet {if null != $employee.end}end{/if}">
                                    <td class="firsttd solid">
                                        <a href="/admin/employee/eplan/user/{$employee.user_id}" title="{$employee.u_name}" class="employee-name">
                                            {$employee.u_name}
                                            {if '' != $employee.plan}
                                                [{$employee.plan}]
                                            {/if}
                                        </a>
										{if false != $is_rule}
											{if null == $employee.end}
												{if isset($department.virtual)}
													<a href="/admin/employee/employee/adddepartment/{$employee.user_id}" title="Перести в подразделение" class="employee-add-department icon-add"></a>
												{else}
													<a href="/admin/employee/employee/changedepartment/{$employee.user_id}?department_id={$employee.department_id}" title="Перенести из подразделения" class="move-employee icon-move-employee "></a>
												{/if}
											{/if}
										{/if}
                                    </td>
                                    {assign var=key_sheet value=$employee.key}
									{if false != $is_rule}
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
												<td class="col-{$col} col-calculate hidden {$tdclass}">
													-
												</td>
												<td class="col-{$col} {$tdclass}" >
													{if 0 < $sheet.plan_amount}
														{assign var=_percent value=((($sheet.fact_amount/$sheet.plan_amount) - 1)*100)}
														{if 0 < $_percent}
															<span class="icon-up">+{$_percent|round}%</span>
														{else}
															<span class="icon-down">{$_percent|round}%</span>
														{/if}	
													{/if}
												</td>
												<td class="col-{$col} solid {$tdclass}" >
													{if 0 < $sheet.fact_amount}
														<span class="span">
															{$sheet.fact_amount|price_format:0}
														</span>
													{else}
														-
													{/if}
												</td>
											{else}
												<td class="col-{$col} {$tdclass}">-</td>
												<td class="col-{$col} col-calculate hidden {$tdclass}">-</td>
												<td class="col-{$col} {$tdclass}">-</td>
												<td class="col-{$col} solid {$tdclass}">-</td>
											{/if}
										{/foreach}
									{/if}
                                    <td class="col-first {if false != $is_rule}edit_cell{/if} currmonth">
                                        {if null == $employee.end}
                                            {if false != $is_rule}
                                                {if 1 == $employee.is_plan_based}
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
                                            {else}
                                                {if 1 == $employee.is_plan_based}
                                                    {assign var=edit_cell value=$form->getData("user")}
                                                    {if isset($edit_cell.$key_user)}
                                                        <span class="value">{$edit_cell.$key_user}</span>
                                                    {/if}
                                                {/if}
                                            {/if}
                                        {/if}
                                    </td>
                                    <td class="col-{$col} col-calculate hidden">
                                        -
                                    </td>
                                    <td class="col-first currmonth">
                                        {if 1 == $employee.is_plan_based}
                                            {if isset($f_users.$key_user)}
                                                {if isset($facts.$key_user)}
                                                    {if 0 < $f_users.$key_user}
                                                        {assign var=_percent value=((($facts.$key_user/($f_users.$key_user*$c_percent*100)) - 1)*100)}
                                                    {else}
                                                        {assign var=_percent value=100}
                                                    {/if}
                                                    {if 0 < $_percent}
                                                        <span class="icon-up">{$_percent|round}%</span>
                                                    {else}
                                                        <span class="icon-down">{$_percent|round}%</span>
                                                    {/if}
                                                {/if}
                                            {/if}
                                        {/if}
                                    </td>
                                    <td class="col-first currmonth">
                                        {if isset($facts.$key_user)}
                                            <span class="span">{$facts.$key_user|price_format:0}</span>
                                        {/if}
                                    </td>
                                    <td></td>
                                </tr>
                            {/if}
                        {/foreach}
                    {/if}
                </tbody>
            {/foreach}
        </table>
    </div>
</div>
{$form->hidden('month')}
{$form->hidden('year')}
{$form->hidden('start')}
{$form->hidden('end')}
{$form->security()}
{$form->submit('Сохранить', ['class' => 'btn'])}
{$form->close()}
<script src="/js/admin/plansheet.js?v={$smarty.now}"></script>

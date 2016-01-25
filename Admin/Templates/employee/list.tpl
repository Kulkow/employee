{include file='menu.tpl'}
<div class="page_module salary">
    <h1>Сотрудники</h1>
    <div id="salary_block">
			<div class="hidenlevels">
				<span class="hidelevel active" data-level="1" title="Показать всех сотрудников 1 уровня">1</span>
				<span class="hidelevel" data-level="2" title="Показать всех сотрудников 2 уровня">2</span>
				<span class="hidelevel" data-level="3" title="Показать всех сотрудников">3</span>
			</div>
            <table style="width:100%;" class="table">
                <thead>
				<tr>
                    <th width="350px" class="tdname">Подразделение</th>
					<th></th>
                </tr>
				</thead>
				{assign var=department_number value=0}
				
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
						<tbody>
							<tr class="dname it-department level{$department.level} {$cpath}" data-class="expanded" data-id="{$department.id}">
								<td class="firsttd dname expanded2 active2" data-department="{$department.id}">
									<a href="/admin/employee/department/{$department.id}" class="name icon-expand" title="Редактировать Подразделение">
										{if null != $department.number}<b>{$department.number}.</b>{/if} {$department.name}
									</a>
								</td>
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
								<tr data-user="{$user.id}" data-number="{$user.number}"
									class="it-employee hidden {if null != $user.fire_date}fire{/if} rang{$user.status} {if isset($salary.balance)}{if 0 == $salary.balance}salaryout{/if}{/if}">
									<td class="tdname">
                                        {if 1 == $user.number}
                                            {if 1 == $user.status}
                                                <span class="rang rang1"></span>
                                            {else}
                                                <span class="rang rang_1"></span>
                                            {/if}
                                        {/if}
                                        {if 2 == $user.number}
                                            {if 2 == $user.status}
                                                <span class="rang rang2"></span>
                                            {else}
                                                <span class="rang rang_2"></span>
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
                                        <span class="icon-helps">
											{if '' != $user.number}<b>{if null != $department_number}{$department_number}.{/if}{$user.number}.</b>{/if}
											{$user.family} {$user.sname}
										</span>
									</td>
									<td class="actions percent_doc_{$user.doc_status}">
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
											<a href="/admin/employee/employee/editor2/{$user.id}" class="employee-edit icon-edit" title="Редактировать"></a>
										{/if}
									</td>
								</tr>
							{/foreach}
					</tbody>
				{/foreach}
            </table>
    </div>
</div>
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
{include file='menu.tpl'}
<div class="page_module employee">
    <h1>Управление сотрудниками</h1>
    <div class="module-notice">
        Перемещение сотрудников между подразделениями, информация о сотрудниках 
    </div>
    <div id="manager_block">
        <div class="block_l">
            <h3>Сотрудники</h3>
            <div class="employees" id="employeeTable">
                <div class="search" block>
                    <input class="search" />
                </div>
                <table style="width:100%;" class="table">
                    <tr>
                        <th class="sort" data-sort="sort-deparment" width="200px">Подразделение</th>
						<th class="sort" data-sort="sort-name">ФИО</th>
                        <th></th>
                    </tr>
                    <tbody class="list">
                    {foreach from=$employees item=employee}
                            <tr class="it-employee department{$employee.department}">
                                <td class="td-deparment level{$employee.department_level}">
                                    {if null !== $employee.department_id}
                                       <span class="sort-deparment hidden">{$employee.department_lkey}</span>
									   <a href="/admin/employee/department/{$employee.department_id}" title="Редактировать Подразделение">{$employee.department}</a>
                                    {/if}
                                </td>
								<td class="td-name">
                                    <a href="/admin/employee/employee/quick/{$employee.id}" class="sort-name quick-view" title="Быстрый Просмотр">{$employee.name}</a>
                                </td>    
                                <td class="actions">
									<a href="/admin/employee/salary/user/{$employee.id}" class="icon-settings" title="Зарплата">ЗП</a>
                                    <a href="/admin/employee/esalary/edit/{$employee.id}/" class="icon-s" title="Редактирование ставки">P</a>
									<a href="/admin/employee/employee/edit/{$employee.id}" class="icon-edit" title="Редактирование данных"></a>
                                    {if $employee.status}
                                        <a href="/admin/employee/employee/fire/{$employee.id}" class="icon-delete" title="Уволить"></a>
                                    {/if}
                                    <span class="icon-check mov" data-user="{$employee.id}" title="Выбрать для перемещения"></span>
                                </td>
                            </tr>
                    {/foreach}
                    </tbody>
                </table>
            </div>
        </div>
        <div class="block_move icon-play">
            {$move->open('/admin/employee/employee/move/')}
            {$move->input('hidden','users')}
            {$move->input('hidden','department')}
            {$move->security()}
            {$move->close()}
        </div>
        <div class="block_r">
            <div class="sticky">
                <h3>Подразделения</h3>
                <div class="departments">
                    <p class="selected" data-department=""></p>
                    <p class="not">Отметить для того, чтобы перевести сотрудника в раздел</p>
                    <ul>
                    {foreach from=$departments item=department}
                        <li class="it-department level{$department.level}">
                            {if $department.level !== '0'}
                                <a href="/admin/employee/department/{$department.id}" data-id="{$department.id}" class="s icon-check" title="Выбрать">{$department.name}</a>
                            {else}
                                {$department.name}
                            {/if}
                        </li>
                    {/foreach}
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
{literal}
<script src="/js/list/list.min.js"></script>
<script>
    $(function(){
        var employeeTable = new List('employeeTable', {
            valueNames: ['sort-name', 'sort-deparment']
        });
    $('.it-employee .mov').click(function(){
        var _class_ok = 'icon-ok', _class_n = 'icon-check';
        var li = $(this);
        if(li.hasClass(_class_ok)){
            li.removeClass(_class_ok).addClass(_class_n);
        }else{
            li.removeClass(_class_n).addClass(_class_ok);
        }
        return !1;
    })
    $('.it-employee .quick-view').click(function(){
		var a = $(this), id = a.data('id'), _url = a.attr('href');
		a.ajaxpopup({
			form: {
				submit: function(response) {
					//console.log('Submit');
					//location.reload();
				}
			},
			title: 'Информация по сотруднику '+ a.text(),
			url: _url,
			dialog: {
				minWidth: 700,
				width: 700
			},
		});
		return !1;
	})
	$('.it-employee .icon-edit').click(function(){
		var a = $(this), id = a.data('id'), _url = a.attr('href');
		a.ajaxpopup({
			form: {
				submit: function(response) {
					//location.reload();
				}
			},
			title: 'Редактирование '+ a.text(),
			url: _url,
			dialog: {
				minWidth: 700,
				width: 700
			},
		});
		return !1;
	})
	$('.it-employee .icon-s').click(function(){
		var a = $(this), id = a.data('id'), _url = a.attr('href');
		a.ajaxpopup({
			form: {
				submit: function(response) {
					//location.reload();
				}
			},
			title: 'Редактирование ставки',
			url: _url,
			dialog: {
				minWidth: 700,
				width: 700
			},
		});
		return !1;
	})
    $('.it-department .s').click(function(){
        var _class_ok = 'icon-ok', _class_n = 'icon-check';
        var a = $(this), _as = $('.it-department .s'), departments = $('.departments'), _s = $('.selected',departments), _not = $('.not',departments);
        if(a.hasClass(_class_ok)){
            _as.removeClass(_class_ok).addClass(_class_n);
            a.removeClass(_class_ok).addClass(_class_n);
            _s.data('department',0).text('').data('department',0);
            _not.removeClass('hidden');
        }else{
            _as.removeClass(_class_ok).addClass(_class_n);
            var _t = 'Переместить в раздел ' + a.text();
            a.removeClass(_class_n).addClass(_class_ok);
            _not.addClass('hidden');
            _s.data('department',a.data('id')).attr('data-department',a.data('id')).text(_t);
        }
        return !1;
    })
    $('.block_move').click(function(){
        var users = [];
        $('.it-employee .icon-ok.mov').each(function(i, _check){
            var _id = $(_check).data('user');
            users.push(_id);
        })
        var _form = $('#move');
        $('#move-users').val(users.join(','));
        var department = $('.departments .selected').data('department');
        $('#move-department').val(department);
        var data = _form.serialize();
        $.ajax({
            type : 'POST',
            dataType : 'json',
            data : data,
            url: _form.attr('action'),
            success: function(){
              window.location.reload();
            }
        });
        return !1;
    })
})
</script>
{/literal}
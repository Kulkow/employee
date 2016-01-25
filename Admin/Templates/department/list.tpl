{include file='menu.tpl'}
<h1>Подразделения</h1>
<div id="departments">
    <table class="table" style="width:50%">
        <thead>
            <tr>
                <th>Наименование</th>
                <th>Руководитель</th>
                <th style="width:90px">Операции</th>
            </tr>
        </thead>
        <tbody>
            {foreach from=$departments item=department}
                <tr class="level{$department.level}">
                    <td class="dname">
                        {if $department.level !== '0'}
                        <a href="/admin/employee/department/{$department.id}" class="icon-settings" title="Просмотр">
                           <b>{$department.number}</b> {$department.name}
                        </a>
                        {else}
                            {$department.name}
                        {/if}
                    </td>
                    <td>
                        {$department.chief}
                    </td>
                    <td class="c-align">
                        {if $department.level !== '0'}
                            <a href="/admin/employee/department/add/{$department.id}" class="icon-add" title="Добавить"></a>
                            <a href="/admin/employee/department/edit/{$department.id}" class="icon-edit" title="Редактировать"></a>
                            <a href="/admin/employee/department/up/{$department.id}" class="icon-up" title="Поднять"></a>
                            <a href="/admin/employee/department/down/{$department.id}" class="icon-down" title="Опустить"></a>
                            <a href="/admin/employee/department/remove/{$department.id}" class="icon-delete" title="Удалить"></a>
                        {else}
                            <a href="/admin/employee/department/add/{$department.id}" class="icon-add" title="Добавить"></a>
                        {/if}
                    </td>
                </tr>
            {/foreach}
        </tbody>
    </table>
    <a href="/admin/employee/department/add" class="icon-add btn">Добавить подразделение</a>
</div>
<script>
$(function() {
    var departments = $('#departments');
    departments.ajaxpopup2({
        trigger: '.icon-add',
        submit: function() {
            window.location.reload();
        }
    });
    departments.ajaxpopup2({
        trigger: '.icon-edit',
        submit: function() {
            window.location.reload();
        }
    });
    departments.on('click', '.icon-delete', function(event) {
        if (confirm('Подтвердите удаление'))
            sp.post(event.target.href).done(function() {
                $(event.target).closest('tr').remove();
            });
        event.preventDefault();
    });
    departments.on('click', '.icon-up', function(event) {
            sp.post(event.target.href).done(function() {
                window.location.reload();
            });
        event.preventDefault();
    });
    departments.on('click', '.icon-down', function(event) {
            sp.post(event.target.href).done(function() {
                window.location.reload();
            });
        event.preventDefault();
    });
});
</script>
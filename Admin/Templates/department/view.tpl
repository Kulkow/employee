<h1>Подразделения / {$department.name}</h1>
<div id="department">
    <div style="margin:0 0 20px">
        <a href="/admin/employee/department/list" class="icon-arrow-left btn">К списку подразделений</a>
        <a href="/admin/employee/department/edit/{$department.id}" class="icon-edit btn">Редактировать</a>
    </div>
    <div class="employee wrapper">
        <h4>Руководитель:</h4>
        {if $department.chief}
            <div class="employee">
                {$department.chief}
                <a href="/admin/employee/department/removeChief/{$department.id}" class="icon-delete" title="Удалить"></a>
            </div>
        {/if}
    </div>
    <div class="wrapper">
        <h4>Сотрудники:</h4>
        {foreach from=$employees item=employee}
            <div class="employee">
                {$employee.name} с {$employee.start}
                <a href="/admin/employee/employee/remove/{$employee.id}" class="icon-delete" title="Удалить"></a>
            </div>
        {/foreach}
    </div>
    <div class="wrapper">
        {$app->forward('/admin/employee/employee/add', ['departmentId' => $department.id])}
    </div>
    <div class="wrapper">
        <h4>Показатели:</h4>
        {foreach from=$plans item=plan}
            <div class="employee">
                {$plan.name}
                <a href="/admin/employee/department/removeplan/{$plan.id}" class="icon-delete" title="Удалить"></a>
            </div>
        {/foreach}
    </div>
    <div class="wrapper">
        {$app->forward('/admin/employee/department/addplan', ['departmentId' => $department.id])}
    </div>
</div>
<script>
$(function() {
    var department = $('#department');
    department.ajaxpopup2({
        trigger: '.icon-edit',
        submit: function() {
            window.location.reload();
        }
    });
    department.on('click', '.icon-delete', function(event) {
        if (confirm('Подтвердите удаление'))
            sp.post(event.target.href).done(function() {
                $(event.target).closest('.employee').remove();
            });
        event.preventDefault();
    });
});
</script>
<style>
#department .wrapper {
    border-bottom: 1px dotted #ccc;
    margin: 0 0 10px;
    overflow: hidden;
    padding-bottom: 10px;
    padding-left: 120px;
}
#department .wrapper H4 {
    float: left;
    margin-left: -120px;
    width: 120px;
}
#department .warehouses .manager {
    margin: 0 0 5px;
}
#department .warehouses B {
    float: left;
    width: 150px;
}
#department .warehouses LABEL {
    width: 60px;
}
</style>

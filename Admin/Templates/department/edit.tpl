{if $form->getData('id')}
    {assign var=title value='Редактирование подразделения'}
{else}
    {assign var=title value='Новое подразделение'}
{/if} 
<div class="employee-form">
    {$form->open(null, 'post', ['title' => $title])}
    <div class="row">
        {$form->label('name', 'Наименование')}
        {$form->text('name', ['class' => 'txt', 'style' => "width:150px"])}
    </div>
    {if null}
    <div class="row">
        {$form->label('chief', 'Руководитель')}
        {$form->text('chief', ['class' => 'txt'])}
    </div>
    {/if}
    <div class="row">
        {$form->label('number', 'Номер')}
        {$form->text('number', ['class' => 'txt', 'style' => "width:50px"])}
    </div>
    <div class="row">
        {$form->label('datesalary', 'День выдачи зарплаты')}
        {$form->text('datesalary', ['class' => 'txt', 'style' => "width:50px"])}
    </div>
    <div class="row">
        {$form->hidden('id')}
        {$form->hidden('chief_id')}
        {$form->hidden('pid')}
        {$form->security()}
        {$form->submit('Сохранить', ['class' => 'btn'])}
    </div>
    {$form->close()}
</div>
<script>
(function() {
    var cache = {};
    /*$('#DepartmentEdit-chief').autocomplete({
        minLength: 2,
        select: function(event, ui) {
            $('#DepartmentEdit-chief_id').val(ui.item.id);
        },
        source: function(request, response) {
            var term = $.ui.autocomplete.escapeRegex(request.term);
            if (term in cache) {
                response(cache[term]);
                return false;
            }
            sp.post('/admin/pm/employee/search/', {
                q: request.term
            }).done(function(data) {
                var regexp = new RegExp('(' + term + ')', 'ig');
                cache[term] = $.map(data.employee, function(item) {
                    item.value = item.name;
                    item.label = item.name.replace(regexp, '<b>$1</b>');
                    return item;
                });
                response(cache[term]);
            });
        },
        change: function(event, ui) {
            if (null === ui.item)
                $('#DepartmentEdit-chief_id').val('');
        }
    }).data('ui-autocomplete')._renderItem = function(ul, item) {
        return $('<li>').append('<a>' + item.label + '</a>').appendTo(ul);
    };*/
})();
</script>
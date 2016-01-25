{$form->open('/admin/employee/employee/add')}
<div class="row inline">
    {$form->label('name', 'ФИО')}
    {$form->text('name', ['class' => 'txt', 'style' => 'width:200px'])}
</div>
<div class="row inline">
    {$form->label('start', 'начало работы')}
    {$form->text('start', ['class' => 'txt datepicker', 'style' => 'width:200px'])}
</div>
<div>
    {$form->hidden('department_id')}
    {$form->hidden('user_id')}
    {$form->security()}
</div>
<div>
    {$form->submit('Добавить сотрудника', ['class' => 'btn'])}
</div>
{$form->close()}
<script>
$(function() {
    $('.datepicker').datepicker();
    $('#EmployeeAdd').form({
        submit: function(response) {
            if (!response.errors)
                window.location.reload();
        }
    });
    var cache = {};
    $('#EmployeeAdd-name').autocomplete({
        minLength: 2,
        select: function(event, ui) {
            $('#EmployeeAdd-user_id').val(ui.item.id);
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
        }
    }).data('ui-autocomplete')._renderItem = function(ul, item) {
        return $('<li>').append('<a>' + item.label + '</a>').appendTo(ul);
    };
});
</script>
<h2>Зарегистрировать</h2>
{$form->open('/admin/employee/recruit', 'post', ['autocomplete' => 'off'])}
    <div class="row">
        {$form->label('q', 'Поиск по email или телефону')}
        {$form->text('q', ['class' => 'txt'])}
    </div>
    <div class="row">
        <span>или Зарегистрировать</span>
    </div>
    <div class="row">
        {$form->label('name', 'ФИО (Петров Иван Константинович)')}
        {$form->text('name', ['class' => 'txt'])}
    </div>
    <div class="row">
        {$form->label('email', 'E-mail')}
        {$form->text('email', ['class' => 'txt'])}
    </div>
    <div class="row">
        {$form->label('phone', 'или Телефон (89271234567)')}
        {$form->text('phone', ['class' => 'txt', 'autocomplete' => 'off'])}
    </div>
    <div class="row">
        {$form->label('password', 'Пароль')}
        {$form->password('password', ['class' => 'txt', 'autocomplete' => 'off'])}
    </div>
    <div>
        {$form->security()}
        {$form->hidden('user_id')}
        {$form->submit('Зарегистрировать', ['class' => 'btn'])}
    </div>
{$form->close()}
{literal}
<script>
$(function() {
    $('#Account').form({
        submit: function(response) {
            if (!response.errors){
                
            }
        }
    });
    var cache = {};
    $('#Account-q').autocomplete({
        minLength: 2,
        select: function(event, ui) {
            $('#Account-user_id').val(ui.item.id);
        },
        change: function( event, ui ) {
            if(! ui.item){
                console.log('null');
                $('#Account-user_id').val('');    
            }       
        },
        source: function(request, response) {
            var term = $.ui.autocomplete.escapeRegex(request.term);
            if (term in cache) {
                response(cache[term]);
                return false;
            }
            sp.post('/admin/employee/recruit/search/', {
                q: request.term
            }).done(function(data) {
                if(data.user){
                    var regexp = new RegExp('(' + term + ')', 'ig');
                    cache[term] = $.map(data.user, function(item) {
                        item.value = item.name;
                        var _name = item.name + ' ('+item.phone+')';
                        item.label = _name.replace(regexp, '<b>$1</b>');
                        return item;
                    });
                    response(cache[term]);
                }
            });
        }
    }).data('ui-autocomplete')._renderItem = function(ul, item) {
        return $('<li>').append('<a>' + item.label + '</a>').appendTo(ul);
    };
});
</script>
{/literal}
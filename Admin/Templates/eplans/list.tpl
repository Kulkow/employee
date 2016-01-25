{include file='menu.tpl'}
<h1>Плановые показатели пользователей</h1>
<div style="width:90%;">
    <table class="table" id="eplans">
        <tr>
            <th>ФИО</th>
            <th>Плановые показатели</th>
        </tr>
        {foreach from=$users item=user}
        <tr class="it-user">
			<td class="td_name">
                <div class="row">
                    <div class="name">{$user.name}</div>
                    <a class="icon-add" href="/admin/employee/eplan/user/{$user.id}" title="Добавить показатель пользователю"></a>
                </div>
            </td>
            <td class="td plans">
                <div class="errors error hidden"></div>
                <div class="row">
                    {$form->open("/admin/employee/eplan/user/`$user.id`")}
                    {foreach from=$user.plans item=plan}
                        {if $plan.is_plan_based == 1}
                            {assign var=value value=$plan.value}
                        {else}
                            {assign var=value value=$plan.value|price_format}
                        {/if}
                        <div class="cell">
                            {$form->text("value[`$plan.id`]",['class' => 'txt','value' => $value])}
                            {$form->select("plan_id[`$plan.id`]",$form->htmlOptions($plans), ['class' => 'select','value' => $plan.plan_id])}
                            <a class="icon-delete" href="/admin/employee/eplan/remove/{$plan.id}/" title="Удалить показатель пользователя"></a>
                            {$form->hidden("is_plan_based[`$plan.id`]",['value' => $plan.is_plan_based])}
                        </div>
                    {/foreach}
                    <div class="cell last">
                        <div class="row" style="height: 40px;">
                            {$form->submit('Сохранить', ['class' => 'btn'])}
                            <a class="icon-add" href="/admin/employee/eplan/user/{$user.id}" title="Добавить показатель пользователю"></a>
                        </div>
                    </div>
                    {$form->hidden('user_id',['value' => $user.id])}
                    {$form->security()}
                    {$form->close()}
                </div>
            </td>
        </tr>
        {/foreach}
    </table>
</div>
{literal}
<style>
    #eplans{}
    #eplans .td{}
    #eplans .td .errors{color:red;}
    #eplans .td .row{position: relative; overflow:hidden;}
    #eplans .td .row .cell{position: relative; float:left; width:200px; padding: 0 20px 7px 0;}
    #eplans .td .row .cell .icon-delete{position: absolute; right: 0; top:50%; margin: -7px 0 0 0; width:15px; height: 15px; display: block;}
    #eplans .td .row .cell .txt{position: relative; display: block; width:200px; margin: 0 0 7px 0;}
    #eplans .td .row .cell .select{position: relative; display: block; width:200px;}
    #eplans .td .row .cell.last{width: 100px;}
    #eplans  TD .row{position: relative; padding: 0 20px 0 0; min-height: 20px;}
    #eplans  TD .row .icon-add{position: absolute; right: 0; top:50%; margin: 0px 0 0 0; width:15px; height: 15px; display: block;}
    #eplans  TD .row .icon-save{position: absolute; right: 0; top:0; margin: 0px 0 0 0; width:15px; height: 15px; display: block;}
</style>
<script>
    var Eplans = {}
    function isArray(myArray) {
        return myArray.constructor.toString().indexOf("Array") > -1;
    }
    function _ResponsForm(response,form) {
        var td = form.closest('td'), _error_block = $('.errors',td);
        if (!response.errors){
            _error_block.addClass('hidden').text('');
            /*window.location.reload();*/
        }else{
            var summary = '';
            for(var field in response.errors){
                var _errors = response.errors[field];
                if (isArray(_errors)) {
                    for(var index in _errors){
                        var _error = _errors[index];
                        summary = summary + _error;
                    }
                }
                else{
                    summary = summary + _errors;
                }
            }
            _error_block.removeClass('hidden').text(summary);
        }
    }
    $(function(){
        var user = $('.it-user');
        
        $('TD .icon-add').click(function(){
            var a = $(this), href = a.attr('href'), _tr = a.closest('.it-user'), _form = $('form', _tr);
            $.get(href,function(response){
                if (response.html) {
                    var _html = response.html;
                    var f = $(_html).find('form');
                    if (f.length == 0) {
                        f = $(_html).filter('form');
                    }
                    $('.cell.last', _tr).before(f.html());
                }
            })
            return !1;
        })
        
        $('TD').on('click', '.icon-delete', function(){
            var a = $(this), href = a.attr('href'), cell = a.closest('.cell'), _tr = a.closest('.it-user'), _form = $('form', _tr);
            if (a.data('new') == '1') {
                cell.remove();
            }else{
                $.get(href,function(response){
                    if (! response.errors) {
                        cell.remove();
                    }else{
                        alert(response.errors);
                    }
                })
            }
            return !1;
        })
        
        $('.it-user form').form({
            submit: function(response) {
                _ResponsForm(response, this);
                /*var td = this.closest('td'), _error_block = $('.errors',td);
                if (!response.errors){
                    _error_block.addClass('hidden').text('');
                    window.location.reload();
                }else{
                    var summary = '';
                    for(var field in response.errors){
                        var _errors = response.errors[field];
                        if (isArray(_errors)) {
                            for(var index in _errors){
                                var _error = _errors[index];
                                summary = summary + _error;
                            }
                        }
                        else{
                            summary = summary + _errors;
                        }
                    }
                    _error_block.removeClass('hidden').text(summary);
                }*/
            }
        });
    })
</script>
{/literal}
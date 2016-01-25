{include file='menu.tpl'}
<div class="filter" style="overflow:hidden; margin: 0 0 10px">
    {$filter->open()}
    <div class="row inline">
        {$filter->label('start', 'С')}
        {$filter->text('start', ['class' => 'txt datepicker'])}
        {$filter->label('end', 'По')}
        {$filter->text('end', ['class' => 'txt datepicker'])}
        {$filter->submit('Фильтр', ['class' => 'btn'])}
        {$filter->hidden('owner_id')}
        {$filter->security()}
    </div>
    {$filter->close()}
</div>

<div class="list" id="plans" style="width:800px;">
    <ul id="nav">
        <li>
            <a href="/admin/employee/planorg/add/" title="Добавить " class="btn icon-add">Добавить</a>
        </li>
    </ul>
    <table class="table">
        <tr>
            <th width="100px">дата</th>
			<th>Баланс</th>
            <th width="50px"></th>
        </tr>
        {foreach from=$plans item=plan}
        <tr class="it-plan">
            <td>
				{$plan.date|date_format:"d.m.Y"}
			</td>
            <td>
                {$plan.profit|price_format}
			</td>
            <td class="actions">
                <a href="/admin/employee/planorg/edit/{$plan.id}" class="icon-edit" title="Изменить"></a>
                <a href="/admin/employee/planorg/remove/{$plan.id}" class="icon-delete" title="Удалить"></a>
            </td>
        </tr>
        {/foreach}
    </table>
</div>
{literal}
<script>
    $(function(){
        $('.datepicker').datepicker();
        var plans = $('#plans');
        plans.on('click','.icon-add', function(){
            var a = $(this), _url = a.attr('href');
            a.ajaxpopup({
                form: {
                    submit: function(response) {
                        if (!response.errors){
                            location.reload();
                        }else{
                            alert(response.errors);
                        }
                    }
                },
                title: 'Добавить',
                url: _url,
                dialog: {
                    minWidth: 350,
                    width: 350
                },
            });
            return !1;
        })
        plans.on('click','.icon-edit', function(){
            var a = $(this), _url = a.attr('href');
            a.ajaxpopup({
                form: {
                    submit: function(response) {
                        if (!response.errors){
                            location.reload();
                        }else{
                            alert(response.errors);
                        }
                    }
                },
                title: 'Изменить',
                url: _url,
                dialog: {
                    minWidth: 350,
                    width: 350
                },
            });
            return !1;
        })
        $('.it-plan').on('click','.icon-delete', function(){
            var tr = $(this).closest('tr');
            $.get(this.href, function(data){
                tr.remove();
            })
            return !1;
        })
    })
</script>
{/literal}
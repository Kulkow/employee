<h2>Пересечение ставок</h2>
<table class="table intersect" style="width:600px;">
    <tr>
        <th>Сотрудник</th>
        <th>Ставка</th>
        <th>Начало</th>
        <th>Окончание</th>
        <th></th>
    </tr>
{foreach from=$intersects item=row}
    <tbody>
        <tr class="it-esalary">
            <td>{$row.name}</td>
            <td>{$row.base|price_format}</td>
            <td>{$row.start|date_format:"d.m.Y"}</td>
            <td>
                {if null != $row.end}
                    {$row.end|date_format:"d.m.Y"}
                {/if}
            </td>
            <td>
                <a href="/admin/employee/esalary/clear/{$row.id}" class="icon-delete" title="Удалить"></a>
            </td>
        </tr>
        {foreach from=$row.intersect item=intersect}
            <tr class="it-esalary">
                <td style="text-align: right;">пересекается с</td>
                <td>{$intersect.row.base|price_format}</td>
                <td>{$intersect.row.start|date_format:"d.m.Y"}</td>
                <td>
                    {if null != $intersect.row.end}
                        {$intersect.row.end|date_format:"d.m.Y"}
                    {/if}
                </td>
                <td>
                    <a href="/admin/employee/esalary/clear/{$intersect.row.id}" class="icon-delete" title="Удалить"></a>
                </td>
            </tr>
        {/foreach}
    </tbody>
{/foreach}
</table>
{literal}
    <script>
        $(function(){
            $('.it-esalary').on('click', '.icon-delete', function(){
                var _a = $(this), href = this.href, tr = _a.closest('tr');
                if(confirm('Удалить ставку?')){
                    sp.get(href, function(response){
                        tr.remove();
                    })
                }
                return !1;
            })
        })
    </script>
{/literal}
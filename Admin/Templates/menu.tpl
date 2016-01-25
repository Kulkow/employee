{literal}
<link rel="stylesheet" href="/css/admin/employee.css" media="all" />
{/literal}
{if !isset($menu)}
    {assign var=menu value='salary'}
{/if}
{if !isset($is_rule)}
    {assign var=is_rule value=false}
{/if}
{if !isset($is_chief)}
    {assign var=is_chief value=false}
{/if}
{if !isset($is_bookkeper)}
    {assign var=is_bookkeper value=false}
{/if}
<div class="nav menu">
{assign var=url_query value=null}
{if isset($cmonth)}
    {assign var=url_query value="month=`$cmonth`"}
{/if}
{if isset($cyear)}
    {if null == url_query}
        {assign var=url_query value="month=`$cmonth`"}
    {else}
        {assign var=url_query value="`$url_query`&amp;year=`$cyear`"}
    {/if}
{/if}
    <ul>
        <li class="it {if ($menu == 'cabinet')}active{/if}"><a href="/admin/employee/salary/cabinet/">Мой кабинет</a></li>
        {if isset($is_rule)}
            {if false != $is_rule}
                <li class="it {if ($menu == 'salary')}active{/if}"><a href="/admin/employee/salary/{if null != $url_query}?{$url_query}{/if}">Зарплаты</a></li>
            {else}
                {if false != $is_chief or false != $is_bookkeper}
                    <li class="it {if ($menu == 'salary')}active{/if}"><a href="/admin/employee/salary/{if null != $url_query}?{$url_query}{/if}">Сотрудники</a></li>
                {/if}
            {/if}
        {else}
            <li class="it {if ($menu == 'salary')}active{/if}"><a href="/admin/employee/salary/{if null != $url_query}?{$url_query}{/if}">Зарплаты</a></li>
        {/if}
        {if isset($is_rule)}
            {if false != $is_rule or false != $is_chief}
                <li class="it {if ($menu == 'plansheet')}active{/if}"><a href="/admin/employee/plansheet/{if null != $url_query}?{$url_query}{/if}">Плановые показатели</a></li>
            {/if}
        {else}
            <li class="it {if ($menu == 'plansheet')}active{/if}"><a href="/admin/employee/plansheet/{if null != $url_query}?{$url_query}{/if}">Плановые показатели</a></li>
        {/if}
        {*
        <li class="it {if $menu == 'employee'}active{/if}"><a href="/admin/employee/employee/manager/">Управление сотрудниками</a></li>
        <li class="it {if $menu == 'department'}active{/if}"><a href="/admin/employee/department/list/">Подразделения</a></li>
        <li class="it {if $menu == 'plan'}active{/if}"><a href="/admin/employee/plan/">показатели</a></li>
        *}
    </ul>
</div>
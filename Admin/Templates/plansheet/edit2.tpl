{include file='menu.tpl'}
{include file='plansheet/filter.tpl'}
{$form->open()}
<div class="department" style="width:700px;">
    {$form->errorSummary('list')}
    
    <table class="table">
        <tr>
            <td width="100px">
            </td>
            <td width="115px">
                <p>Месяц:<b>{$form->getData('mount')}</b></p>
                <p>Год:<b>{$form->getData('year')}</b></p>
            </td>
            {foreach from=$info_col item=col}
                <td width="50px">{$col|date_format}</td>
            {/foreach}
        </tr>
    {foreach from=$departments item=department}
        <tr class="level{$department.level}">
            <td width="100px" class="d">
                <b>{$department.number}</b> {$department.name}
            </td>
            <td colspan="{$info_col|@count + 1}">
                <table class="table">
                    {foreach from=$department.plans item=dplan}
                        <tr>
                            <td width="100px">{$dplan.name}</td>
                            <td class="common" width="50px">
                                {if $dplan.is_common == 1}
                                    {$form->text("common[`$dplan.plan_id`]", ["class" => "txt"])}
                                {/if}
                            </td>
                            {foreach from=$info_col item=col}
                                {if isset($info.$col)}
                                    {assign var=inform value=$info.$col}
                                    {assign var=k value="common_`$dplan.plan_id`"}
                                    <td width="50px">
                                        {if isset($inform.$k)}
                                            {$inform.$k.plan_amount|price_format}
                                        {/if}
                                    </td>
                                {else}
                                    <td width="50px"></td>
                                {/if}
                            {/foreach}
                        </tr>
                        {foreach from=$dplan.employees item=employee}
                            <tr>
                                <td width="100px">{$form->label("user-`$employee.user_id`_`$employee.plan_id`",$employee.u_name)}</td>
                                <td width="50px">{$form->text("user[`$employee.user_id`_`$employee.plan_id`]", ["class" => "txt"])}</td>
                                {foreach from=$info_col item=col}
                                    {if isset($info.$col)}
                                        {assign var=inform value=$info.$col}
                                        {assign var=k value="user_`$employee.user_id`_`$employee.plan_id`"}
                                        <td width="50px">
                                            {if isset($inform.$k)}
                                                {$inform.$k.plan_amount|price_format}
                                            {else}
                                                {assign var=plan_id_old value="plan_`$employee.plan_id`"}
                                                {if (isset($inform.$plan_id_old))}
                                                    {foreach from=$inform.$plan_id_old item=s}
                                                        {$s.plan_amount|price_format},
                                                    {/foreach}
                                                {/if}
                                            {/if}
                                        </td>
                                    {else}
                                        <td width="50px"></td>
                                    {/if}
                                {/foreach}
                            </tr>
                        {/foreach}            
                    {/foreach}
                </table>
            </td>
        </tr>
    {/foreach}
    </table>
</div>
{$form->hidden('mount')}
{$form->hidden('year')}
{$form->hidden('start')}
{$form->hidden('end')}
{$form->security()}
{$form->submit('Сохранить', ['class' => 'btn'])}
{$form->close()}
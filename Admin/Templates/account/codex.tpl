{$form->open("/admin/employee/codex/`$user_id`")}
<div class="row">
    {$form->label('role_id', 'Роль')}
    {$form->select('role_id', $form->htmlOptions($roles), ['class' => 'it-select'])}
</div>
<h5>Правила</h5>
<table class="table tablecodex">
    <tr class="thead">
        <th>Правило</th>
        <th>По группе</th>
        <th>Индивидуальное личное</th>
        <th>Итоговое</th>
    </tr>
{foreach from=$userrule item=group}
    <tbody>
        <tr class="thead" data-group="{$group.id}">
            <td colspan="4" class="expanded"><span class="icon-expand">{$group.name}</span></td>
        </tr>
    </tbody>
    <tbody class="tbody_rule_{$group.id}">
        {foreach from=$group.rules item=rule}
        <tr class="it-rule">
            <td class="td-name">{$rule.name}</td>
            <td class="td-rule-group">
                {if null == $rule.group_rule}
                    -
                {else}
                    +
                {/if}
            </td>
            <td class="td-rule-personal">
                {if null == $rule.personal_rule}
                    -
                    {assign var=check value=null}
                {else}
                    +
                    {assign var=check value=1}
                {/if}
                <input type="checkbox" class="checkbox" name="Codex[rule][{$rule.id}]" value="1" {if null == $check}{else}checked="checked"{/if} />
            </td>
            <td class="td-rule-summary">
                {if null == $rule.is_allowed}
                    -
                {else}
                    +
                {/if}
            </td>
        </tr>
        {/foreach}
    </tbody>
{/foreach}
</table>
<div>
    {$form->security()}
    {$form->submit('Сохранить')}
</div>
{$form->close()}
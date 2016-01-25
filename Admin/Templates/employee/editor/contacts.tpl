{$form->open("/admin/employee/employee/contact/`$user_id`")}
{foreach from=$contacts key=_group_key item=_group}
    <h6>{$_group.name}</h6>
    {foreach from=$_group.contacts key=field item=_contact}
        {assign var=_name value=$_contact.message}
        {assign var=value value=''}
        {if isset($econtacts.$field)}
            {assign var=value value=$econtacts.$field}
        {/if}
        {assign var=required value=1}
        {if isset($cmap.$field)}
            {if 1 == $cmap.$field}
                {assign var=required value=false}
            {/if}    
        {/if}
        <div class="row txt-row">
            {$form->label($field, $_name, $required)}
            <input type="text" class="txt" name="Contact[{$field}]" value="{$value}" />
            {if false != $is_rule}
            <span class="set-required">
                <input type="checkbox" class="s_required checkbox" title="{if null != $required}Сделать не обязательным{else}Сделать обязательным{/if}"
                       name="ContactMap[{$field}]" value="1" {if false == $required}checked="checked"{/if} />    
            </span>
            {/if}
        </div>
    {/foreach}
{/foreach}
{$form->hidden('user_id')}
{$form->security()}
{$form->close()}
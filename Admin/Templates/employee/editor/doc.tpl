{$form->open("/admin/employee/employee/doc/`$user_id`")}
{foreach from=$docs key=field item=_name}
    {assign var=check value=null}
    {if isset($doc.$field)}
        {if 1 == $doc.$field}
            {assign var=check value=1}
        {/if}    
    {/if}
    {assign var=required value=1}
    {if isset($docmap.$field)}
        {if 1 == $docmap.$field}
            {assign var=required value=false}
        {/if}    
    {/if}
    <div class="row checkboxer">
        {$form->label($field, $_name, $required)}
        <input type="checkbox" class="checkbox" name="Doc[{$field}]" value="1" {if null != $check}checked="checked"{/if} />
        {if false != $is_rule}
        <span class="set_required" style="background: green; padding: 2px;">
            <input type="checkbox" class="s_required checkbox" title="{if null != $required}Сделать не обязательным{else}Сделать обязательным{/if}" name="DocMap[{$field}]" value="1" {if false == $required}checked="checked"{/if} />    
        </span>
        {/if}
    </div>
{/foreach}
{$form->hidden('user_id')}
{$form->security()}
{$form->close()}
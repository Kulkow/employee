<tr class="it-bonus {if 2 == $bonus.is_approved} cancel hidden{/if} {if 0 > $bonus.amount}minus{else}plus{/if} {if 1 == $bonus.approved}approved{else}{/if}" id="bonus_tr_{$bonus.id}">
    <td class="tdtype">
        <div class="name edit-type">
            <p class="value">{$bonus.type}</p>
            {if false !== $is_rule}
                <div class="hidden _editor">
                    <textarea name="BonusEdit[]" style="height:30px;resize: none;" class="area txt" rows="1" data-id="{$bonus.id}">{$bonus.type}</textarea>	
                </div>
            {else}
                {if 1 == $is_main and 0 == $bonus.approved_id and 0 == $bonus.approved}
                    <div class="hidden _editor">
                        <textarea name="BonusEdit[]" style="height:30px;resize: none;" class="area txt" rows="1" data-id="{$bonus.id}">{$bonus.type}</textarea>	
                    </div>
                {/if}
            {/if}
        </div>
        <i>{$bonus.comment}</i>
        <div class="comment_user">
            <div class="value">{$bonus.comment_user}</div>
            {if 1 == $is_main}
                {if 0 == $bonus.approved_id or (2 == $bonus.request and 0 == $bonus.approved)}
                <div class="hidden edit_main_comment">
                    <div class="row BonusRequest-type_request-block">
                        <select name="BonusRequest[type_request]" class="BonusRequest-requesttype">
                            <option value="0">Выбрать причину</option>
                        {foreach from=$valieblesrequest key=_id item=vrequest}
                            <option value="{$_id}">{$vrequest}</option>
                        {/foreach}
                        </select>
                    </div>
                    <div class="row BonusRequest-employee_id-block hidden">
                        <label>Менялся с</label>
                        <input class="txt autocomplite-employee BonusRequest-employee_name" name="BonusRequest[employee_name]" value="" type="text" />
                        <input class="BonusRequest-employee_id autocomplite_id" name="BonusRequest[employee_id]" value="" type="hidden" />
                    </div>
                    <div class="row BonusRequest-working-block hidden">
                        <label>Дата отработки</label>
                        <input class="txt BonusRequest-working datepicker" name="BonusRequest[working]" value="" placeholder="" type="text" />
                    </div>
                    <div class="row BonusRequest-from-block hidden">
                        <label>c</label>
                        <input class="txt timepiker BonusRequest-from" name="BonusRequest[from]" value="" type="text" />
                    </div>
                    <div class="row BonusRequest-to-block hidden">
                        <label>По</label>
                        <input class="txt timepiker BonusRequest-to" name="BonusRequest[to]" value="" type="text" />
                    </div>
                    <div class="row edit_main_comment_area-block hidden">
                        <label>Другая причина</label>
                        <textarea data-id="{$bonus.id}" class="txt edit_main_comment_area" placeholder="Напишите комментарий и нажмите Enter" name="BonusRequest[comment_user]" rows="2">{$bonus.comment_user}</textarea>
                    </div>
                    <div class="row">
                        <button class="btn send-request" data-id="{$bonus.id}" name="send-request">Запрос</button>
                    </div>
                </div>
                {/if}
            {/if}
        </div>
        {if false !== $is_rule && (1 == $bonus.request or 3 == $bonus.request)}
            <div class="comment_cancel">
                <div class="value">{$bonus.comment_cancel}</div>
                <div class="hidden comment_cancel_block">
                    <textarea data-id="{$bonus.id}" class="txt edit_comment_cancel" placeholder="Напишите комментарий и нажмите Enter" name="BonusRequest[comment_cancel]" rows="2">{$bonus.comment_cancel}</textarea>
                </div>
            </div>
        {else}
            {if $auth_user == $bonus.manager_id && 3 == $bonus.request}
                <div class="comment_cancel">
                    <div class="value">{if '' != $bonus.comment_cancel}{$bonus.comment_cancel}{else}Отклонен{/if}</div>
                </div>
            {/if}
        {/if}
    </td>
    <td class="tdcreater">
        {if 0 < $bonus.creator_id}
        <i>{$bonus.creater_name}</i>
        {/if}
    </td>
    <td class="tddate edit-date">
        <span class="value">{$bonus.date|date_format:"%e.%m.%Y"}</span>
        {if false !== $is_rule}
            <div class="hidden _editor">
                <input class="datepicker txt" type="text" name="BonusEdit[date]" data-id="{$bonus.id}" value="{$bonus.date|date_format:"%d.%m.%Y"}" />
            </div>
        {/if}
    </td>
    <td class="tdamount edit_cell edit-amount">
        {if false !== $is_rule}
            <span class="value">{$bonus.amount|price_format}</span>
        {else}
            {if 1 == $is_main}
                <span class="value">{$bonus.amount|price_format}</span>
            {else}
                {if false != $is_chief and 1 == $bonus.skip_type}
                    <span class="value">{$bonus.amount|price_format}</span>    
                {/if}
            {/if}
        {/if}
        {if false !== $is_rule}
            <input name="Bonus[amount]" value="{$bonus.amount|price_format}" class="txt bonus hidden" data-bonus="{$bonus.id}" data-salary="{$salary_id}" />
        {else}
            {if 1 == $is_main and 0 == $bonus.approved_id and 0 == $bonus.approved}
                <input name="Bonus[amount]" value="{$bonus.amount|price_format}" class="txt bonus hidden" data-bonus="{$bonus.id}" data-salary="{$salary_id}" />
            {/if}
        {/if}
    </td>
    <td class="actions">
            {if ! isset($bonus.virtual)}
                {if 1 == $bonus.approved}
                    {if false !== $is_rule}
                        {if false}
                            <a href="/admin/employee/bonus/edit/{$bonus.id}?salary_id={$salary_id}" data-bonus="{$bonus.id}" class="icon-edit" title="Редактировать"></a>
                        {/if}
                        {if 0 == $bonus.request or 3 == $bonus.request}
                        <a href="/admin/employee/bonus/remove/{$bonus.id}?salary_id={$salary_id}" data-salary="{$salary_id}" data-bonus="{$bonus.id}"
                                    class="icon-delete bonus-remove"  title="Отменить"></a>
                        {/if}
                           {if 1 == $bonus.request}
                              <a href="/admin/employee/bonus/remove/{$bonus.id}?salary_id={$salary_id}" data-salary="{$salary_id}" data-bonus="{$bonus.id}"
                                        class="{if 0 < $bonus.approved_id}icon-ok{else}icon-check{/if} bonus-remove"  title="Отменить {if 1 == $bonus.request}по запросу{/if} {if 0 < $bonus.approved_id}Одобрено: {$bonus.approved_name}{/if}"></a>
                              <a href="/admin/employee/bonus/cancelrequest/{$bonus.id}?salary_id={$salary_id}" data-salary="{$salary_id}" data-bonus="{$bonus.id}"
                                        class="icon-delete small_mini request-cancel"  title="Отклонить запрос"></a>
                            {/if}
                            {if '2'  == $bonus.request}
                                {if 0  < $bonus.creator_id}
                                    <a href="/admin/employee/bonus/remove/{$bonus.id}?salary_id={$salary_id}" data-salary="{$salary_id}" data-bonus="{$bonus.id}"
                                        class="icon-delete bonus-remove"  title="Отменить"></a>
                                    <span class="icon-ok2" title="Одобрен директором {if 0 < $bonus.approved_id} и {$bonus.approved_name}{/if}"></span>
                                {else}
                                    <a href="/admin/employee/bonus/remove/{$bonus.id}?salary_id={$salary_id}" data-salary="{$salary_id}" data-bonus="{$bonus.id}"
                                        class="icon-delete bonus-remove"  title="Отменить"></a>
                                {/if}
                            {/if}
                    {else}
                        {if $auth_user == $bonus.creator_id}
                            <span class="icon-ok2" title="одобрено"></span>
                        {else}
                            {if 1 == $is_main}
                                {if 0 == $bonus.request}
                                    <a href="/admin/employee/bonus/request/{$bonus.id}" class="icon-delete small_mini add-request" title="Запросить отмену"></a>
                                {else}
                                    {if 1 == $bonus.request}
                                        {if 0 < $bonus.approved_id}
                                            <span class="icon-ok" title="Одобрен {$bonus.approved_name}"></span>
                                        {else}
                                            <a href="/admin/employee/bonus/removemainrequest/{$bonus.id}" class="icon-delete bonus-removemain" title="Отменить запрос"></a>
                                        {/if}
                                    {else}
                                        <a href="/admin/employee/bonus/request/{$bonus.id}" class="icon-delete small_mini add-request" title="Запросить отмену"></a>
                                    {/if}
                                {/if}
                            {/if}
                            {if false != $is_chief}
                                {if 1 != $is_main}
                                    {if 1 == $bonus.request}
                                        {if 0 < $bonus.approved_id}
                                            <span class="icon-ok" title="Одобрено {$bonus.approved_name}"></span>
                                                <a href="/admin/employee/bonus/cancelrequest/{$bonus.id}?salary_id={$salary_id}" data-salary="{$salary_id}" data-bonus="{$bonus.id}"
                                        class="icon-delete small_mini request-cancel"  title="Отклонить запрос"></a>
                                        {else}
                                        <a href="/admin/employee/bonus/approvedchief/{$bonus.id}?salary_id={$salary_id}" data-salary="{$salary_id}" data-id="{$bonus.id}" class="icon-check bonus-approvedchief"
                                   title="Одобрить запрос на отмену"></a>
                                            <a href="/admin/employee/bonus/cancelrequest/{$bonus.id}?salary_id={$salary_id}" data-salary="{$salary_id}" data-bonus="{$bonus.id}"
                                        class="icon-delete small_mini request-cancel"  title="Отклонить запрос"></a>
                                        {/if}
                                    {/if}
                                {/if}
                            {/if}
                        {/if}
                    {/if}
                {else}
                    {if false !== $is_rule}
                        {if false}
                            <a href="/admin/employee/bonus/edit/{$bonus.id}?salary_id={$salary_id}" data-salary="{$salary_id}" data-id="{$bonus.id}" class="icon-edit" title="Редактировать"></a>
                        {/if}
                        <a href="/admin/employee/bonus/approved/{$bonus.id}?salary_id={$salary_id}" data-salary="{$salary_id}" data-id="{$bonus.id}"
                           class="{if 0 < $bonus.approved_id}icon-ok{else}icon-check{/if} bonus-aproved" title="{if 0 < $bonus.approved_id}Одобрен {$bonus.approved_name}{else}Одобрить{/if}"></a>
                        {if 1 == $bonus.request}
                            <a href="/admin/employee/bonus/remove/{$bonus.id}?salary_id={$salary_id}" data-salary="{$salary_id}"
                               data-id="{$bonus.id}" class="icon-delete small_mini bonus-remove" title="отклонить запрос"></a>
                        {/if}
                        {if false}
                            <a href="/admin/employee/bonus/up/{$bonus.id}?salary_id={$salary_id}" data-salary="{$salary_id}" data-id="{$bonus.id}" class="icon-play" title="перенести на следующий месяц"></a>
                        {/if}
                    {else}
                        {if $auth_user == $bonus.creator_id}
                            {if 2 == $bonus.request}
                                <span class="icon-clear" title="Отменен">Отменен</a>
                            {else}
                                {if 0 < $bonus.approved_id}
                                    <span class="icon-ok" title="Одобрен {$bonus.approved_name}"></span>
                                {else}
                                    <a href="/admin/employee/bonus/removemain/{$bonus.id}" class="icon-delete bonus-removemain" title="Отменить"></a>
                                {/if}
                            {/if}
                        {else}
                            {if 1 == $is_main}
                                {if 0 == $bonus.request}
                                    {if 2 != $bonus.is_approved}
                                        <a href="/admin/employee/bonus/request/{$bonus.id}" class="icon-check add-request" title="Запросить отмену"></a>
                                    {/if}
                                {else}
                                    {if 1 == $bonus.request}
                                        {if 0 > $bonus.approved_id}
                                            <span class="icon-ok" title="Одобрен начальником подразделения"></span>
                                        {else}
                                            <a href="/admin/employee/bonus/removemain/{$bonus.id}" class="icon-delete small_mini bonus-removemain" title="Отменить запрос"></a>
                                        {/if}
                                    {else}
                                        <span class="icon-clear" title="Отменен">Отменен</a>
                                    {/if}
                                {/if}
                            {else}
                                {if false != $is_chief}
                                        {if 0 < $bonus.approved_id}
                                            <span class="icon-ok" title="Одобрено {$bonus.approved_name}"></span>
                                            {if 2 != $bonus.is_approved}
                                                <a href="/admin/employee/bonus/cancelrequest/{$bonus.id}?salary_id={$salary_id}" data-salary="{$salary_id}" data-bonus="{$bonus.id}"
                                                        class="icon-delete small_mini request-cancel"  title="Отклонить запрос"></a>
                                            {/if}	
                                        {else}
                                            <a href="/admin/employee/bonus/approvedchief/{$bonus.id}?salary_id={$salary_id}" data-salary="{$salary_id}" data-bonus="{$bonus.id}" class="icon-check bonus-approvedchief"
                                                        title="одобрить"></a>
                                            {if 2 != $bonus.is_approved}
                                                <a href="/admin/employee/bonus/remove/{$bonus.id}?salary_id={$salary_id}" data-salary="{$salary_id}" data-bonus="{$bonus.id}"
                                                    class="icon-delete small_mini bonus-remove"  title="Отклонить запрос"></a>
                                            {/if}
                                        {/if}
                                {/if}
                            {/if}
                        {/if}
                    {/if}
                {/if}
            {else}
                <a href="/admin/employee/bonus/request/{$bonus.id}" class="add-request" title="Запрос на отмену"></a>
                <a href="/admin/employee/bonus/timesheet/{$bonus.id}" class="add-request-time" data-date="{$bonus.date}" data-start="{$bonus.start}" data-end="{$bonus.start}" title="Запрос на изменения графика"></a>
            {/if}
    </td>
</tr>
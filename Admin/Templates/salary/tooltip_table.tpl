<table class="table suser" style="width:600px;">
    <thead class="thead" data-section="plans">
        <tr>
            <th>Показатели</th>
            <th width="70px">Влияние/ ставка</th>
            {if null !== $prev_salary}
                <th class="pf-td" width="70px">План</th>
                <th class="pf-td" width="70px">Факт</th>
                <th width="100px">Выполнение</th>
            {/if}
            <th class="pf-td" width="70px">План</th>
            <th class="pf-td" width="70px">Факт</th>
            <th width="100px">Выполнение</th>
            <th width="150px">Ставка/общий процент</th>
            <th class="price-td" width="50px">
                <span id="show_all_money" title="Посмотреть все цифры">Начислено</span>
            </th>
        </tr>
        <tr>
            <th></th>
            <th width="70px"></th>
            {if null !== $prev_salary}
                <th class="pf-td" width="240px" colspan="3">
                    {assign var=_stamp value=strtotime("`$month_prev.year`-`$month_prev.month`-01")}
                    {$_stamp|date_format:"%B %Y"}
                </th>
            {/if}
            <th class="pf-td" width="240px" colspan="3">
                {assign var=_stamp value=strtotime("`$cyear`-`$cmount`-01")}
                {$_stamp|date_format:"%B %Y"}
            </th>
            <th width="150px"></th>
            <th class="price-td" width="50px">
            </th>
        </tr>
    </thead>
    <tbody class="tbody section-plans">
    {if 0 < $cplan_isbased}
        <tr class="it-plan heads">
            <td colspan="{if null !== $prev_salary}10{else}7{/if}">Плановые</td>
        </tr>
        {foreach from=$plan_based item=_plan}
            {if ! isset($_plan.hidden)}
            <tr class="it-plan">
                <td class="tdname">
                    {$_plan.name}
                    {if 0 < $_plan.department_id}
                        {assign var=did value=$_plan.department_id }
                        {if isset($edepartments.$did)}
                            [{$edepartments.$did.name}]
                        {else}
                            [{$_plan.department_id}]
                        {/if}
                    {/if}
                </td>
                <td class="tdvalue">
                    {$_plan.value|round} %
                </td>
                {if null !== $prev_salary}
                    <td class="tdplan">
                        {if false != $_plan.prev}
                            {$_plan.prev.plan|price_format}
                        {/if}
                    </td>
                    <td class="tdfact">
                        {if false != $_plan.prev}
                            {$_plan.prev.fact|price_format:0}
                        {/if}
                    </td>
                    <td class="tdtempo">
                        {if false != $_plan.prev}
                            {if $_plan.prev.plan > 0}
                                {if 0 == $_plan.prev.is_negative}
                                    {assign var=_percent value=(($_plan.prev.fact/$_plan.prev.plan)*100|round)}
                                    {$_percent|round}%
                                {else}
                                    {assign var=_percent value=((2-$_plan.prev.fact/$_plan.prev.plan)*100)|round}
                                    {$_percent|round}%
                                {/if}
                            {else}
                                0 %
                            {/if}
                            {if 0 == $_plan.prev.is_negative}
                                {if 0 < $_plan.prev.plan}
                                    {if 0 > $_plan.prev.tempo}
                                        <span class="icon-down" title="отставание от плана">{($_plan.prev.tempo)|round}%</span>
                                    {else}
                                        <span class="icon-up" title="Опережение плана">{($_plan.prev.tempo)|round}%</span>
                                    {/if}
                                {/if}
                            {else}
                                {if $_plan.prev.plan > 0}
                                    {assign var=_tempo value=round(100-(($_plan.prev.fact/$_plan.prev.plan)*100))}
                                    {if 0 > $_tempo}
                                        <span class="icon-down" title="отставание от плана">{$_tempo}%</span>
                                    {else}
                                        <span class="icon-up" title="Опережение плана">{$_tempo}%</span>
                                    {/if}
                                {/if}
                            {/if}
                        {/if}
                    </td>
                {/if}
                <td class="tdplan">{$_plan.plan|price_format}</td>
                <td class="tdfact">{$_plan.fact|price_format:0}</td>
                <td class="tdtempo">
                    {if $_plan.plan > 0}
                        {if 0 == $_plan.is_negative}
                            {assign var=_percent value=(($_plan.fact/$_plan.plan)*100|round)}
                            {$_percent|round}%
                        {else}
                            {assign var=_percent value=((2-$_plan.fact/$_plan.plan)*100)|round}
                            {$_percent|round}%
                        {/if}
                    {else}
                        0 %
                    {/if}
                    {if 0 == $_plan.is_negative}
                        {if 0 < $_plan.plan}
                            {if 0 > $_plan.tempo}
                                <span class="icon-down" title="отставание от плана">{($_plan.tempo)|round}%</span>
                            {else}
                                <span class="icon-up" title="Опережение плана">{($_plan.tempo)|round}%</span>
                            {/if}
                        {/if}
                    {else}
                        {if $_plan.plan > 0}
                            {assign var=_tempo value=round(100-(($_plan.fact/$_plan.plan)*100))}
                            {if 0 > $_tempo}
                                <span class="icon-down" title="отставание от плана">{$_tempo}%</span>
                            {else}
                                <span class="icon-up" title="Опережение плана">{$_tempo}%</span>
                            {/if}
                        {/if}
                    {/if}
                </td>
                {if null !== $summary_percent}
                    <td rowspan="{$cplan_isbased}" class="tdtempo">
                        <div class="base2">
                            <span class="money">{$esalary.base|price_format}</span>
                        </div>
                        <div class="percents">
                            <span class="percent">{$summary_percent}%</span>
                            {if $summary_percent > 100}
                                <span class="icon-up" title="Опережение плана">{($summary_percent - 100)}%</span>
                            {else}
                                <span class="icon-down" title="Отставание от плана">{($summary_percent - 100)}%</span>
                            {/if}
                        </div>
                    </td>
                    {assign var=summary_percent value=null}
                {/if}
                {if 0 < $cplan_isbased}
                    <td rowspan="{$cplan_isbased}" class="price-td">
                        <span>{$salary.total_plan|price_format:0}</span>
                    </td>
                    {assign var=cplan_isbased value=0}
                {/if}
            </tr>
            {/if}
        {/foreach}
    {/if}
    {if 0 < $cplan_nobased}
        <tr class="it-plan heads">
            <td colspan="{if null !== $prev_salary}10{else}7{/if}">Сдельные</td>
        </tr>
        {foreach from=$plan_nobased item=_plan}
            <tr class="it-plan">
                <td class="tdname">
                    {$_plan.name}
                    {if 0 < $_plan.department_id}
                        {assign var=did value=$_plan.department_id }
                        {if isset($edepartments.$did)}
                            [{$edepartments.$did.name}]
                        {else}
                            [{$_plan.department_id}]
                        {/if}
                    {/if}
                </td>
                <td class="tdvalue">
                        {if 0 == $_plan.is_discrete}
                            {$_plan.value*100} %
                        {else}
                            {$_plan.value|price_format} за {$_plan.measurement}
                        {/if}
                </td>
                {if null !== $prev_salary}
                    <td class="tdplan"></td>
                    <td class="tdfact">
                        {if false != $_plan.prev}
                            {if 0 < $_plan.prev.fact}
                                {if 1 == $_plan.prev.is_discrete}
                                    <span class="money">{$_plan.prev.fact}</span>
                                {else}
                                    <span class="money">{$_plan.prev.fact|price_format}</span>
                                {/if}
                            {/if}
                        {/if}
                    </td>
                    <td></td>
                {/if}
                <td></td>
                <td class="tdfact">
                    {if 0 < $_plan.fact}
                        {if 1 == $_plan.is_discrete}
                            <span class="money">{$_plan.fact}</span>
                        {else}
                            <span class="money">{$_plan.fact|price_format}</span>
                        {/if}
                    {/if}
                </td>
                <td colspan="2"></td>
                <td class="price-td">
                    {if 0 < $_plan.total}
                        <span>{$_plan.total|price_format:0}</span>
                    {/if}
                </td>
            </tr>
        {/foreach}
    {/if}
    <tr class="it-plan heads">
        <td colspan="{if null !== $prev_salary}9{}6{/if}">Депремирования</td>
        <td class="price-td">
            {$summary_bonus|price_format:0}
        </td>
    </tr>
    </tbody>
</table>
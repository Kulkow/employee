{$form->open("/admin/employee/timemanager/edit/")}
{assign var=_id value=$form->getData('id',0)}
<div id="tmanager_popup">
    <div class="l">
        <h2>Текущий график</h2>
        {if null != $tmanager}
            {foreach from=$days key=day item=name}
                {assign var=freeday value=''}
                {if 0 == $day}
                    {assign var=freeday value=freeday}
                {/if}
                {if 6 == $day}
                    {assign var=freeday value=freeday}
                {/if}
            <div class="day-row day{$day}">
                <div class="row day-cell name">
                    {$name}
                </div>
                <div class="row day-cell d_s">
                    {assign var=s value="s`$day`"}
                    {if isset($tmanager.$s)}
                        {$tmanager.$s|date_format:"H:i"}
                    {/if}
                </div>
                <div class="row day-cell delimiter">
                    -
                </div>
                <div class="row day-cell d_e">
                    {assign var=e value="e`$day`"}
                    {if isset($tmanager.$e)}
                        {$tmanager.$e|date_format:"H:i"}
                    {/if}
                </div>
            </div>
            {/foreach}
        {else}
            {foreach from=$days key=day item=name}
                {assign var=freeday value=''}
                {if 0 == $day}
                    {assign var=freeday value=freeday}
                {/if}
                {if 6 == $day}
                    {assign var=freeday value=freeday}
                {/if}
            <div class="day-row day{$day}">
                <div class="row day-cell name">
                    
                </div>
                <div class="row day-cell delimiter">
                    
                </div>
                <div class="row day-cell name">
                    {$name}
                </div>
            </div>
            {/foreach}
        {/if}
    </div>
    <div class="r">
        <h2>Изменить</h2>
        {foreach from=$days key=day item=name}
            {assign var=freeday value=''}
            {if 0 == $day}
                {assign var=freeday value=freeday}
            {/if}
            {if 6 == $day}
                {assign var=freeday value=freeday}
            {/if}
        <div class="day-row day{$day}">
            <div class="row day-cell">
                {$form->text("s`$day`", ['class' => "start txt `$freeday`", 'style' => 'width:50px'])}
            </div>
            <div class="row day-cell delimiter">
                -
            </div>
            <div class="row day-cell">
                {$form->text("e`$day`", ['class' => "end txt `$freeday`", 'style' => 'width:50px'])}
            </div>
            <div class="row day-cell actions">
                <span class="icon-clear small"></span>
            </div>
        </div>
        {/foreach}
    </div>
</div>
<div id="tmanager_popup">
    <div class="l" style="min-height: 30px">
        <div class="row day-cell">
            {$form->label("since", "Действует с")}<br />
            {$form->text("since", ['class' => "txt datepicker", 'style' => "width:120px"])}
        </div>
    </div>
    <div class="r" style="min-height: 30px">
        {if 0 !== $_id}
        <div class="row day-cell">
            {$form->label("since_new", "новый график будет действовать с")}<br />
            {$form->text("since_new", ['class' => "txt datepicker", 'style' => "width:120px"])}
        </div>
        {/if}
    </div>
</div>
<div class="set-action timemanager">
    <span class="btn" data-s="09:00" data-e="18:00">с 9:00</span>
    <span class="btn" data-s="10:00" data-e="19:00">с 10:00</span>
</div>
<div>
    {$form->security()}
    {$form->hidden('user_id')}
    {$form->hidden('id')}
</div>
<div class="row">
    {$form->submit('Сохранить', ['class' => 'btn submit green'])}
</div>
{$form->close()}
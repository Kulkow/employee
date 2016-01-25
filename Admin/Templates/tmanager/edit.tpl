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
                <div class="row day-cell">
                    {assign var=s value="s`$day`"}
                    {if isset($tmanager.$s)}
                        {$tmanager.$s|date_format:"H:i"}
                    {/if}
                </div>
                <div class="row day-cell delimiter">
                    -
                </div>
                <div class="row day-cell">
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
<div class="tmanager_popup">
    <div class="l" style="min-height: 30px">
        <div class="row day-cell">
            {$form->label("since", "Действует с")}<br />
            {if false != $is_rule}
                {$form->text("since", ['class' => "txt datepicker", 'style' => "width:120px"])}
            {else}
                {$form->getData("since")}
                {if 0 !== $_id}
                    {$form->hidden("since")}
                {else}
                    {$form->text("since", ['class' => "txt datepicker", 'style' => "width:120px"])}
                {/if}
            {/if}
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
    <span class="progress {if 0== $tmanager_progress.percent}red{/if}">Всего: <span class="hour">{$tmanager_progress.count}</span> ч {if 0== $tmanager_progress.percent} - должно быть 45{/if}</span>
    {$form->submit('Сохранить', ['class' => 'btn green'])}
</div>
{$form->close()}
{literal}
<script>
    $(function(){
        $('#TimeManageEdit').form({
            submit: function(response) {
                if (!response.errors){
                    //console.log(response);
                }else{
                    console.log(response.errors);
                }
            }
        })
        $('.datepicker').datepicker();
        $('.end.txt').mask('99:99');
        $('.start.txt').mask('99:99');
        $('.set-action span').click(function(){
            var _span = $(this), _s = _span.data('s'), _e = _span.data('e');
            $('.start.txt:not(.freeday)').val(_s);
            $('.end.txt:not(.freeday)').val(_e);
        })
        $('.day-row').on('click', '.icon-clear', function(){
            var _span = $(this), _day_row = _span.closest('.day-row');
            $('.txt', _day_row).val('');
        })
    })
</script>
{/literal}
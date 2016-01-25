{$filter->open()}
<div class="grid-table">
    <div class="grid-row">
        <div class="grid-cell">
            {$filter->label('date', 'Дата')}
            {$filter->text('date',['class' => 'txt'])}
        </div>
        <div class="grid-cell">
            {$filter->submit('Поиск',['class' => 'btn'])}
        </div>
    </div>
</div>
{$filter->security()}
{$filter->close()}
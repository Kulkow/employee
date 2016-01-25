{$filter->open()}
<div class="grid-table">
    <div class="grid-row">
        <div class="grid-cell">
            {$filter->label('name', 'ФИО')}
            {$filter->text('name',['class' => 'txt'])}
        </div>
        <div class="grid-cell">
            {$filter->label('department', 'Подразделение')}
            {$filter->select('department',[],['class' => 'txt'])}
        </div>
        <div class="grid-cell">
            {$filter->submit('Поиск',['class' => 'btn'])}
        </div>
    </div>
</div>
{$filter->security()}
{$filter->close()}
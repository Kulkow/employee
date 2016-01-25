{$form->open()}
<div class="cell add">
    {$form->text("nvalue[]",['class' => 'txt'])}
    {$form->select("nplan_id[]",$form->htmlOptions($plans), ['class' => 'select'])}
    <a class="icon-delete" href="javascript;;" data-new="1" title="Удалить показатель пользователя"></a>
</div>
{$form->close()}
{include file='menu.tpl'}
{if null == $plan}
    {$form->open('/admin/employee/plan/add')}
{else}
    <h1>Редактирование плана</h1>
    {$form->open("/admin/employee/plan/edit/`$plan.id`")}
{/if}
<table class="table" style="width:500px">
    <tr>
        <td>{$form->label('name', 'Название')}</td>
        <td>{$form->text('name', ['class' => 'txt'])}</td>
    </tr>
    <tr>
        <td>{$form->label('alias', 'Код')}</td>
        <td>{$form->text('alias', ['class' => 'txt'])}</td>
    </tr>
    <tr>
    <tr>
        <td>{$form->label('measurement', 'Ед. измерения')}</td>
        <td>{$form->text('measurement', ['class' => 'txt'])}</td>
    </tr>
    <tr>
        <td>{$form->label('is_negative', 'Положительная / отрицательная динамика')}</td>
        <td>{$form->checkbox('is_negative')}</td>
    </tr>
    <tr>
        <td>{$form->label('is_plan_based', 'Зависит от плана')}</td>
        <td>{$form->checkbox('is_plan_based')}</td>
    </tr>
    {if null !== $plan}
        {if 1 == $plan.is_common}
        <tr>
            <td>{$form->label('pid', 'Связан с личным планом')}</td>
            <td>{$form->select('pid', [0 => '--'] + $form->htmlOptions($plans),['class' => 'select', 'style' => 'width:200px'])}</td>
        </tr>
        {/if}
    {/if}
    <tr>
        <td>{$form->label('is_common', 'Личный или для подразделения')}</td>
        <td>{$form->checkbox('is_common')}</td>
    </tr>
    <tr>
        <td>{$form->label('is_discrete', 'Целый или процент для не плановых показателей')}</td>
        <td>{$form->checkbox('is_discrete')}</td>
    </tr>
</table>    
<div>
    {$form->hidden('id')}
    {$form->security()}
</div>
{$form->submit('Сохранить', ['class' => 'btn'])}
{$form->close()}
{if null  !== $plan}
<div class="wrapper">
    <h4>Связанные показатели</h4>
    <table class="table" style="width:500px">
    {foreach from=$plan_plans item=pplan}
        <tr class="pplan">
            <td>{$pplan.plan}</td>
            <td width="25px"><a href="/admin/employee/plan/removeplan/{$pplan.id}" title="" class="icon-delete"></td>
        </tr>
    {/foreach}
    </table>
</div>
<div class="wrapper">
    {$app->forward('/admin/employee/plan/addplan', ['id' => $plan.id])}
</div>
{/if}
<script>
$(function() {
    $('#PlanEdit2').form({
        submit: function(response) {
            if (!response.errors){
                window.location.reload();
            }else{
                console.log(response.errors);
            }
        }
    });
    $('.pplan .icon-delete').click(function(){
        var tr = $(this).closest('tr');
        $.get(this.href, function(data){
            tr.remove();
        })
        return !1;
    })
});
</script>

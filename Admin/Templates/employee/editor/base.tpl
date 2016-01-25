<div id="employee-editor">
    <!-- 0 START -->
    <div class="employee-editor-block" id="employee-login">
        <div class="employee-editor-head">
            <span class="icon-expand">0. Сотрудник</span>
        </div>
        <div class="employee-editor-wrapper hidden">
            {assign var=form value=$form0}
            {include file='employee/editor/login.tpl'}
        </div>
    </div>
    <!-- 0 END -->
    <!-- 1 START -->
    <div class="employee-editor-block"  id="employee-doc">
        <div class="employee-editor-head">
            <span class="icon-expand">1. Документы</span> <span class="progress percent_{$doc_progress.percent}" data-required="{$doc_progress.required}">({$doc_progress.count}/{$doc_progress.all})</span>
        </div>
        <div class="employee-editor-wrapper hidden">
            {assign var=form value=$form1}
            {include file='employee/editor/doc.tpl'}
        </div>
    </div>
    <!-- 1 END -->
    
    <!-- 2 START -->
    <div class="employee-editor-block"  id="employee-contact">
        <div class="employee-editor-head">
            <span class="icon-expand">2. Контакты</span> <span class="progress percent_{$contact_progress.percent}" data-requred="{$contact_progress.required}">({$contact_progress.count}/{$contact_progress.all})</span>
        </div>
        <div class="employee-editor-wrapper hidden">
            {assign var=form value=$form2}
            {include file='employee/editor/contacts.tpl'}
        </div>
    </div>
    <!-- 2 END -->
    
    <!-- 3 START -->
    <div class="employee-editor-block"  id="employee-department">
        <div class="employee-editor-head">
            <span class="icon-expand">3. Подразделение</span>
        </div>
        <div class="employee-editor-wrapper hidden">
            {assign var=form value=$form3}
            {include file='employee/editor/department.tpl'}
        </div>
    </div>
    <!-- 3 END -->
    
    <!-- 4 START -->
    <div class="employee-editor-block"  id="employee-rule">
        <div class="employee-editor-head">
            <span class="icon-expand">4. Роли</span>{if 3 == $employee.role_id} <span class="progress red" title="Не имеет доступа в админку">( ! )</span>{/if}
        </div>
        <div class="employee-editor-wrapper hidden">
            {assign var=form value=$form4}
            {include file='employee/editor/codex.tpl'}
        </div>
    </div>
    <!-- 4 END -->
    
    <!-- 5 START -->
    <div class="employee-editor-block"  id="employee-grafik">
        <div class="employee-editor-head">
            <span class="icon-expand">5. График</span>{if null == $tmanager_progress.percent} <span class="progress red" title="Не выставлен график">( {$tmanager_progress.count}/{$tmanager_progress.all})</span>{/if}
        </div>
        <div class="employee-editor-wrapper hidden">
            {assign var=form value=$form5}
            {include file='employee/editor/timemanager.tpl'}
        </div>
    </div>
    <!-- 5 END -->
    
    <!-- 6 START -->
    <div class="employee-editor-block"  id="employee-plan">
        <div class="employee-editor-head">
            <span class="icon-expand">6. Планы</span>{if null == $eplans} <span class="progress red" title="Не выставлено ни одного показателя">( ! )</span>{/if}
        </div>
        <div class="employee-editor-wrapper hidden">
            <div style="width:100%; position: relative; overflow: hidden; margin: 0 0 10px 0;">
                <div style="width:45%; position: relative; float: left;">
                {assign var=form value=$form7}
                {include file='employee/editor/esalary.tpl'}
                </div>
                <div style="width:55%; position: relative; float: right;">
            {assign var=form value=$form6}
            {include file='employee/editor/plans.tpl'}
        </div>
    </div>
    <!-- 6 END -->
    {if false}
    <!-- 7 START -->
    <div class="employee-editor-block"  id="employee-salary">
        <div class="employee-editor-head">
            <span class="icon-expand">7. Ставки и налоги</span>
        </div>
        <div class="employee-editor-wrapper hidden">
            {assign var=form value=$form7}
            {include file='employee/editor/esalary.tpl'}
        </div>
    </div>
    {/if}
    <!-- 7 END -->
</div>
<script src="/js/admin/employee-editor.js?v={$smarty.now}"></script>
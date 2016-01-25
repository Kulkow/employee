<div id="employee-editor">
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
</div>
<script src="/js/admin/employee-editor.js?v={$smarty.now}"></script>
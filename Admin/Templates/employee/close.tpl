{$form->open()}
<div class="row">
    {$employee.department}
</div>
<div class="row">
    {$form->label('end', 'с какого дня')}
    {$form->text('end', ['class' => 'txt datepicker', 'style' => 'width:150px'])}
</div>
<div class="row">
    {$form->submit('Отвязать', ['class' => 'btn'])}
</div>
<div>
    {$form->hidden('id')}
    {$form->security()}
    {$form->close()}
</div>
{literal}
    <script>
        $(function(){
            $('#EmployeeClose').form({
                submit: function(response) {
                    if (!response.errors){
                        console.log(response);
                        /*window.location.reload();*/
                    }else{
                        console.log(response.errors);
                    }
                }
            });
        })
    </script>
{/literal}
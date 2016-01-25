<div id="BaseCalculator">
{$form->open("/admin/employee/calculator/base/`$user_id`")}
    <div class="row">
        {$form->label('base1', 'Оклад на испытательный срока')}
        {$form->text('base1',['class' => 'txt'])}
    </div>
    <div class="row">
        {$form->label('base2', 'Оклад После испытательный срока')}
        {$form->text('base2',['class' => 'txt'])}
    </div>
    <div class="row">
        {$form->label('start', 'Дата начала работы')}
        {$form->text('start',['class' => 'txt datepiker'])}
    </div>
    <div class="row">
        {$form->label('end', 'Дата окончания испытательного срока')}
        {$form->text('end',['class' => 'txt datepiker'])}
    </div>
    <div class="row">
        <span>Испытательный срок</span><br />
        <span class="btn set-experiment" data-mess="days" data-experiment="14">2 недели</span>
        <span class="btn set-experiment" data-mess="month" data-experiment="1">1 месяц</span>
        <span class="btn set-experiment" data-mess="month" data-experiment="2">2 месяца</span>
    </div>
    <div class="row">
        <span>Рассчет на {$data|date_format:"Y-m"}</span>
    </div>
    <div class="row">
        <div>Рассчет <span id="result_calculator_html"></span>=<b><span id="result_calculator"></span></b>
        </div>
        
    </div>
    <div>
        {$form->hidden('user_id')}
        {$form->hidden('date')}
        {$form->security()}
        {$form->submit('Расчитать', ['class' => 'btn'])}
    </div>
{$form->close()}
</div>
{literal}
    <script>
        $('.datepiker').datepicker();
        $('#BaseCalculator .set-experiment').click(function(){
            var _span = $(this), _mess = _span.data('mess'), _interval = _span.data('experiment');
            var _start = $('#Base-start').val();
            if ('' != _start) {
                var _startpath = _start.split('.');
                var _Y = _startpath[2], _M = _startpath[1], _D = _startpath[0];
                _Y = +_Y;
                _M = +_M-1;
                _D = +_D;
                var _startTime = new Date(_Y, _M, _D, 0, 0, 0);
                if ('days' == _mess) {
                    var _End = new Date(_startTime.setDate(_startTime.getDate()+_interval));
                }
                if ('month' == _mess) {
                    var _End = new Date(_startTime.setMonth(_startTime.getMonth()+_interval));
                }
                _Y = _End.getFullYear();
                _M = +_End.getMonth()+1;
                _D = +_End.getDate();
                var _end_str = _D +'.' + _M +'.' + _Y;
                console.log(_end_str);
                $('#Base-end').val(_end_str)
            }
        })
        $('#Base').form({
            submit: function(response) {
              if(!response.errors){
                if(response.base){
                    console.log(response.base);
                    $('#result_calculator').html(response.base.result);
                    $('#result_calculator_html').html(response.base.html);
                }
              }
            },
        });
        var jan312009 = new Date(2009, 0, 31);
        var eightMonthsFromJan312009 = new Date(new Date(jan312009).setMonth(jan312009.getMonth()+8));
        
    </script>
{/literal}

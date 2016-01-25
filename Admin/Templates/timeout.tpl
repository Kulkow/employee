<script>
    $(function(){
        var timer = document.createElement('div');
        timer.id = 'vitimer';
        timer.title = "Сбросить";
        timer.innerHTML = '<span class="value" id="vitimer_timeout" data-timer-all="{$timeout}" data-timer="{$timeout}">{$timeout}</span>с';
        document.body.appendChild(timer);
        var interval = null;
        interval = setInterval( function(){
            var vitimer = document.getElementById('vitimer_timeout'), _timeout = vitimer.getAttribute('data-timer');
            if (_timeout == 0 || _timeout == 'Nan') {
                var url = window.location.protocol+'//'+window.location.hostname+'/admin/employee/cabinet/logout';
                window.location = url;
            }
            _timeout = +_timeout - 1;
            vitimer.setAttribute('data-timer',_timeout);
            vitimer.innerHTML = _timeout;
            
        }, 1000);
        $('body').on('click', '#vitimer', function(){
            var _t = $(this).find('span').data('timer-all');
            $(this).find('span').data('timer', _t).attr('data-timer',_t).text(_t);
        });
    })
</script>
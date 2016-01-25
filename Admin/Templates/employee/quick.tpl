<div class="detail">
    <table class="table">
        <tr>
            <td>ФИО</td>
            <td>{$employee.name}</td>
        </tr>
        <tr class="toggle" data-tbogy="carier">
            <th colspan="2">Карьера</td>
        </tr>
        <tbody class="carier">
            <tr>
                <td>Статус</td>
                <td>{$employee.status}</td>
            </tr>
            <tr>
                <td>Подразделение</td>
                <td>{$employee.department}</td>
            </tr>
            <tr>
                <td>Начало работы</td>
                <td>{$employee.start}</td>
            </tr>
            <tr>
                <td>Стаж</td>
                <td>{$employee.experience|date_format}</td>
            </tr>
        </tbody>
        <tr class="toggle" data-tbogy="personal">
            <th colspan="2">Данные</td>
        </tr>
        <tbody class="personal">
            <tr>
                <td>Телефон</td>
                <td>{$employee.phone}</td>
            </tr>
            <tr>
                <td>Skype</td>
                <td>{$employee.skype}</td>
            </tr>
        </tbody>
    </table>
<div>
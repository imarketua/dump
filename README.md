
# CMS сайта i-Market.com.ua

Недавно поработал с человеком: https://vk.com/id299146299 (https://vk.com/reqiill) над сайтом http://i-market.com.ua/. Суть была в создании мобильной версии и правке некоторых проблем на сайте.
По его словам деньги были переведены с яндекса на карту, предоставил скриншот. Перевода я так и не дождался, а по истечении 7 рабочих дней после отправки транзакции, данный тип попросту стал игнорировать.
Гугл подсказал что от "Виталия Княгеева" пострадало еще несколько человек. Думаю будет правильным если я выложу тот жуткий говнокод над которым пришлось работать в свободный доступ. 
Разумеется номера и почтовые адреса в дампе базы данных были удаленны. 

### Инструкция по установке:
1. Заливаем все файлы на сервер.
2. Необходим chmod 0777 на папку "/photo/" в корне.
3. Заливаем БД.
4. Настраиваем в mysql.php подключение к БД.
5. Бегаем поиском у удаляем везде захардкоденный домен "i-market.com.ua".
6. Регестрируемся и даем себе права админа в БД.

P.S. Скрипт кривой, если собираетесь использовать его на продакшене, наймите кодера который бы залатал уязвимые места и оптимизировал работу странно написанного кода.

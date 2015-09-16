function zero()
{
	$('#mapCanvas').stop();
	$("#mapCanvas").css('opacity', '0.0');
}
function slideup()
{
	$("#mapCanvas").animate({'opacity': '1.0'}, 120);
}
function canvasBrowser() {
    // Определяем тип браузера
    var ua = navigator.userAgent.toLowerCase();
    var isIE = (ua.indexOf("msie") != -1 && ua.indexOf("opera") == -1 && ua.indexOf("webtv") == -1); 
    var isOpera = ua.indexOf("opera") != -1; 
    var isFF = ua.indexOf("firefox") != -1;
    var isSafari = ua.indexOf("safari") != -1;
    var isChrome = ua.indexOf("chrome") != -1;
    
    var result = false;
    // Разрешим все версии IE
    if (isIE) result = true;
    // Лису разрешим только с полторашки
    else if (isFF) {
        var ffVersion = parseFloat(ua.substring(ua.indexOf("firefox") + 8, ua.length));
        if (ffVersion >= 1.5) result = true;
    // Оперу, начиная с версии 9.0
    } else if (isOpera) {
        var operaVersion = parseFloat(ua.substring(ua.indexOf("opera") + 6, ua.length));
        if (operaVersion >= 9.0) result = true;
    } else if (isSafari || isChrome) result = true;
    return result;
}

// Кеш координат
var coordsCashe = [];
// Прикручиваем события
function initImageMap() {
    if (!canvasBrowser()) return;
    var map = document.getElementById("maps");
    var area, i;
    for (i = 0; i < map.childNodes.length; i++) {
        area = map.childNodes[i];
        // Проверяем тип узла
        if (area.nodeType != 1) continue;
        // Проверяем, что узел является элементом area
        if (area.nodeName.toLowerCase() != "area") continue;
        // Добавляем ID c ключом массива координат
        area.id = "id" + i;
        // Добавляем к элементу обработчики событий
        area.onmouseover = mouseOverHandler;
        area.onmouseout = mouseOutHandler;
        // Кешируем координаты
        coordsCashe[i] = parseCoords(area.coords);
    }
}

// Обработчик события mouseover
function mouseOverHandler() {
	zero();
	slideup();
    // Вырезаем индекс для массива координат из ID
    var i = this.id.substring(2, this.id.length);
    // Рисуем многоугольник на полученной области
    drawPoly(
        "mapCanvas",
        coordsCashe[i]
    );
}
// Обработчик события mouseout
function mouseOutHandler() {
    // Стираем нарисованное в canvas
    clearCanvas("mapCanvas");
}

// Парсим строку с координатами, перечисленными через запятую, в двумерный массив
function parseCoords(str) {
    var coords = [];
    var buferArray = str.split(",");
    var j = 0;
    for (var i = 0; i < buferArray.length; i++) {
        if (i % 2 == 0) {
            coords[j] = [];
            coords[j][0] = buferArray[i];
        } else {
            coords[j][1] = buferArray[i];
            j++;
        }
    }
    return coords;
}
// Функция, принимающая id тега <canvas> и массив координат
function drawPoly(id, arr) {
    var canvas = document.getElementById(id).getContext('2d');
    // Начинаем отрисовку
    canvas.beginPath();
    for (var i = 0; i < arr.length; i++) {
        // Ставим точку на исходную позицию
        if (i == 0) canvas.moveTo(arr[i][0], arr[i][1]);
        // Рисуем линии от точки к точке
        else canvas.lineTo(arr[i][0], arr[i][1]);
    }
    // Задаем цвет заливки в формате RGBA
    canvas.fillStyle = "rgba(44,123,229,1.0)";
    // Зальем полученный многоугольник цветом
    canvas.fill();
}

// Очищаем область Canvas
function clearCanvas(id) {
    var canvas = document.getElementById(id)
    var width = parseInt(canvas.width);
    var height = parseInt(canvas.height);
    canvas = canvas.getContext('2d');
    canvas.clearRect(0, 0, width, height);
}

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
    // ���������� ��� ��������
    var ua = navigator.userAgent.toLowerCase();
    var isIE = (ua.indexOf("msie") != -1 && ua.indexOf("opera") == -1 && ua.indexOf("webtv") == -1); 
    var isOpera = ua.indexOf("opera") != -1; 
    var isFF = ua.indexOf("firefox") != -1;
    var isSafari = ua.indexOf("safari") != -1;
    var isChrome = ua.indexOf("chrome") != -1;
    
    var result = false;
    // �������� ��� ������ IE
    if (isIE) result = true;
    // ���� �������� ������ � ����������
    else if (isFF) {
        var ffVersion = parseFloat(ua.substring(ua.indexOf("firefox") + 8, ua.length));
        if (ffVersion >= 1.5) result = true;
    // �����, ������� � ������ 9.0
    } else if (isOpera) {
        var operaVersion = parseFloat(ua.substring(ua.indexOf("opera") + 6, ua.length));
        if (operaVersion >= 9.0) result = true;
    } else if (isSafari || isChrome) result = true;
    return result;
}

// ��� ���������
var coordsCashe = [];
// ������������ �������
function initImageMap() {
    if (!canvasBrowser()) return;
    var map = document.getElementById("maps");
    var area, i;
    for (i = 0; i < map.childNodes.length; i++) {
        area = map.childNodes[i];
        // ��������� ��� ����
        if (area.nodeType != 1) continue;
        // ���������, ��� ���� �������� ��������� area
        if (area.nodeName.toLowerCase() != "area") continue;
        // ��������� ID c ������ ������� ���������
        area.id = "id" + i;
        // ��������� � �������� ����������� �������
        area.onmouseover = mouseOverHandler;
        area.onmouseout = mouseOutHandler;
        // �������� ����������
        coordsCashe[i] = parseCoords(area.coords);
    }
}

// ���������� ������� mouseover
function mouseOverHandler() {
	zero();
	slideup();
    // �������� ������ ��� ������� ��������� �� ID
    var i = this.id.substring(2, this.id.length);
    // ������ ������������� �� ���������� �������
    drawPoly(
        "mapCanvas",
        coordsCashe[i]
    );
}
// ���������� ������� mouseout
function mouseOutHandler() {
    // ������� ������������ � canvas
    clearCanvas("mapCanvas");
}

// ������ ������ � ������������, �������������� ����� �������, � ��������� ������
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
// �������, ����������� id ���� <canvas> � ������ ���������
function drawPoly(id, arr) {
    var canvas = document.getElementById(id).getContext('2d');
    // �������� ���������
    canvas.beginPath();
    for (var i = 0; i < arr.length; i++) {
        // ������ ����� �� �������� �������
        if (i == 0) canvas.moveTo(arr[i][0], arr[i][1]);
        // ������ ����� �� ����� � �����
        else canvas.lineTo(arr[i][0], arr[i][1]);
    }
    // ������ ���� ������� � ������� RGBA
    canvas.fillStyle = "rgba(44,123,229,1.0)";
    // ������ ���������� ������������� ������
    canvas.fill();
}

// ������� ������� Canvas
function clearCanvas(id) {
    var canvas = document.getElementById(id)
    var width = parseInt(canvas.width);
    var height = parseInt(canvas.height);
    canvas = canvas.getContext('2d');
    canvas.clearRect(0, 0, width, height);
}

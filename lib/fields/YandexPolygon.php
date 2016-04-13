<?php

namespace marvin255\bxlib\fields;

/**
 * Пользовательское поле с яндекс картой, на которой можно выделить полигон
 */
class YandexPolygon extends \CUserTypeString
{
	// ---------------------------------------------------------------------
	// Общие параметры методов класса:
	// @param array $arUserField - метаданные (настройки) свойства
	// @param array $arHtmlControl - массив управления из формы (значения свойств, имена полей веб-форм и т.п.)
	// ---------------------------------------------------------------------

	// Функция регистрируется в качестве обработчика события OnUserTypeBuildList
	function GetUserTypeDescription()
	{
		return array(
			"PROPERTY_TYPE" => "S",
			"USER_TYPE" => "BxlibYandexPolygon",
			"DESCRIPTION" => "Выбрать область на яндекс карте",
			"GetPropertyFieldHtml" => ['\marvin255\bxlib\fields\YandexPolygon', "GetPropertyFieldHtml"],
			"GetPublicViewHTML" => ['\marvin255\bxlib\fields\YandexPolygon', "GetPublicViewHTML"],
		);
	}

	function GetPropertyFieldHtml($arProperty, $value, $strHTMLControlName)
	{
		return self::getList($strHTMLControlName['VALUE'], $value['VALUE'], $arProperty['MULTIPLE'] === 'Y');
	}

	function GetPublicViewHTML($arProperty, $value, $strHTMLControlName)
	{
		return '';
	}


    /**
     * @var int
     */
    protected static $counter = 0;

	/**
	 * Возвращает выпадающий список с часовыми поясами
	 * @param string $name
	 * @param string $value
	 * @param bool $is_multiple
	 */
	protected static function getList($name, $value, $is_multiple = false)
	{
        global $APPLICATION;
        $APPLICATION->AddHeadScript('//api-maps.yandex.ru/2.1/?load=package.full&lang=ru-RU');

        $id = md5('BxlibYandexPolygon' . $name . self::$counter);
        self::$counter++;
        $printVal = $value ? $value : '[]';

        $html = <<<EOD
    <div id="{$id}" style="width:100%; height:400px; margin-bottom: 10px;"></div>
    <input type="button" value="Начать редактирование" onclick="polygon_button_{$id}(this);">
    <input type="hidden" value="{$printVal}" id="value_{$id}" name="{$name}">
    <script type="text/javascript">
        var map_{$id};
        var polygon_{$id};
        var polygon_button_{$id} = function (button) {
            if (button.value == 'Начать редактирование') {
                polygon_{$id}.editor.startDrawing();
                button.value = 'Сохранить';
            } else {
                polygon_{$id}.editor.stopEditing();
                var coords = polygon_{$id}.geometry.getCoordinates();
                button.value = 'Начать редактирование';
                document.getElementById('value_{$id}').value = JSON.stringify(coords);
            }
        };

        ymaps.ready(function () {
            map_{$id} = new ymaps.Map("{$id}", {
                center: [55.76,37.64],
                zoom: 15,
                controls: ['geolocationControl', 'searchControl', 'zoomControl']
            });
            map_{$id}.behaviors.disable('scrollZoom');

            polygon_{$id} = new ymaps.GeoObject({
                geometry: {
                    type: "Polygon",
                    coordinates: {$printVal}
                }
            });

            map_{$id}.geoObjects.add(polygon_{$id});
            var bounds = polygon_{$id}.geometry.getBounds();
            if (bounds) {
                var x = (bounds[0][0] + bounds[1][0]) / 2;
                var y = (bounds[0][1] + bounds[1][1]) / 2;
                map_{$id}.setCenter([x, y], 15, {
                    checkZoomRange: true
                });
            }
        });
    </script>
EOD;
		return $html;
	}
}

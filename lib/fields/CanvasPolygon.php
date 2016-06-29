<?php

namespace marvin255\bxlib\fields;

/**
 * Пользовательское поле с яндекс картой, на которой можно выделить полигон
 */
class CanvasPolygon extends \CUserTypeString
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
			"USER_TYPE" => "BxlibCanvasPolygon",
			"DESCRIPTION" => "Выбрать область на изображении",
			"GetPropertyFieldHtml" => ['\marvin255\bxlib\fields\CanvasPolygon', "GetPropertyFieldHtml"],
			"GetPublicViewHTML" => ['\marvin255\bxlib\fields\CanvasPolygon', "GetPublicViewHTML"],
			"GetSettingsHTML" => ['\marvin255\bxlib\fields\CanvasPolygon', "GetSettingsHTML"],
			"PrepareSettings" => ['\marvin255\bxlib\fields\CanvasPolygon', "PrepareSettings"],
		);
	}

	function GetPropertyFieldHtml($arProperty, $value, $strHTMLControlName)
	{
		return self::getList($arProperty, $strHTMLControlName['VALUE'], $value['VALUE'], $arProperty['MULTIPLE'] === 'Y');
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
	protected static function getList($arProperty, $name, $value, $is_multiple = false)
	{
		$image = self::getImage($arProperty);
		if (!$image) return '';

		global $APPLICATION;
		if (self::$counter === 0) {
			$script = file_get_contents(__DIR__ . '/../assets/jquery.canvasAreaDraw.js');
			\CJSCore::Init(array("jquery"));
			$APPLICATION->AddHeadString("<script>{$script}</script>", true);
		}

		$id = 'canv_' . md5('CanvasPolygon' . $name . self::$counter);
		$html = <<<EOD
	<div style="max-width: 100%; max-height: 600px; overflow: scroll;">
		<input type="hidden" value="{$value}" id="{$id}" name="{$name}">
	</div>
	<button id="reset_{$id}">Очистить</button>
	<script type="text/javascript">
		(function($) {
			var \$input = $('#{$id}');
			var \$parent = \$input.parent();
			\$parent.width(\$parent.parent().width());
			\$input.canvasAreaDraw({
				imageUrl: "{$image}",
				reset: "#reset_{$id}"
			});
		})(jQuery);
	</script>
EOD;

		self::$counter++;
		return $html;
	}

	/**
	 * Возвращает путь до картинки
	 */
	function getImage($arProperty)
	{
		$current = self::getCurrent($arProperty);
		$image = null;
		$p = strtoupper($arProperty['USER_TYPE_SETTINGS']['PROPERTY_NAME']);
		$link = strtoupper($arProperty['USER_TYPE_SETTINGS']['PROPERTY_LINK']);
		if ($link && !empty($current[$link])) {
			//узнаем инфоблок привязанного свойства
			if ($link === 'IBLOCK_SECTION_ID') {
				$res = \CIBlockSection::GetById($current[$link]);
				if ($ob = $res->Fetch()) {
					$image = isset($ob[$p]) ? $ob[$p] : null;
				}
			} else {
				$res = \CIBlockElement::GetById($current[$link]);
				if ($ob = $res->Fetch()) {
					if (strpos($p, 'PROPERTY_') === 0) {
						$pres = \CIBlockElement::GetProperty(
							$ob['IBLOCK_ID'],
							$ob['ID'],
							[],
							['CODE' => substr($p, 9)]
						);
						if ($pob = $pres->Fetch()) $image = $pob['VALUE'];
					} else {
						$image = isset($ob[$p]) ? $ob[$p] : null;
					}
				}
			}
		} elseif (!empty($current[$p])) {
			$image = $current[$p] ;
		}
		if ($image && is_numeric($image)) {
			$image = \CFile::GetPath($image);
		}
		return $image;
	}

	/**
	 * Возвращает текущий элемент
	 * @return array
	 */
	protected function getCurrent($arProperty)
	{
		$return = null;
		$id = isset($_REQUEST['ID']) ? $_REQUEST['ID'] : null;
		if (!$id) return $return;
		//нужно вернуть то значение, которое нам требуется
		$select = ['ID'];
		if (!empty($arProperty['USER_TYPE_SETTINGS']['PROPERTY_LINK'])) {
			$select[] = $arProperty['USER_TYPE_SETTINGS']['PROPERTY_LINK'];
		} elseif (!empty($arProperty['USER_TYPE_SETTINGS']['PROPERTY_NAME'])) {
			$select[] = $arProperty['USER_TYPE_SETTINGS']['PROPERTY_NAME'];
		}
		//получаем текущий элемент
		$res = \CIBlockElement::GetList(
			[],
			['IBLOCK_ID' => $arProperty['IBLOCK_ID'], 'ID' => $id],
			false,
			false,
			$select
		);
		if ($ob = $res->Fetch()) {
			$return['id'] = $ob['ID'];
			if (!empty($arProperty['USER_TYPE_SETTINGS']['PROPERTY_LINK'])) {
				$name = strtoupper($arProperty['USER_TYPE_SETTINGS']['PROPERTY_LINK']);
			} elseif (!empty($arProperty['USER_TYPE_SETTINGS']['PROPERTY_NAME'])) {
				$name = strtoupper($arProperty['USER_TYPE_SETTINGS']['PROPERTY_NAME']);
			}
			$return[$name] = isset($ob[$name]) ? $ob[$name] : $ob[$name . '_VALUE'];
		}
		return $return;
	}

	/**
	 * Возвращает html для настроект поля
	 */
	function GetSettingsHTML($arProperty, $strHTMLControlName, &$arPropertyFields)
	{
		$return = '';
		$arPropertyFields = array(
			'HIDE' => ['DEFAULT_VALUE'],
			'USER_TYPE_SETTINGS_TITLE' => 'Настройки изображения для выбора полигона',
		);
		$return .= '<tr>';
		$return .= '<td>Свойство с картинкой:</td>';
		$value = isset($arProperty['USER_TYPE_SETTINGS']['PROPERTY_NAME'])
			? htmlspecialchars($arProperty['USER_TYPE_SETTINGS']['PROPERTY_NAME'])
			: '';
		$return .= '<td><input type="text" size="25" name="' . $strHTMLControlName["NAME"] . '[PROPERTY_NAME]" value="' . $value . '"></td>';
		$return .= '</tr>';
		$return .= '<tr>';
		$return .= '<td>Свойство для привязки:</td>';
		$value = isset($arProperty['USER_TYPE_SETTINGS']['PROPERTY_LINK'])
			? htmlspecialchars($arProperty['USER_TYPE_SETTINGS']['PROPERTY_LINK'])
			: '';
		$return .= '<td><input type="text" size="25" name="' . $strHTMLControlName["NAME"] . '[PROPERTY_LINK]" value="' . $value . '"></td>';
		$return .= '</tr>';
		return $return;
	}

	/**
	 * Возвращает пользовательские настройки поля
	 */
	function PrepareSettings($arFields)
	{
		return $arFields['USER_TYPE_SETTINGS'];
	}
}

<?php

namespace marvin255\bxlib\fields;

/**
 * Пользовательское поле с выпадающим списком с часовыми поясами
 */
class Timezone extends \CUserTypeString
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
			"USER_TYPE" => "BxlibTimezone",
			"DESCRIPTION" => "Список часовых поясов",
			"GetPropertyFieldHtml" => ['\marvin255\bxlib\fields\Timezone', "GetPropertyFieldHtml"],
			"GetPublicViewHTML" => ['\marvin255\bxlib\fields\Timezone', "GetPublicViewHTML"],
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
	 * Возвращает выпадающий список с часовыми поясами
	 * @param string $name
	 * @param string $value
	 * @param bool $is_multiple
	 */
	protected static function getList($name, $value, $is_multiple = false)
	{
		$list = self::getTimezones();
		$html = '<select name="' . strip_tags(htmlspecialchars(str_replace('"', '', $name))) . '"';
		if ($is_multiple) $html .= ' multiple="multiple"';
		$html .= '>';
		foreach ($list as $key => $name) {
			$sel = ($is_multiple && in_array($key, $value)) || (!$is_multiple && $key == $value);
			$html .= '<option value="' . $key . '"' . ($sel ? ' selected="selected"' : '') . '>' . $name . '</option>';
		}
		$html .= '<select>';
		return $html;
	}


	/**
	 * @var array
	 */
	protected static $_timezones = null;

	/**
	 * Возвращает список часовых поясов
	 * @return array
	 */
	protected static function getTimezones()
	{
		if (self::$_timezones === null) {
			$timeZonesList = \CTimeZone::GetZones();
			unset($timeZonesList['']);
			self::$_timezones = $timeZonesList;
		}
		return self::$_timezones;
	}
}

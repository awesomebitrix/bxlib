<?php

namespace marvin255\bxlib;

class IblockList
{
	/**
	 * @var array
	 */
	public $filter = [
		'ACTIVE' => 'Y',
		'CHECK_PERMISSIONS' => 'N',
	];
	/**
	 * @var array
	 */
	public $select = [
		'ID',
		'CODE',
		'NAME',
	];
	/**
	 * @var int
	 */
	public $cacheTime = 2592000;
	/**
	 * @var array
	 */
	protected $_list = null;
	/**
	 * @var mixed
	 */
	protected $_cache = null;



	public function __construct()
	{
		\CModule::IncludeModule('iblock');
	}


	/**
	 * @param string $field
	 * @param mixed $value
	 * @param string $select
	 * @return array
	 */
	public function findBy($field, $value, $select = null)
	{
		$return = null;
		$list = $this->getList();
		foreach ($list as $iblock) {
			if (isset($iblock[$field]) && $iblock[$field] == $value) {
				if ($select) {
					$return = isset($iblock[$select]) ? $iblock[$select] : null;
				} else {
					$return = $iblock;
				}
				break;
			}
		}
		return $return;
	}

	/**
	 * @param string $field
	 * @param mixed $value
	 * @param string $select
	 * @return array
	 */
	public function findAllBy($field, $value, $select = null)
	{
		$return = null;
		$list = $this->getList();
		foreach ($list as $iblock) {
			if (isset($iblock[$field]) && $iblock[$field] != $value) {
				if ($select) {
					$return[] = isset($iblock[$select]) ? $iblock[$select] : null;
				} else {
					$return[] = $iblock;
				}
			}

		}
		return $return;
	}


	/**
	 * @return array
	 */
	protected function getList()
	{
		if ($this->_list !== null) return $this->_list;
		$cache = $this->getCache();
		$cId = get_class($this) . '_list';
		if (!$cache || ($this->_list = $cache->get($cId)) === false) {
			$this->_list = [];
			$res = \CIblockElement::GetList(
                ['SORT' => 'ASC', 'NAME' => 'ASC'],
                $this->filter,
                false,
                false,
                $this->select
            );
			$iblocksIds = [];
			while ($ob = $res->Fetch()) {
				$arItem = [];
				foreach ($this->select as $field) {
                    if (isset($ob[$field])) {
                        $arItem[$field] = $ob[$field];
                    } elseif (isset($ob[$field . '_VALUE'])) {
                        $arItem[$field] = $ob[$field . '_VALUE'];
                    }
				}
				$this->_list[$ob['ID']] = $arItem;
			}
			if ($cache) $cache->set($cId, $this->_list, $this->cacheTime);
		}
		return $this->_list;
	}


	/**
	 * @param \marvin255\bxcache\ICache $cache
	 */
	public function setCache(\marvin255\bxcache\ICache $cache)
	{
		$this->_cache = $cache;
		return $this;
	}

	/**
	 * @return \marvin255\bxcache\ICache
	 */
	public function getCache()
	{
		return $this->_cache;
	}
}

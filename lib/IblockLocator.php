<?php

namespace marvin255\bxlib;

class IblockLocator
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
		'PROPERTIES',
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
	 * @param string $code
	 * @return string
	 */
	public function getIdByCode($code)
	{
		return $this->findBy('CODE', $code, 'ID');
	}

	/**
	 * @param string $id
	 * @return string
	 */
	public function getCodeById($id)
	{
		return $this->findBy('ID', $id, 'CODE');
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
	 * @param int $iblockId
	 * @return array
	 */
	public function getIblockFields($iblockId)
	{
		$return = [];
		$cache = $this->getCache();
		$cId = get_class($this) . '_fields';
		if (!$cache || ($return = $cache->get($cId)) === false) {
			$return = [];
			$list = $this->getList();
			foreach ($list as $ib) {
				if (empty($ib['ID'])) continue;
				$return[$ib['ID']] = \CIBlock::getFields($ib['ID']);
			}
			if ($cache) $cache->set($cId, $return, $this->cacheTime);
		}
		return isset($return[$iblockId]) ? $return[$iblockId] : [];
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
			$res = \CIblock::GetList([], $this->filter);
			$iblocksIds = [];
			while ($ob = $res->Fetch()) {
				$arItem = [];
				foreach ($this->select as $field) {
					if (!isset($ob[$field])) continue;
					$arItem[$field] = $ob[$field];
				}
				$this->_list[$ob['ID']] = $arItem;
			}
			if (in_array('PROPERTIES', $this->select) && !empty($this->_list)) {
				$pRes = \CIBlockProperty::GetList([], []);
				while ($pOb = $pRes->Fetch()) {
					if (!isset($this->_list[$pOb['IBLOCK_ID']])) continue;
					$this->_list[$pOb['IBLOCK_ID']]['PROPERTIES'][] = $pOb;
				}
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
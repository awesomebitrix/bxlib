<?php

namespace marvin255\bxlib;

class Request
{
    /**
	 * @var \Symfony\Component\HttpFoundation\Request
	 */
	protected $_request = null;


	/**
	 * Магия. Пробуем вызвать метод сначала с битриксового объекта, а потом со встроенного
	 * @param string $name
	 * @param array $arguments
	 * @return mixed
	 */
	public function __call($name, $arguments)
	{
		$request = $this->getRequest();
		if (method_exists($request, $name)) {
			return call_user_func_array([$request, $name], $arguments);
		} else {
			return null;
		}
	}


	/**
	 * Является ли запрос аяксовым
	 * @return bool
	 */
	public function isAjax()
	{
		$request = $this->getRequest();
		$types = $this->getAcceptableContentTypes();
		return $request->isXmlHttpRequest() || $this->isJsonRequest() || $types[0] === 'application/json';
	}


	/**
	 * Если данные в запросе переданы в json
	 * @return bool
	 */
	public function isJsonRequest()
	{
		return strpos($this->getRequest()->server->get('CONTENT_TYPE'), 'application/json') !== false;
	}

	/**
	 * Возвращает данные запроса, пришедшие в json
	 * @return array
	 */
	public function getJsonRequestParams()
	{
		return $this->isJsonRequest() ? json_decode($this->getContent(), true) : null;
	}


	/**
	 * Возвращает объект запроса
	 * @return \Symfony\Component\HttpFoundation\Request
	 */
	public function getRequest()
	{
		if ($this->_request === null) {
			$this->_request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
		}
		return $this->_request;
	}

	/**
	 * Возвращает ip адрес клиента
	 * @return string
	 */
	public function getClientIp()
	{
		$ip = $this->getRequest()->getClientIp();
		if ($ip == '127.0.0.1' && !empty($_SERVER['HTTP_X_REAL_IP'])) {
			$ip = $_SERVER['HTTP_X_REAL_IP'];
		}
		return $ip;
	}
}

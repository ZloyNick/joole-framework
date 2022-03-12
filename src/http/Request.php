<?php

declare(strict_types=1);

namespace joole\framework\http;

use ArrayAccess;

class Request extends BaseRequest implements ArrayAccess
{

    /** @var array GET params */
    private array $_get;
    private array $_post;

    public function __construct()
    {
        $this->_get = $_GET;
        $this->_post = $_POST;

        parent::__construct();
    }

    public function offsetExists(mixed $offset):bool
    {
        return isset($this->_post[$offset]) || isset($this->_get[$offset]);
    }

    public function offsetGet(mixed $offset):mixed
    {
        return ($this->_get[$offset] ?? $this->_post[$offset]) ?? null;
    }

    public function offsetSet(mixed $offset, $value):void
    {
        if(isset($this->_get[$offset])){
            $this->_get[$offset] = $value;
        }elseif (isset($this->_post[$offset])){
            $this->_post[$offset] = $value;
        }else{
            $this->{$offset} = $value;
        }
    }

    public function offsetUnset(mixed $offset):void
    {
        if(isset($this->_get[$offset])){
            unset($this->_get[$offset]);
        }elseif (isset($this->_post[$offset])){
            unset($this->_post[$offset]);
        }else{
            unset($this->{$offset});
        }
    }

    /**
     * Returns array of GET and POST params.
     *
     * @return array
     */
    public function all():array{
        return array_merge_recursive($this->_get, $this->_post);
    }

    /**
     * Returns POST param(s).
     *
     * If the parameter does not exist, the second parameter passed to this method will be returned.
     * If no params given, all POST params will be returned.
     *
     * @param string|null $param Param name.
     * @param mixed|null $default If param not found, it will be returned.
     * @return mixed <ul>
     * <ul>
     *      <li> mixed - if $param not empty;</li>
     *      </li> array - all POST params;</li>
     * </ul>
     */
    public function post(?string $param = null, mixed $default = null):mixed{
        if($param){
            return $this->_post[$param] ?? $default;
        }

        return $this->_post;
    }

    /**
     * Returns GET param(s).
     *
     * If the parameter does not exist, the second parameter passed to this method will be returned.
     * If no params given, all GET params will be returned.
     *
     * @param string|null $param Param name.
     * @param mixed|null $default If param not found, it will be returned.
     * @return mixed <ul>
     * <ul>
     *      <li> mixed - if $param not empty;</li>
     *      </li> array - all GET params;</li>
     * </ul>
     */
    public function get(?string $param = null, mixed $default = null):mixed{
        if($param){
            return $this->_get[$param] ?? $default;
        }

        return $this->_get;
    }
}
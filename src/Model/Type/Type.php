<?php
/**
 * Created by PhpStorm.
 * User: karavaev
 * Date: 15.11.17
 * Time: 5:24 PM
 */

namespace BSUIRBot\Model\Type;

use BSUIRBot\Exception\InvalidInputException;

class Type {
    /** @var int $update_id */
    public $update_id;
    /** @var string $api */
    public $api = 'telegram';
    /** @var string $object_type */
    protected $object_type;
    /** @var array $safe_types */
    private $safe_types = ['int', 'integer', 'string', 'boolean', 'stdClass', 'bool'];

    protected $message;

    public function __construct($input, $api = 'telegram')
    {
        $this->api = $api;

        if (!empty($input->update_id)) {
            $this->update_id = $input->update_id;
            unset($input->update_id);
            $key = key($input);
            $this->object_type = $key;
            $this->$key = $this->initAdditionalClass($this->convertObjectTypeToClassName($key), $input->$key);
        } else {
            if (!$this->validateInput($input))
                throw new InvalidInputException('Invalid input, sorry.');

            $this->load($input);
        }

        return true;
    }

    public function convertObjectTypeToClassName($type) {
        return str_replace('_', '', ucwords($type,'_'));
    }

    public function validateInput($input):bool
    {
        $attributes = $this->attributes();
        if (is_array($input)) {
            $key = key($input[0]);
        } else
            $key = key($input);

        return (array_key_exists($key, $attributes));
    }

    public function attributes():array
    {
        $class = new \ReflectionClass($this);
        $attributes = [];
        foreach ($class->getProperties(\ReflectionProperty::IS_PROTECTED) as $property) {
            $type = null;
            if (!$property->isStatic()) {
                $type = $this->getPropertyInfo($property);
                $name = $property->getName();
                $attributes[$name] = $type;
            }
        }
        return $attributes;
    }

    public function getPropertyInfo(\ReflectionProperty $property):array
    {
        $doc = substr($property->getDocComment(), 3, -2);
        preg_match('/@var[\s]+(?<type>[a-zA-Z]+)(?<is_array>\[?\]?)[\s]+\$\D+$/i', $doc, $matches);
        $result = [
            'typeOf' => trim($matches['type']),
            'is_array' => ($matches['is_array']) ? true : false,
            'is_safe' => (in_array($matches['type'], $this->safe_types))
        ];

        return $result;
    }

    public function getAttributeValue($name) {
        if (property_exists($this, $name))
            return $this->$name;
        else return null;
    }

    public function load($values)
    {
        $attributes = $this->attributes();
        foreach ($values as $name => $value) {
            if (isset($attributes[$name])) {
                if ( ! $attributes[$name]['is_safe']) {
                    $this->$name = $this->initAdditionalClass($attributes[$name]['typeOf'], $value);
                } else {
                    $this->$name = $value;
                }
            }
        }

        return null;
    }

    public function initAdditionalClass($type, $value) {
        try {
            $class = new \ReflectionClass(get_parent_class($this));
        }
        catch (\ReflectionException $e) {
            $class = new \ReflectionClass($this);
        }
        $namespace = $class->getNamespaceName();
        $className = $namespace  . "\\" . $type . "\\" . ucfirst($this->api) . $type;
        if (is_array($value)) {
            $object = [];
            foreach ($value as $key => $val) {
                $instance = new $className($val);
                $instance->load($val);
                $object[] = $instance;
            }
        } else {
            $object = new $className($value);
            $object->load($value);
        }

        return $object;
    }

    /**
     * @return int
     */
    public function getUpdateId(): int
    {
        return $this->update_id;
    }

    /**
     * @return string
     */
    public function getApi(): string
    {
        return $this->api;
    }

    /**
     * @return string
     */
    public function getObjectType(): string
    {
        return $this->object_type;
    }


}
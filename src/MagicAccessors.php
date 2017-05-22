<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 * Copyright (c) 2008 Filip Procházka (filip@prochazka.su)
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Doctrine\MagicAccessors;

use Doctrine\Common\Collections\Collection;
use Kdyby\Doctrine\Collections\Readonly\ReadOnlyCollectionWrapper;
use Nette\ObjectMixin;
use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;
use Traversable;

trait MagicAccessors
{

	/**
	 * @var array
	 */
	private static $__properties = [];

	/**
	 * @var array
	 */
	private static $__methods = [];

	/**
	 * @param string $property property name
	 * @return \Doctrine\Common\Collections\Collection|array
	 */
	protected function convertCollection($property)
	{
		return new ReadOnlyCollectionWrapper($this->$property);
	}

	/**
	 * Utility method, that can be replaced with `::class` since php 5.5
	 *
	 * @deprecated
	 * @return string
	 */
	public static function getClassName()
	{
		return get_called_class();
	}

	/**
	 * Access to reflection.
	 *
	 * @return \ReflectionClass
	 */
	public static function getReflection()
	{
		return new ReflectionClass(get_called_class());
	}

	/**
	 * Allows the user to access through magic methods to protected and public properties.
	 * There are get<name>() and set<name>($value) methods for every protected or public property,
	 * and for protected or public collections there are add<name>($entity), remove<name>($entity) and has<name>($entity).
	 * When you'll try to call setter on collection, or collection manipulator on generic value, it will throw.
	 * Getters on collections will return all it's items.
	 *
	 * @param string $name method name
	 * @param array $args arguments
	 * @throws \Kdyby\Doctrine\MagicAccessors\UnexpectedValueException
	 * @throws \Kdyby\Doctrine\MagicAccessors\MemberAccessException
	 * @return mixed
	 */
	public function __call($name, $args)
	{
		if (strlen($name) > 3) {
			$properties = $this->listObjectProperties();

			$op = substr($name, 0, 3);
			$prop = strtolower($name[3]) . substr($name, 4);
			if ($op === 'set' && isset($properties[$prop])) {
				if ($this->$prop instanceof Collection) {
					throw \Kdyby\Doctrine\MagicAccessors\UnexpectedValueException::collectionCannotBeReplaced($this, $prop);
				}

				$this->$prop = $args[0];

				return $this;

			} elseif ($op === 'get' && isset($properties[$prop])) {
				if ($this->$prop instanceof Collection) {
					return $this->convertCollection($prop);

				} else {
					return $this->$prop;
				}

			} else { // collections
				if ($op === 'add') {
					if (isset($properties[$prop . 's'])) {
						if (!$this->{$prop . 's'} instanceof Collection) {
							throw \Kdyby\Doctrine\MagicAccessors\UnexpectedValueException::notACollection($this, $prop . 's');
						}

						$this->{$prop . 's'}->add($args[0]);

						return $this;

					} elseif (substr($prop, -1) === 'y' && isset($properties[$prop = substr($prop, 0, -1) . 'ies'])) {
						if (!$this->$prop instanceof Collection) {
							throw \Kdyby\Doctrine\MagicAccessors\UnexpectedValueException::notACollection($this, $prop);
						}

						$this->$prop->add($args[0]);

						return $this;

					} elseif (substr($prop, -1) === 's' && isset($properties[$prop = substr($prop, 0, -1) . 'ses'])) {
						if (!$this->$prop instanceof Collection) {
							throw \Kdyby\Doctrine\MagicAccessors\UnexpectedValueException::notACollection($this, $prop);
						}

						$this->$prop->add($args[0]);

						return $this;

					} elseif (isset($properties[$prop])) {
						throw \Kdyby\Doctrine\MagicAccessors\UnexpectedValueException::notACollection($this, $prop);
					}

				} elseif ($op === 'has') {
					if (isset($properties[$prop . 's'])) {
						if (!$this->{$prop . 's'} instanceof Collection) {
							throw \Kdyby\Doctrine\MagicAccessors\UnexpectedValueException::notACollection($this, $prop . 's');
						}

						return $this->{$prop . 's'}->contains($args[0]);

					} elseif (substr($prop, -1) === 'y' && isset($properties[$prop = substr($prop, 0, -1) . 'ies'])) {
						if (!$this->$prop instanceof Collection) {
							throw \Kdyby\Doctrine\MagicAccessors\UnexpectedValueException::notACollection($this, $prop);
						}

						return $this->$prop->contains($args[0]);

					} elseif (substr($prop, -1) === 's' && isset($properties[$prop = substr($prop, 0, -1) . 'ses'])) {
						if (!$this->$prop instanceof Collection) {
							throw \Kdyby\Doctrine\MagicAccessors\UnexpectedValueException::notACollection($this, $prop);
						}

						return $this->$prop->contains($args[0]);

					} elseif (isset($properties[$prop])) {
						throw \Kdyby\Doctrine\MagicAccessors\UnexpectedValueException::notACollection($this, $prop);
					}

				} elseif (strlen($name) > 6 && ($op = substr($name, 0, 6)) === 'remove') {
					$prop = strtolower($name[6]) . substr($name, 7);

					if (isset($properties[$prop . 's'])) {
						if (!$this->{$prop . 's'} instanceof Collection) {
							throw \Kdyby\Doctrine\MagicAccessors\UnexpectedValueException::notACollection($this, $prop . 's');
						}

						$this->{$prop . 's'}->removeElement($args[0]);

						return $this;

					} elseif (substr($prop, -1) === 'y' && isset($properties[$prop = substr($prop, 0, -1) . 'ies'])) {
						if (!$this->$prop instanceof Collection) {
							throw \Kdyby\Doctrine\MagicAccessors\UnexpectedValueException::notACollection($this, $prop);
						}

						$this->$prop->removeElement($args[0]);

						return $this;

					} elseif (substr($prop, -1) === 's' && isset($properties[$prop = substr($prop, 0, -1) . 'ses'])) {
						if (!$this->$prop instanceof Collection) {
							throw \Kdyby\Doctrine\MagicAccessors\UnexpectedValueException::notACollection($this, $prop);
						}

						$this->$prop->removeElement($args[0]);

						return $this;

					} elseif (isset($properties[$prop])) {
						throw \Kdyby\Doctrine\MagicAccessors\UnexpectedValueException::notACollection($this, $prop);
					}
				}
			}
		}

		if ($name === '') {
			throw \Kdyby\Doctrine\MagicAccessors\MemberAccessException::callWithoutName($this);
		}
		$class = get_class($this);

		// event functionality
		if (preg_match('#^on[A-Z]#', $name) && property_exists($class, $name)) {
			$rp = new ReflectionProperty($this, $name);
			if ($rp->isPublic() && !$rp->isStatic()) {
				$list = $this->$name;
				if (is_array($list) || $list instanceof Traversable) {
					foreach ($list as $handler) {
						call_user_func_array($handler, $args);
					}
				} elseif ($list !== NULL) {
					throw \Kdyby\Doctrine\MagicAccessors\UnexpectedValueException::invalidEventValue($list, $this, $name);
				}

				return NULL;
			}
		}

		// extension methods
		$cb = static::extensionMethod($name);
		if ($cb !== NULL) {
			array_unshift($args, $this);
			return call_user_func_array($cb, $args);
		}

		throw \Kdyby\Doctrine\MagicAccessors\MemberAccessException::undefinedMethodCall($this, $name);
	}

	/**
	 * Call to undefined static method.
	 *
	 * @param string $name method name (in lower case!)
	 * @param array $args arguments
	 * @return mixed
	 * @throws \Kdyby\Doctrine\MagicAccessors\MemberAccessException
	 */
	public static function __callStatic($name, $args)
	{
		throw \Kdyby\Doctrine\MagicAccessors\MemberAccessException::undefinedStaticMethodCall(get_called_class(), $name);
	}

	/**
	 * Adding method to class.
	 *
	 * @param string $name method name
	 * @param callable $callback
	 * @return mixed
	 */
	public static function extensionMethod($name, $callback = NULL)
	{
		if (!class_exists(ObjectMixin::class)) {
			return NULL;
		}

		if (strpos($name, '::') === FALSE) {
			$class = get_called_class();
		} else {
			list($class, $name) = explode('::', $name);
			$class = (new ReflectionClass($class))->getName();
		}

		if ($callback === NULL) {
			return ObjectMixin::getExtensionMethod($class, $name);
		} else {
			ObjectMixin::setExtensionMethod($class, $name, $callback);
		}

		return NULL;
	}

	/**
	 * Returns property value. Do not call directly.
	 *
	 * @param string $name property name
	 *
	 * @throws \Kdyby\Doctrine\MagicAccessors\MemberAccessException if the property is not defined.
	 * @return mixed property value
	 */
	public function &__get($name)
	{
		if ($name === '') {
			throw \Kdyby\Doctrine\MagicAccessors\MemberAccessException::propertyReadWithoutName($this);
		}

		// property getter support
		$originalName = $name;
		$name[0] = $name[0] & "\xDF"; // case-sensitive checking, capitalize first character
		$m = 'get' . $name;

		$methods = $this->listObjectMethods();
		if (isset($methods[$m])) {
			// ampersands:
			// - uses &__get() because declaration should be forward compatible
			// - doesn't call &$_this->$m because user could bypass property setter by: $x = & $obj->property; $x = 'new value';
			$val = $this->$m();

			return $val;
		}

		$m = 'is' . $name;
		if (isset($methods[$m])) {
			$val = $this->$m();

			return $val;
		}

		// protected attribute support
		$properties = $this->listObjectProperties();
		$name = $originalName;
		if (isset($properties[$name])) {
			if ($this->$name instanceof Collection) {
				$coll = $this->convertCollection($name);

				return $coll;

			} else {
				$val = $this->$name;

				return $val;
			}
		}

		$type = isset($methods['set' . $name]) ? 'a write-only' : 'an undeclared';
		throw \Kdyby\Doctrine\MagicAccessors\MemberAccessException::propertyNotReadable($type, $this, $originalName);
	}

	/**
	 * Sets value of a property. Do not call directly.
	 *
	 * @param string $name property name
	 * @param mixed $value property value
	 *
	 * @throws \Kdyby\Doctrine\MagicAccessors\UnexpectedValueException
	 * @throws \Kdyby\Doctrine\MagicAccessors\MemberAccessException if the property is not defined or is read-only
	 */
	public function __set($name, $value)
	{
		if ($name === '') {
			throw \Kdyby\Doctrine\MagicAccessors\MemberAccessException::propertyWriteWithoutName($this);
		}

		// property setter support
		$originalName = $name;
		$name[0] = $name[0] & "\xDF"; // case-sensitive checking, capitalize first character

		$methods = $this->listObjectMethods();
		$m = 'set' . $name;
		if (isset($methods[$m])) {
			$this->$m($value);

			return;
		}

		// protected attribute support
		$properties = $this->listObjectProperties();
		$name = $originalName;
		if (isset($properties[$name])) {
			if ($this->$name instanceof Collection) {
				throw \Kdyby\Doctrine\MagicAccessors\UnexpectedValueException::collectionCannotBeReplaced($this, $name);
			}

			$this->$name = $value;

			return;
		}

		$type = isset($methods['get' . $name]) || isset($methods['is' . $name]) ? 'a read-only' : 'an undeclared';
		throw \Kdyby\Doctrine\MagicAccessors\MemberAccessException::propertyNotWritable($type, $this, $originalName);
	}

	/**
	 * Is property defined?
	 *
	 * @param string $name property name
	 *
	 * @return bool
	 */
	public function __isset($name)
	{
		$properties = $this->listObjectProperties();
		if (isset($properties[$name])) {
			return TRUE;
		}

		if ($name === '') {
			return FALSE;
		}

		$methods = $this->listObjectMethods();
		$name[0] = $name[0] & "\xDF";

		return isset($methods['get' . $name]) || isset($methods['is' . $name]);
	}

	/**
	 * Access to undeclared property.
	 *
	 * @param string $name property name
	 * @return void
	 * @throws \Kdyby\Doctrine\MagicAccessors\MemberAccessException
	 */
	public function __unset($name)
	{
		throw \Kdyby\Doctrine\MagicAccessors\MemberAccessException::cannotUnset($this, $name);
	}

	/**
	 * Should return only public or protected properties of class
	 *
	 * @return array
	 */
	private function listObjectProperties()
	{
		$class = get_class($this);
		if (!isset(self::$__properties[$class])) {
			$refl = new ReflectionClass($class);
			$properties = array_map(function (ReflectionProperty $property) {
				return $property->getName();
			}, $refl->getProperties(ReflectionProperty::IS_PUBLIC | ReflectionProperty::IS_PROTECTED));

			self::$__properties[$class] = array_flip($properties);
		}

		return self::$__properties[$class];
	}

	/**
	 * Should return all public methods of class
	 *
	 * @return array
	 */
	private function listObjectMethods()
	{
		$class = get_class($this);
		if (!isset(self::$__methods[$class])) {
			$refl = new ReflectionClass($class);
			$methods = array_map(function (ReflectionMethod $method) {
				return $method->getName();
			}, $refl->getMethods(ReflectionMethod::IS_PUBLIC));

			self::$__methods[$class] = array_flip($methods);
		}

		return self::$__methods[$class];
	}

}

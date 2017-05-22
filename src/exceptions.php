<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Doctrine\MagicAccessors;

use Doctrine\Common\Collections\Collection;

interface Exception
{

}

class UnexpectedValueException extends \UnexpectedValueException implements \Kdyby\Doctrine\MagicAccessors\Exception
{

	/**
	 * @param mixed $list
	 * @param string|object $class
	 * @param string $property
	 * @return \Kdyby\Doctrine\MagicAccessors\UnexpectedValueException
	 */
	public static function invalidEventValue($list, $class, $property)
	{
		$class = is_object($class) ? get_class($class) : $class;
		return new static(sprintf('Property %s::$%s must be array or NULL, %s given.', $class, $property, gettype($list)));
	}

	/**
	 * @param string|object $class
	 * @param string $property
	 * @return \Kdyby\Doctrine\MagicAccessors\UnexpectedValueException
	 */
	public static function notACollection($class, $property)
	{
		$class = is_object($class) ? get_class($class) : $class;
		return new static(sprintf('Class property %s::$%s is not an instance of %s.', $class, $property, Collection::class));
	}

	/**
	 * @param string|object $class
	 * @param string $property
	 * @return \Kdyby\Doctrine\MagicAccessors\UnexpectedValueException
	 */
	public static function collectionCannotBeReplaced($class, $property)
	{
		$class = is_object($class) ? get_class($class) : $class;
		return new static(sprintf(
			'Class property %s::$%s is an instance of %s. Use add<property>() and remove<property>() methods to manipulate it or declare your own.',
			$class,
			$property,
			Collection::class
		));
	}

}

class MemberAccessException extends \LogicException implements \Kdyby\Doctrine\MagicAccessors\Exception
{

	/**
	 * @param string $type
	 * @param string|object $class
	 * @param string $property
	 * @return \Kdyby\Doctrine\MagicAccessors\MemberAccessException
	 */
	public static function propertyNotWritable($type, $class, $property)
	{
		$class = is_object($class) ? get_class($class) : $class;
		return new static(sprintf('Cannot write to %s property %s::$%s.', $type, $class, $property));
	}

	/**
	 * @param string|object $class
	 * @return \Kdyby\Doctrine\MagicAccessors\MemberAccessException
	 */
	public static function propertyWriteWithoutName($class)
	{
		$class = is_object($class) ? get_class($class) : $class;
		return new static(sprintf('Cannot write to a class %s property without name.', $class));
	}

	/**
	 * @param string $type
	 * @param string|object $class
	 * @param string $property
	 * @return \Kdyby\Doctrine\MagicAccessors\MemberAccessException
	 */
	public static function propertyNotReadable($type, $class, $property)
	{
		$class = is_object($class) ? get_class($class) : $class;
		return new static(sprintf('Cannot read %s property %s::$%s.', $type, $class, $property));
	}

	/**
	 * @param string|object $class
	 * @return \Kdyby\Doctrine\MagicAccessors\MemberAccessException
	 */
	public static function propertyReadWithoutName($class)
	{
		$class = is_object($class) ? get_class($class) : $class;
		return new static(sprintf('Cannot read a class %s property without name.', $class));
	}

	/**
	 * @param string|object $class
	 * @return \Kdyby\Doctrine\MagicAccessors\MemberAccessException
	 */
	public static function callWithoutName($class)
	{
		$class = is_object($class) ? get_class($class) : $class;
		return new static(sprintf('Call to class %s method without name.', $class));
	}

	/**
	 * @param object|string $class
	 * @param string $method
	 * @return \Kdyby\Doctrine\MagicAccessors\MemberAccessException
	 */
	public static function undefinedMethodCall($class, $method)
	{
		$class = is_object($class) ? get_class($class) : $class;
		return new static(sprintf('Call to undefined method %s::%s().', $class, $method));
	}

	/**
	 * @param string $class
	 * @param string $method
	 * @return \Kdyby\Doctrine\MagicAccessors\MemberAccessException
	 */
	public static function undefinedStaticMethodCall($class, $method)
	{
		return new static(sprintf('Call to undefined static method %s::%s().', $class, $method));
	}

	/**
	 * @param object|string $class
	 * @param string $property
	 * @return \Kdyby\Doctrine\MagicAccessors\MemberAccessException
	 */
	public static function cannotUnset($class, $property)
	{
		$class = is_object($class) ? get_class($class) : $class;
		return new static(sprintf('Cannot unset the property %s::$%s.', $class, $property));
	}

}

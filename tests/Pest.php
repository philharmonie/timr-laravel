<?php

declare(strict_types=1);

uses(
    Tests\TestCase::class,
)->in('Unit');

function setPrivateProperty(object $object, string $property, mixed $value): void
{
    $reflection = new ReflectionClass($object);
    $property = $reflection->getProperty($property);
    $property->setAccessible(true);
    $property->setValue($object, $value);
}

function getPrivateProperty(object $object, string $property): mixed
{
    $reflection = new ReflectionClass($object);
    $property = $reflection->getProperty($property);
    $property->setAccessible(true);

    return $property->getValue($object);
}

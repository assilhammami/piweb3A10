<?php
namespace App\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class FrenchToDateTimeTransformer implements DataTransformerInterface
{
    public function transform($dateObject)
{
    if ($dateObject === null || !($dateObject instanceof \DateTimeInterface)) {
        return '';
    }

    return $dateObject->format('d/m/Y');
}

public function reverseTransform($frenchDate)
{
    if ($frenchDate === null || $frenchDate === '') {
        return (new \DateTime())->format('d/m/Y');
    }

    $date = \DateTime::createFromFormat('m/d/Y', $frenchDate);

    if ($date === false) {
        throw new TransformationFailedException(sprintf('Invalid date format "%s".', $frenchDate));
    }

    return $date->format('d/m/Y');
}}
?>
<?php
namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class CustomTwigExtension extends AbstractExtension
{
    public function getFunctions()
    {
        return [
            new TwigFunction('strtotime', [$this, 'strtotimeFunction']),
        ];
    }

    public function strtotimeFunction($time)
    {
        return strtotime($time);
    }
}

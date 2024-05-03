<?php
namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class DateModifyExtension extends AbstractExtension
{
    public function getFunctions()
    {
        return [
            new TwigFunction('date_modify', [$this, 'dateModify']),
        ];
    }

    public function dateModify(\DateTime $date, $modify)
    {
        $date->modify($modify);
        return $date;
    }
}

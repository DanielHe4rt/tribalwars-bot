<?php
/**
 * Created by PhpStorm.
 * User: felipesantos
 * Date: 28/01/19
 * Time: 15:24
 */

namespace App\Enums;

abstract class Choices extends Enum
{
    const BUILD = 'Build';
    const RECRUIT = 'Recruit';
    const FORGE = 'Forge';
    const REFRESH = 'Refresh';
    const EXIT = 'Exit';
}

<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 05.01.17
 * Time: 13:58
 */

namespace zaboy\test\installer;

use zaboy\installer\Command;

class CommandTest extends \PHPUnit_Framework_TestCase
{

    public function testPublicDir()
    {
        $expectedPublicDir = realpath('public');
        $publicDir = Command::getPublicDir();
        $this->assertEquals($expectedPublicDir, $publicDir);
    }
}

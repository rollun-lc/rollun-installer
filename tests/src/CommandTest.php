<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 05.01.17
 * Time: 13:58
 */

namespace rolluncom\test\installer;

use rolluncom\installer\Command;

class CommandTest extends \PHPUnit_Framework_TestCase
{

    public function testPublicDir()
    {
        $expectedPublicDir = realpath('public');
        $publicDir = Command::getPublicDir();
        $this->assertEquals($expectedPublicDir, $publicDir);
    }
}

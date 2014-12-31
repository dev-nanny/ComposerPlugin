<?php

namespace DevNanny\Composer\Plugin;

use Composer\Script\CommandEvent;
use DevNanny\Connector\BaseTestCase;

/**
 * @coversDefaultClass DevNanny\Composer\Plugin\GitHookInstaller
 * @covers ::<!public>
 */
class GitHookInstallerTest extends BaseTestCase
{
    ////////////////////////////////// FIXTURES \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
    /** @var GitHookInstaller */
    private $installer;

    protected function setUp()
    {
        $this->installer = new GitHookInstaller();
    }
    /////////////////////////////////// TESTS \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
        /**
         * @covers ::install
         */
        final public function testInstallerShouldReceiveCommandEventWhenAskedToInstall()
        {
            $installer = $this->installer;

            $this->setExpectedExceptionRegExp(
                \PHPUnit_Framework_Error::class,
                $this->regexMustBeAnInstanceOf('install', CommandEvent::class)
            );

            /** @noinspection PhpParamsInspection */
            $installer->install();
        }

        /**
         * @covers ::install
         */
        final public function testInstallerShouldWhen()
        {
            $installer = $this->installer;

            $mockEvent = $this->getMockBuilder(CommandEvent::class)
                ->disableOriginalConstructor()
                ->getMock()
            ;
            $installer->install($mockEvent);
        }
    ////////////////////////////// MOCKS AND STUBS \\\\\\\\\\\\\\\\\\\\\\\\\\\\\
    /////////////////////////////// DATAPROVIDERS \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
}

/*EOF*/

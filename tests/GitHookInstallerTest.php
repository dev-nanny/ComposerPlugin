<?php

namespace DevNanny\Composer\Plugin;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Script\CommandEvent;
use Composer\Script\ScriptEvents;
use DevNanny\Composer\Plugin\Interfaces\DecoratorInterface;
use DevNanny\Connector\BaseTestCase;
use DevNanny\GitHook\Installer;
use DevNanny\GitHook\Interfaces\InstallerInterface;
use DevNanny\GitHook\Interfaces\RepositoryContainerInterface;

/**
 * @coversDefaultClass DevNanny\Composer\Plugin\GitHookInstaller
 * @covers ::<!public>
 */
class GitHookInstallerTest extends BaseTestCase
{
    ////////////////////////////////// FIXTURES \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
    const ERROR_METHOD_DOES_NOT_EXIST = 'Subscribing non-public or non-existing method(s)';
    const ERROR_EVENT_DOES_NOT_EXIST = 'Subscribing to invalid event';
    /** @var IOInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $mockIo;
    /** @var GitHookInstaller */
    private $installer;

    protected function setUp()
    {
        $this->installer = new GitHookInstaller();
    }
    /////////////////////////////////// TESTS \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
    /**
     * @covers ::activate
     */
    final public function testInstallerShouldReceiveComposerWhenActivated()
    {
        $installer = $this->installer;

        $this->setExpectedExceptionRegExp(
            \PHPUnit_Framework_Error::class,
            $this->regexMustBeAnInstanceOf('activate', Composer::class)
        );

        /** @noinspection PhpParamsInspection */
        $installer->activate();
    }

    /**
     * @covers ::activate
     */
    final public function testInstallerShouldReceiveIoInterfaceWhenActivated()
    {
        $installer = $this->installer;

        $this->setExpectedExceptionRegExp(
            \PHPUnit_Framework_Error::class,
            $this->regexMustBeAnInstanceOf('activate', IOInterface::class)
        );

        /** @noinspection PhpParamsInspection */
        $installer->activate($this->getMockComposer());
    }

    /**
     * @covers ::activate
     */
    final public function testInstallerShouldAcceptComposerAndIoInterfaceWhenActivated()
    {
        $installer = $this->installer;

        $this->mockIo = $this->getMockBuilder(IOInterface::class)->getMock();

        $installer->activate($this->getMockComposer(), $this->mockIo);

        // @TODO: Be less lazy and actually *check* nothing breaks
        $this->assertTrue(true);

        return $installer;
    }

    /**
     * @covers ::getSubscribedEvents
     *
     * @dataProvider provideValidEventNames
     *
     * @param string[] $validEventNames
     */
    final public function testInstallerShouldReturnValidEventNamesWhenAskedWhichEventsToSubscribeTo(array $validEventNames)
    {
        $installer = $this->installer;

        $subscribedEvents = $installer->getSubscribedEvents();

        $eventNames = array_keys($subscribedEvents);

        $this->assertEquals([], array_diff($eventNames, $validEventNames), self::ERROR_EVENT_DOES_NOT_EXIST);
    }

    /**
     * @covers ::getSubscribedEvents
     *
     * @dataProvider provideMethodNames
     *
     * @param string[] $publicMethods
     */
    final public function testInstallerShouldReturnExistingMethodsWhenAskedWhichEventsToSubscribeTo(array $publicMethods)
    {
        $installer = $this->installer;

        $subscribedEvents = $installer->getSubscribedEvents();
        $methods = array_values($subscribedEvents);

        $this->assertEquals([], array_diff($methods, $publicMethods), self::ERROR_METHOD_DOES_NOT_EXIST);
    }

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

    final public function testInstallerShouldComplainWhenAskedToInstallOutsideOfGitRepository()
    {
        $this->markTestSkipped('This can not be tested until the filesystem can be mocked');

        $installer = $this->installer;

        $this->addMockDecorator($installer);
        $this->addMockContainer($installer);
        $this->addMockInstaller($installer);
        $mockEvent = $this->getMockEvent();
        $mockIo = $this->mockIo;

        $mockIo->expects($this->exactly(1))
            ->method('write')
            ->with(
                $this->matchesRegularExpression(
                    '#' . sprintf(GitHookInstaller::MESSAGE_NOT_A_GIT_REPOSITORY, __DIR__) . '#'
                )
            )
        ;

        $installer->install($mockEvent);
    }

    /**
     * @covers ::install
     */
    final public function testInstallerShouldInstallHookWhenAskedToInstall()
    {
        $installer = $this->installer;

        $this->addMockDecorator($installer);
        $this->addMockContainer($installer);
        $mockInstaller = $this->addMockInstaller($installer);
        $mockEvent = $this->getMockEvent();

        $mockInstaller->expects($this->exactly(1))
            ->method('install')
        ;

        $installer->install($mockEvent);
    }

    /**
     * @covers ::install
     *
     * @depends testInstallerShouldAcceptComposerAndIoInterfaceWhenActivated
     */
    final public function testInstallerShouldComplainWhenInstallFailed()
    {
        $installer = $this->installer;

        $this->addMockDecorator($installer);
        $this->addMockContainer($installer);
        $mockInstaller = $this->addMockInstaller($installer);
        $mockEvent = $this->getMockEvent();
        $mockIo = $this->mockIo;

        $mockInstaller->expects($this->exactly(1))
            ->method('install')
            ->willReturn(false)
        ;

        $mockIo->expects($this->exactly(1))
            ->method('write')
            ->with(
                $this->matchesRegularExpression(
                    '#' . sprintf(GitHookInstaller::MESSAGE_INSTALL_FAILURE, GitHookInstaller::VENDOR) . '#'
                )
            )
        ;

        $installer->install($mockEvent);
    }

    /**
     * @covers ::install
     *
     * @depends testInstallerShouldAcceptComposerAndIoInterfaceWhenActivated
     */
    final public function testInstallerShouldAskUserForConfirmationWhenDifferentGitHookAlreadyExists()
    {
        $this->markTestSkipped('User confirmation is not yet supported');

        $installer = $this->installer;

        $this->addMockDecorator($installer);
        $this->addMockContainer($installer);
        $mockInstaller = $this->addMockInstaller($installer);
        $mockEvent = $this->getMockEvent();
        $mockIo = $this->mockIo;

        $mockInstaller->expects($this->exactly(1))
            ->method('install')
            ->willThrowException(new \UnexpectedValueException(Installer::ERROR_HOOK_ALREADY_EXISTS))
        ;

        $mockInstaller->expects($this->exactly(1))
            ->method('forceInstall')
        ;

        $mockIo->expects($this->exactly(1))
            ->method('askConfirmation')
            ->with(GitHookInstaller::MESSAGE_HOOK_ALREADY_EXISTS)
        ;

        $mockIo->expects($this->exactly(1))
            ->method('write')
            ->with(
                $this->matchesRegularExpression(
                    '#' . sprintf(GitHookInstaller::MESSAGE_INSTALL_FAILURE, GitHookInstaller::VENDOR) . '#'
                )
            )
        ;

        $installer->install($mockEvent);
    }

    /**
     * @covers ::install
     */
    final public function testInstallerShouldStateSuccessWhenInstallSucceeds()
    {
        $installer = $this->installer;

        $this->addMockDecorator($installer);
        $this->addMockContainer($installer);
        $mockInstaller = $this->addMockInstaller($installer);
        $mockEvent = $this->getMockEvent();
        $mockIo = $this->mockIo;

        $mockInstaller->expects($this->exactly(1))
            ->method('install')
            ->willReturn(true)
        ;

        $mockIo->expects($this->exactly(1))
            ->method('write')
            ->with(
                $this->matchesRegularExpression(
                    '#' . sprintf(GitHookInstaller::MESSAGE_INSTALL_SUCCESS, GitHookInstaller::VENDOR) . '#'
                )
            )
        ;

        $installer->install($mockEvent);
    }

    ////////////////////////////// MOCKS AND STUBS \\\\\\\\\\\\\\\\\\\\\\\\\\\\\
    /**
     * @return Composer|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getMockComposer()
    {
        return $this->getMockBuilder(Composer::class)->disableOriginalConstructor()->getMock();
    }

    /**
     * @return CommandEvent|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getMockEvent()
    {
        $mockEvent = $this->getMockBuilder(CommandEvent::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->mockIo = $this->getMockBuilder(IOInterface::class)->getMock();

        $mockEvent->expects($this->exactly(1))
            ->method('getIO')
            ->willReturn($this->mockIo)
        ;

        return $mockEvent;
    }

    /**
     * @param GitHookInstaller $installer
     *
     * @return DecoratorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function addMockDecorator(GitHookInstaller $installer)
    {
        $mockDecorator = $this->getMock(DecoratorInterface::class);

        $reflectionObject = new \ReflectionObject($installer);
        $reflectionProperty = $reflectionObject->getProperty('decorator');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($installer, $mockDecorator);

        $mockDecorator->expects($this->any())
            ->method('decorate')
            ->willReturnArgument(0)
        ;
        return $mockDecorator;
    }

    /**
     * @param GitHookInstaller $installer
     *
     * @return InstallerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function addMockInstaller(GitHookInstaller $installer)
    {
        $mockInstaller = $this->getMock(InstallerInterface::class);

        $reflectionObject = new \ReflectionObject($installer);
        $reflectionProperty = $reflectionObject->getProperty('installer');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($installer, $mockInstaller);

        return $mockInstaller;
    }

    /**
     * @param GitHookInstaller $installer
     */
    private function addMockContainer(GitHookInstaller $installer)
    {
        $mockContainer = $this->getMock(RepositoryContainerInterface::class);

        $reflectionObject = new \ReflectionObject($installer);
        $reflectionProperty = $reflectionObject->getProperty('repositoryContainer');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($installer, $mockContainer);
    }

    /////////////////////////////// DATAPROVIDERS \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
    /**
     * @param $method
     *
     * @return array
     */
    public function provideMethodNames($method)
    {
        $reflectionClass = new \ReflectionClass(GitHookInstaller::class);
        $publicMethods = $reflectionClass->getMethods(\ReflectionMethod::IS_PUBLIC);
        foreach ($publicMethods as &$method) {
            $method = $method->getName();
        }

        return array(
            array($publicMethods, $method)
        );
    }

    /**
     * @return array
     */
    public function provideValidEventNames()
    {
        $reflectionClass = new \ReflectionClass(ScriptEvents::class);
        $constants = $reflectionClass->getConstants();

        return [[$constants]];
    }
}

/*EOF*/

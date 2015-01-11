<?php

namespace DevNanny\Composer\Plugin;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\Script\CommandEvent;
use Composer\Script\ScriptEvents;
use DevNanny\Composer\Plugin\Interfaces\DecoratorInterface;
use DevNanny\GitHook\Installer;
use DevNanny\GitHook\Interfaces\InstallerInterface;
use DevNanny\GitHook\RepositoryContainer;

class GitHookInstaller implements PluginInterface,  EventSubscriberInterface
{
    ////////////////////////////// CLASS PROPERTIES \\\\\\\\\\\\\\\\\\\\\\\\\\\\
    const VENDOR = 'dev-nanny';
    const PROJECT = 'composer-plugin';
    const VENDOR_DIR = 'vendor-dir';

    const MESSAGE_HOOK_ALREADY_EXISTS = 'Another pre-commit hook already exists. Do you want to replace it?';
    const MESSAGE_INSTALL_SUCCESS = 'Installed %s pre-commit hook';
    const MESSAGE_INSTALL_FAILURE = 'Could not install %s pre-commit-hook';
    const MESSAGE_NOT_A_GIT_REPOSITORY = 'Directory "%s" is not a git repository';

    /** @var Composer */
    private $composer;
    /** @var IOInterface */
    private $io;
    /** @var InstallerInterface */
    private $installer;
    /** @var RepositoryContainer */
    private $repositoryContainer;
    /** @var MessageDecorator */
    private $decorator;

    //////////////////////////// SETTERS AND GETTERS \\\\\\\\\\\\\\\\\\\\\\\\\\\
    /**
     * @return DecoratorInterface
     */
    private function getDecorator()
    {
        if ($this->decorator === null) {
            $this->decorator = new MessageDecorator();
        }

        return $this->decorator;
    }

    /**
     * @return InstallerInterface
     */
    private function getInstaller()
    {
        if ($this->installer === null) {
            $container = $this->getRepositoryContainer();
            $this->installer = new Installer($container);
        }

        return $this->installer;
    }

    /**
     * @return RepositoryContainer
     */
    private function getRepositoryContainer()
    {
        if ($this->repositoryContainer === null) {
            $path = $this->getRepositoryPath();
            $this->repositoryContainer = new RepositoryContainer($path);
        }

        return $this->repositoryContainer;
    }

    //////////////////////////////// PUBLIC API \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
    final public function activate(Composer $composer, IOInterface $io)
    {
        $this->composer = $composer;
        $this->io = $io;
    }

    final public static function getSubscribedEvents()
    {
        return array(
            ScriptEvents::POST_INSTALL_CMD => 'install',
            ScriptEvents::POST_UPDATE_CMD => 'install',
        );
    }

    final public function install(CommandEvent $event)
    {
        $this->composer = $event->getComposer();
        $this->io = $event->getIO();

        if ($this->isGitRepository() === false) {
            $this->write(
                'error',
                self::MESSAGE_INSTALL_FAILURE . '. ' . self::MESSAGE_NOT_A_GIT_REPOSITORY,
                self::VENDOR,
                $this->getRepositoryPath()
            );
        } else {
            //@TODO: TRY/CATCH for eventualities and add nice messages for the user
            $installed = $this->doInstall();
            if ($installed === true) {
                $this->write('info', self::MESSAGE_INSTALL_SUCCESS, self::VENDOR);
            } else {
                $this->write('error', self::MESSAGE_INSTALL_FAILURE, self::VENDOR);
            }
        }
    }

    ////////////////////////////// UTILITY METHODS \\\\\\\\\\\\\\\\\\\\\\\\\\\\\
    /**
     * @return string
     */
    private function getRepositoryPath()
    {
        $vendorDirectory = $this->getVendorDirectory();

        // @CHECKME: Couldn't this code break if a custom `vendor-dir` is used?
        $parts = explode(DIRECTORY_SEPARATOR, $vendorDirectory);
        array_pop($parts); // Remove 'vendor'
        $path = implode(DIRECTORY_SEPARATOR, $parts);

        return $path;
    }

    /**
     * @param string $type
     * @param string $message
     */
    private function write($type, $message)
    {
        $parameters = func_get_args();
        array_shift($parameters);

        $message = call_user_func_array('sprintf', $parameters);
        $message = $this->decorate($message);

        $message = sprintf('<%1$s>%2$s</%1$s>', $type, $message);

        $this->io->write($message);
    }

    /**
     * @return bool
     */
    private function isGitRepository()
    {
        $path = $this->getRepositoryPath();
        return is_dir($path . '/.git');
    }

    private function doInstall()
    {
        $installer = $this->getInstaller();

        $installed = $installer->install(Installer::PRE_COMMIT);

        /* @TODO: Ask user to force install once Installer::forceInstall() is implemented
        try {
        } catch(\UnexpectedValueException $exception){
            if ($exception->getMessage() === Installer::ERROR_HOOK_ALREADY_EXISTS) {
                $answer = $this->io->askConfirmation(self::MESSAGE_HOOK_ALREADY_EXISTS);
                if ($answer === true) {
                    $installed = $installer->forceInstall(Installer::PRE_COMMIT);
                }
            } else {
                throw $exception;
            }
        }
        */

        return $installed;
    }

    /**
     * @param string $message
     *
     * @return string
     */
    private function decorate($message)
    {
        $decorator = $this->getDecorator();

        if ($decorator !== null) {
            $message = $this->decorator->decorate($message);
        }
        return $message;
    }

    /**
     * @return mixed
     */
    private function getVendorDirectory()
    {
        return $this->composer->getConfig()->get(self::VENDOR_DIR);
    }
}

/*EOF*/

<?php

namespace DevNanny\Composer\Plugin;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\Script\CommandEvent;
use Composer\Script\ScriptEvents;
use DevNanny\GitHook\Installer;
use DevNanny\GitHook\RepositoryContainer;

class GitHookInstaller implements PluginInterface,  EventSubscriberInterface
{
    ////////////////////////////// CLASS PROPERTIES \\\\\\\\\\\\\\\\\\\\\\\\\\\\
    const VENDOR = 'dev-nanny';
    const PROJECT = 'composer-plugin';

    /** @var Composer */
    private $composer;
    /** @var IOInterface */
    private $io;

    //////////////////////////// SETTERS AND GETTERS \\\\\\\\\\\\\\\\\\\\\\\\\\\
    //////////////////////////////// PUBLIC API \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\

    final public function activate(Composer $composer, IOInterface $io)
    {
        $this->composer = $composer;
        $this->io = $io;
    }

    final public static function getSubscribedEvents()
    {
        return array(
            /*
            ConsoleEvents::COMMAND => 'consoleCommandEventHandler',
            ConsoleEvents::TERMINATE => 'consoleTerminateEventHandler',
            ConsoleEvents::EXCEPTION => 'consoleExceptionEventHandler',

            InstallerEvents::PRE_DEPENDENCIES_SOLVING => 'installerPreDependenciesSolvingEventHandler',
            InstallerEvents::POST_DEPENDENCIES_SOLVING => 'installerPostDependenciesSolvingEventHandler',

            PluginEvents::PRE_FILE_DOWNLOAD => 'pluginPreFileDownloadEventHandler',
            PluginEvents::COMMAND => 'pluginCommandEventHandler',

            ScriptEvents::PRE_ARCHIVE_CMD => 'scriptPreArchiveCommandEventHandler',
            ScriptEvents::POST_ARCHIVE_CMD => 'scriptPostArchiveCommandEventHandler',
            ScriptEvents::PRE_AUTOLOAD_DUMP => 'scriptPrePostAutoloadDumpEventHandler',
            ScriptEvents::POST_AUTOLOAD_DUMP => 'scriptPostAutoloadDumpEventHandler',
            //ScriptEvents::PRE_CREATE_PROJECT_CMD => 'scriptPreCreateProjectCommandEventHandler',
            ScriptEvents::POST_CREATE_PROJECT_CMD => 'scriptPostCreateProjectCommandEventHandler',
            ScriptEvents::PRE_INSTALL_CMD => 'scriptPreInstallCommandEventHandler',
            ScriptEvents::PRE_PACKAGE_INSTALL => 'scriptPrePackageInstallEventHandler',
            ScriptEvents::POST_PACKAGE_INSTALL => 'scriptPostPackageInstallEventHandler',
            ScriptEvents::PRE_PACKAGE_UNINSTALL => 'scriptPrePackageUninstallEventHandler',
            ScriptEvents::POST_PACKAGE_UNINSTALL => 'scriptPostPackageUninstallEventHandler',
            ScriptEvents::PRE_PACKAGE_UPDATE => 'scriptPrePackageUpdateEventHandler',
            ScriptEvents::POST_PACKAGE_UPDATE => 'scriptPostPackageUpdateEventHandler',
            //ScriptEvents::PRE_ROOT_PACKAGE_INSTALL => 'scriptPreRootPackageInstallEventHandler',
            ScriptEvents::POST_ROOT_PACKAGE_INSTALL => 'scriptPostRootPackageInstallEventHandler',
            ScriptEvents::PRE_STATUS_CMD => 'scriptPreStatusCommandEventHandler',
            ScriptEvents::POST_STATUS_CMD => 'scriptPostStatusCommandEventHandler',
            ScriptEvents::PRE_UPDATE_CMD => 'scriptPreUpdateCommandEventHandler',
            */
            ScriptEvents::POST_INSTALL_CMD => 'scriptPostInstallCommandEventHandler',
            ScriptEvents::POST_UPDATE_CMD => 'scriptPostUpdateCommandEventHandler',
        );
    }

    final public function install(CommandEvent $event)
    {
        //@TODO: TRY/CATCH for eventualities and add nice messages for the user
        //@TODO: When the hook exists but does not match, ask to overwrite using $this->io->ask[etc]

        $path = $this->getRepositoryPath();

        $container = new RepositoryContainer($path);
        $installer = new Installer($container);

        $installer->install(Installer::PRE_COMMIT);
    }

    ////////////////////////////// UTILITY METHODS \\\\\\\\\\\\\\\\\\\\\\\\\\\\\
    /**
     * @return string
     */
    private function getRepositoryPath()
    {
        $currentDirectory = __DIR__;

        $vendorDirectory = 'vendor/' . self::VENDOR . '/' . self::PROJECT . '/src';
        $length = strlen($vendorDirectory);
        if (substr($currentDirectory, -$length) === $vendorDirectory) {
            $path = substr($currentDirectory, 0, -$length);
        } else {
            $path = substr($currentDirectory, 0, -strlen('src'));
        }
        var_dump($path);
        return $path;
    }
}

/*EOF*/

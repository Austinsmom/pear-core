<?php
//
// +----------------------------------------------------------------------+
// | PHP Version 5                                                        |
// +----------------------------------------------------------------------+
// | Copyright (c) 1997-2004 The PHP Group                                |
// +----------------------------------------------------------------------+
// | This source file is subject to version 3.0 of the PHP license,       |
// | that is bundled with this package in the file LICENSE, and is        |
// | available through the world-wide-web at the following url:           |
// | http://www.php.net/license/3_0.txt.                                  |
// | If you did not receive a copy of the PHP license and are unable to   |
// | obtain it through the world-wide-web, please send a note to          |
// | license@php.net so we can mail you a copy immediately.               |
// +----------------------------------------------------------------------+
// | Author: Stig S�ther Bakken <ssb@php.net>                             |
// +----------------------------------------------------------------------+
//
// $Id$

require_once "PEAR/Command/Common.php";
require_once "PEAR/Installer.php";

/**
 * PEAR commands for installation or deinstallation/upgrading of
 * packages.
 *
 */
class PEAR_Command_Install extends PEAR_Command_Common
{
    // {{{ properties

    var $commands = array(
        'install' => array(
            'summary' => 'Install Package',
            'function' => 'doInstall',
            'shortcut' => 'i',
            'options' => array(
                'force' => array(
                    'shortopt' => 'f',
                    'doc' => 'will overwrite newer installed packages',
                    ),
                'nodeps' => array(
                    'shortopt' => 'n',
                    'doc' => 'ignore dependencies, install anyway',
                    ),
                'register-only' => array(
                    'shortopt' => 'r',
                    'doc' => 'do not install files, only register the package as installed',
                    ),
                'soft' => array(
                    'shortopt' => 's',
                    'doc' => 'soft install, fail silently, or upgrade if already installed',
                    ),
                'nobuild' => array(
                    'shortopt' => 'B',
                    'doc' => 'don\'t build C extensions',
                    ),
                'nocompress' => array(
                    'shortopt' => 'Z',
                    'doc' => 'request uncompressed files when downloading',
                    ),
                'installroot' => array(
                    'shortopt' => 'R',
                    'arg' => 'DIR',
                    'doc' => 'root directory used when installing files (ala PHP\'s INSTALL_ROOT)',
                    ),
                'ignore-errors' => array(
                    'doc' => 'force install even if there were errors',
                    ),
                'alldeps' => array(
                    'shortopt' => 'a',
                    'doc' => 'install all required and optional dependencies',
                    ),
                'onlyreqdeps' => array(
                    'shortopt' => 'o',
                    'doc' => 'install all required dependencies',
                    ),
                ),
            'doc' => '[channel/]<package> ...
Installs one or more PEAR packages.  You can specify a package to
install in four ways:

"Package-1.0.tgz" : installs from a local file

"http://example.com/Package-1.0.tgz" : installs from
anywhere on the net.

"package.xml" : installs the package described in
package.xml.  Useful for testing, or for wrapping a PEAR package in
another package manager such as RPM.

"Package[-version/state][.tar]" : queries your default channel\'s server
({config master_server}) and downloads the newest package with
the preferred quality/state ({config preferred_state}).

To retrieve Package version 1.1, use "Package-1.1," to retrieve
Package state beta, use "Package-beta."  To retrieve an uncompressed
file, append .tar (make sure there is no file by the same name first)

To download a package from another channel, prefix with the channel name like
"channel/Package"

More than one package may be specified at once.  It is ok to mix these
four ways of specifying packages.
'),
        'upgrade' => array(
            'summary' => 'Upgrade Package',
            'function' => 'doInstall',
            'shortcut' => 'up',
            'options' => array(
                'force' => array(
                    'shortopt' => 'f',
                    'doc' => 'overwrite newer installed packages',
                    ),
                'nodeps' => array(
                    'shortopt' => 'n',
                    'doc' => 'ignore dependencies, upgrade anyway',
                    ),
                'register-only' => array(
                    'shortopt' => 'r',
                    'doc' => 'do not install files, only register the package as upgraded',
                    ),
                'nobuild' => array(
                    'shortopt' => 'B',
                    'doc' => 'don\'t build C extensions',
                    ),
                'nocompress' => array(
                    'shortopt' => 'Z',
                    'doc' => 'request uncompressed files when downloading',
                    ),
                'installroot' => array(
                    'shortopt' => 'R',
                    'arg' => 'DIR',
                    'doc' => 'root directory used when installing files (ala PHP\'s INSTALL_ROOT)',
                    ),
                'ignore-errors' => array(
                    'doc' => 'force install even if there were errors',
                    ),
                'alldeps' => array(
                    'shortopt' => 'a',
                    'doc' => 'install all required and optional dependencies',
                    ),
                'onlyreqdeps' => array(
                    'shortopt' => 'o',
                    'doc' => 'install all required dependencies',
                    ),
                ),
            'doc' => '<package> ...
Upgrades one or more PEAR packages.  See documentation for the
"install" command for ways to specify a package.

When upgrading, your package will be updated if the provided new
package has a higher version number (use the -f option if you need to
upgrade anyway).

More than one package may be specified at once.
'),
        'upgrade-all' => array(
            'summary' => 'Upgrade All Packages',
            'function' => 'doInstall',
            'shortcut' => 'ua',
            'options' => array(
                'nodeps' => array(
                    'shortopt' => 'n',
                    'doc' => 'ignore dependencies, upgrade anyway',
                    ),
                'register-only' => array(
                    'shortopt' => 'r',
                    'doc' => 'do not install files, only register the package as upgraded',
                    ),
                'nobuild' => array(
                    'shortopt' => 'B',
                    'doc' => 'don\'t build C extensions',
                    ),
                'nocompress' => array(
                    'shortopt' => 'Z',
                    'doc' => 'request uncompressed files when downloading',
                    ),
                'installroot' => array(
                    'shortopt' => 'R',
                    'arg' => 'DIR',
                    'doc' => 'root directory used when installing files (ala PHP\'s INSTALL_ROOT)',
                    ),
                'ignore-errors' => array(
                    'doc' => 'force install even if there were errors',
                    ),
                ),
            'doc' => '
Upgrades all packages that have a newer release available.  Upgrades are
done only if there is a release available of the state specified in
"preferred_state" (currently {config preferred_state}), or a state considered
more stable.
'),
        'uninstall' => array(
            'summary' => 'Un-install Package',
            'function' => 'doUninstall',
            'shortcut' => 'un',
            'options' => array(
                'nodeps' => array(
                    'shortopt' => 'n',
                    'doc' => 'ignore dependencies, uninstall anyway',
                    ),
                'register-only' => array(
                    'shortopt' => 'r',
                    'doc' => 'do not remove files, only register the packages as not installed',
                    ),
                'installroot' => array(
                    'shortopt' => 'R',
                    'arg' => 'DIR',
                    'doc' => 'root directory used when installing files (ala PHP\'s INSTALL_ROOT)',
                    ),
                'ignore-errors' => array(
                    'doc' => 'force install even if there were errors',
                    ),
                ),
            'doc' => '[channel/]<package> ...
Uninstalls one or more PEAR packages.  More than one package may be
specified at once.  Prefix with channel name to uninstall from a
channel not in your default channel ({config default_channel})
'),
        'bundle' => array(
            'summary' => 'Unpacks a Pecl Package',
            'function' => 'doBundle',
            'shortcut' => 'bun',
            'options' => array(
                'destination' => array(
                   'shortopt' => 'd',
                    'arg' => 'DIR',
                    'doc' => 'Optional destination directory for unpacking (defaults to current path or "ext" if exists)',
                    ),
                'force' => array(
                    'shortopt' => 'f',
                    'doc' => 'Force the unpacking even if there were errors in the package',
                ),
            ),
            'doc' => '<package>
Unpacks a Pecl Package into the selected location. It will download the
package if needed.
'),
    );

    // }}}
    // {{{ constructor

    /**
     * PEAR_Command_Install constructor.
     *
     * @access public
     */
    function PEAR_Command_Install(&$ui, &$config)
    {
        parent::PEAR_Command_Common($ui, $config);
    }

    // }}}

    // {{{ doInstall()

    function doInstall($command, $options, $params)
    {
        require_once 'PEAR/Downloader.php';
        if (empty($this->installer)) {
            $this->installer = &new PEAR_Installer($this->ui);
        }
        if ($command == 'upgrade') {
            $options['upgrade'] = true;
        }
        if ($command == 'upgrade-all') {
            include_once "PEAR/Remote.php";
            $options['upgrade'] = true;
            $reg = new PEAR_Registry($this->config->get('php_dir'));
            $remote = &new PEAR_Remote($this->config, $reg);
            $savechannel = $this->config->get('default_channel');
            foreach ($reg->listChannels as $channel) {
                $this->config->set('default_channel', $channel);
                $state = $this->config->get('preferred_state');
                PEAR::pushErrorHandling(PEAR_ERROR_RETURN);
                if (empty($state) || $state == 'any') {
                    $latest = $remote->call("package.listLatestReleases");
                } else {
                    $latest = $remote->call("package.listLatestReleases", $state);
                }
                PEAR::popErrorHandling();
                if (PEAR::isError($latest)) {
                    continue;
                }
                $installed = array_flip($reg->listPackages($channel));
                $params = array();
                foreach ($latest as $package => $info) {
                    $package = strtolower($package);
                    if (!isset($installed[$package])) {
                        // skip packages we don't have installed
                        continue;
                    }
                    $inst_version = $reg->packageInfo($package, 'version', $channel);
                    if (version_compare("$info[version]", "$inst_version", "le")) {
                        // installed version is up-to-date
                        continue;
                    }
                    $params[] = $reg->parsedPackageNameToString(array('package' => $package, 'channel' => $channel));
                    $this->ui->outputData(array('data' => "Will upgrade $package"), $command);
                }
            }
            $this->config->set('default_channel', $savechannel);
        }
        $this->downloader = &new PEAR_Downloader($this->ui, $options, $this->config);
        $errors = array();
        $downloaded = array();
        $downloaded = &$this->downloader->download($params);
        $errors = $this->downloader->getErrorMsgs();
        if (count($errors)) {
            foreach ($errors as $error) {
                $err['data'][] = array($error);
            }
            $err['headline'] = 'Install Errors';
            $this->ui->outputData($err);
            return $this->raiseError("$command failed");
        }
        $this->installer->sortPackagesForInstall($downloaded);
        $this->installer->setOptions($options);
        if (PEAR::isError($err = $this->installer->setDownloadedPackages($downloaded))) {
            $this->ui->outputData($err->getMessage());
            return;
        }
        $reg = &$this->config->getRegistry();
        foreach ($downloaded as $param) {
            PEAR::pushErrorHandling(PEAR_ERROR_RETURN);
            $info = $this->installer->install($param, $options,
                $this->config);
            PEAR::popErrorHandling();
            if (PEAR::isError($info)) {
                if (!$param->installBinary($this->installer)) {
                    $this->ui->outputData('ERROR: ' .$info->getMessage());
                    continue;
                }
            }
            if (is_array($info)) {
                if ($this->config->get('verbose') > 0) {
                    $channel = $param->getChannel();
                    $label = $reg->parsedPackageNameToString(
                        array(
                            'channel' => $channel,
                            'package' => $param->getPackage(),
                            'version' => $param->getVersion(),
                        ));
                    $out = array('data' => "$command ok: $label");
                    if (isset($info['release_warnings'])) {
                        $out['release_warnings'] = $info['release_warnings'];
                    }
                    $this->ui->outputData($out, $command);
                }
            } else {
                return $this->raiseError("$command failed");
            }
        }
        return true;
    }

    // }}}
    // {{{ doUninstall()

    function doUninstall($command, $options, $params)
    {
        if (empty($this->installer)) {
            $this->installer = &new PEAR_Installer($this->ui);
        }
        if (sizeof($params) < 1) {
            return $this->raiseError("Please supply the package(s) you want to uninstall");
        }
        $reg = &$this->config->getRegistry();
        $newparams = array();
        $badparams = array();
        foreach ($params as $pkg) {
            $channel = $this->config->get('default_channel');
            PEAR::pushErrorHandling(PEAR_ERROR_RETURN);
            $parsed = $reg->parsePackageName($pkg);
            PEAR::popErrorHandling();
            if (!$parsed || PEAR::isError($parsed)) {
                $badparams[] = $pkg;
                continue;
            }
            $package = $parsed['package'];
            $channel = $parsed['channel'];
            $info = &$reg->getPackage($package, $channel);
            if ($info === null) {
                $badparams[] = $pkg;
            } else {
                $newparams[] = &$info;
                // add the contents of a dependency group to the list of installed packages
                if (isset($parsed['group'])) {
                    $group = $info->getDependencyGroup($parsed['group']);
                    if ($group) {
                        $installed = &$reg->getInstalledGroup($group);
                        if ($installed) {
                            foreach ($installed as $i => $p) {
                                $newparams[] = & $installed[$i];
                            }
                        }
                    }
                }
            }
        }
        $this->installer->sortPackagesForUninstall($newparams);
        $params = $newparams;
        // twist this to use it to check on whether dependent packages are also being uninstalled
        // for circular dependencies like subpackages
        $this->installer->setDownloadedPackages($newparams);
        $params = array_merge($params, $badparams);
        foreach ($params as $pkg) {
            $this->installer->pushErrorHandling(PEAR_ERROR_RETURN);
            if ($err = $this->installer->uninstall($pkg, $options)) {
                $this->installer->popErrorHandling();
                if (PEAR::isError($err)) {
                    $this->ui->outputData($err->getMessage(), $command);
                    continue;
                }
                if ($this->config->get('verbose') > 0) {
                    if (is_object($pkg)) {
                        $pkg = $reg->parsedPackageNameToString($pkg);
                    }
                    $this->ui->outputData("uninstall ok: $pkg", $command);
                }
            } else {
                $this->installer->popErrorHandling();
                if (is_object($pkg)) {
                    $pkg = $reg->parsedPackageNameToString($pkg);
                }
                return $this->raiseError("uninstall failed: $pkg");
            }
        }
        return true;
    }

    // }}}


    // }}}
    // {{{ doBundle()
    /*
    (cox) It just downloads and untars the package, does not do
            any check that the PEAR_Installer::_installFile() does.
    */

    function doBundle($command, $options, $params)
    {
        if (empty($this->installer)) {
            $this->installer = &new PEAR_Downloader($this->ui);
        }
        $installer = &$this->installer;
        $reg = &$this->config->getRegistry();
        if (sizeof($params) < 1) {
            return $this->raiseError("Please supply the package you want to bundle");
        }

        if (isset($options['destination'])) {
            if (!is_dir($options['destination'])) {
                System::mkdir('-p ' . $options['destination']);
            }
            $dest = realpath($options['destination']);
        } else {
            $pwd = getcwd();
            if (is_dir($pwd . DIRECTORY_SEPARATOR . 'ext')) {
                $dest = $pwd . DIRECTORY_SEPARATOR . 'ext';
            } else {
                $dest = $pwd;
            }
        }
        $pkgfile = &$this->installer->bundle($params[0], $dest);
        if (PEAR::isError($pkgfile)) {
            return $pkgfile;
        }
        $pkgname = $pkgfile->getName();
        $pkgversion = $pkgfile->getVersion();

        // Unpacking -------------------------------------------------
        $dest .= DIRECTORY_SEPARATOR . $pkgname;
        $orig = $pkgname . '-' . $pkgversion;

        $tar = new Archive_Tar($pkgfile->getArchive());
        if (!@$tar->extractModify($dest, $orig)) {
            return $this->raiseError('unable to unpack ' . $pkgfile->getArchive());
        }
        $this->ui->outputData("Package ready at '$dest'");
    // }}}
    }

    // }}}

}
?>

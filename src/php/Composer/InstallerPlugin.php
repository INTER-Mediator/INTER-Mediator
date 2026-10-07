<?php

namespace INTERMediator\Composer;

use Composer\CaBundle\CaBundle;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Composer\Script\Event;
use Composer\Script\ScriptEvents;

class InstallerPlugin implements PluginInterface, EventSubscriberInterface
{
    private const PACKAGE_NAME = 'inter-mediator/inter-mediator';

    /**
     * Expected SHA-256 checksums of the official pnpm install scripts.
     *
     * Pinned to pnpm/get.pnpm.io commit
     * 87bc637b6daf3e72ee61eb81c7d34460242f7357 (SHASUMS256.txt). A downloaded
     * installer is executed only when its checksum matches the value below,
     * so a tampered or unexpectedly updated script is rejected.
     *
     * @see https://github.com/pnpm/get.pnpm.io/blob/87bc637b6daf3e72ee61eb81c7d34460242f7357/SHASUMS256.txt
     */
    private const PNPM_INSTALL_SH_SHA256 = '9bbfd08ac1bd3001828b7a645ab071d31cbc7a26c92808554dbb6283313121ec';
    private const PNPM_INSTALL_PS1_SHA256 = 'c8ec3ded3a9d1660cd6876f4049691bdcd1c883cba746a2d15cfbaf9a094c189';

    /**
     * Immutable source of the pnpm install scripts.
     *
     * The scripts are fetched from the pinned commit on raw.githubusercontent.com
     * (not the mutable https://get.pnpm.io/) so their bytes never change and the
     * checksums above always match. Updating pnpm's bootstrap means bumping this
     * commit together with the checksums above.
     */
    private const PNPM_INSTALL_BASE_URL =
        'https://raw.githubusercontent.com/pnpm/get.pnpm.io/87bc637b6daf3e72ee61eb81c7d34460242f7357/';

    /**
     * Apply plugin modifications to Composer.
     *
     * Note: state is intentionally not stored on the instance because the
     * post-install/update logic is invoked statically as a Composer script
     * callback as well (Composer scripts cannot call instance methods).
     */
    public function activate(\Composer\Composer $composer, IOInterface $io): void
    {
    }

    /**
     * Remove any hooks from Composer.
     */
    public function deactivate(\Composer\Composer $composer, IOInterface $io): void
    {
    }

    /**
     * Prepare the plugin to be uninstalled.
     */
    public function uninstall(\Composer\Composer $composer, IOInterface $io): void
    {
    }

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * @return array<string, string|array{0: string, 1?: int}>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            ScriptEvents::POST_INSTALL_CMD => ['onPostInstallCmd', 0],
            ScriptEvents::POST_UPDATE_CMD => ['onPostUpdateCmd', 0],
        ];
    }

    /**
     * Called after the "composer install" command.
     *
     * Implemented as a static method, so it can be invoked both as a plugin
     * event subscriber (when this package is installed as a dependency) and
     * as a Composer script callback (when this package is the root project).
     */
    public static function onPostInstallCmd(Event $event): void
    {
        self::runPostInstallTasks($event, false);
    }

    /**
     * Called after the "composer update" command.
     */
    public static function onPostUpdateCmd(Event $event): void
    {
        self::runPostInstallTasks($event, true);
    }

    /**
     * Composer script callback for the "clear" script.
     *
     * Removes the installed "node_modules" and "vendor" directories using
     * PHP's filesystem functions, so it works on every platform including
     * Windows. This replaces the UNIX-only "rm -rf node_modules vendor"
     * shell command.
     */
    public static function onClear(Event $event): void
    {
        $composer = $event->getComposer();
        $io = $event->getIO();
        $vendorDir = $composer->getConfig()->get('vendor-dir');
        $baseDir = dirname($vendorDir);

        // node_modules is removed first and vendor last. The vendor directory
        // holds the Composer autoloader, but this class is loaded from
        // src/php (already in memory), so removal is safe.
        $targets = [
            $baseDir . '/node_modules',
            $vendorDir,
        ];
        foreach ($targets as $target) {
            if (self::removeDirectory($target)) {
                $io->write(sprintf('<info>INTER-Mediator: Removed "%s".</info>', $target));
            }
        }
    }

    /**
     * Execute the post-install/update tasks.
     *
     * @param bool $isUpdate True when called after "composer update",
     *                       false when called after "composer install".
     */
    protected static function runPostInstallTasks(Event $event, bool $isUpdate): void
    {
        $composer = $event->getComposer();
        $io = $event->getIO();
        $vendorDir = $composer->getConfig()->get('vendor-dir');
        $baseDir = dirname($vendorDir);
        $taskMessage = $isUpdate ? 'update' : 'install';
        // Resolve where the official installer places the pnpm executable so it
        // can be invoked by an explicit path (the installer cannot update this
        // process's PATH). An explicit PNPM_HOME wins, matching "pnpm setup".
        $pnpmHome = getenv('PNPM_HOME') ?: '';
        if ($pnpmHome === '') {
            $home = getenv('HOME') ?: '';
            if ($home === '' && PHP_OS_FAMILY !== 'Windows') {
                throw new \RuntimeException(
                    'INTER-Mediator: Cannot determine the pnpm install location. Set PNPM_HOME or HOME.'
                );
            }
            if (PHP_OS_FAMILY === 'Darwin') {
                $pnpmHome = $home . '/Library/pnpm';
            } else {
                $xdgDataHome = getenv('XDG_DATA_HOME') ?: $home . '/.local/share';
                $pnpmHome = $xdgDataHome . '/pnpm';
            }
        }
        $pnpmPath = escapeshellarg("{$pnpmHome}/bin/pnpm");

        $scriptPath = null;
        try {
            if (self::isInstalledAsDependency($composer)) {
                // INTER-Mediator was installed as a dependency via "composer require".
                $io->write("<info>INTER-Mediator: Running post-{$taskMessage} tasks (dependency mode)...</info>");
                if (PHP_OS_FAMILY === 'Windows') {
                    // Windows: download, verify the checksum, then run the pnpm installer.
                    $scriptPath = self::downloadVerifiedPnpmInstaller($io, true);
                    // A PowerShell single-quoted string escapes a quote by doubling it.
                    $psScriptPath = str_replace("'", "''", $scriptPath);
                    // Run the verified installer and "pnpm ci" in the same
                    // PowerShell session so the PATH the installer sets stays available.
                    self::executeCommand($io, $baseDir, 'powershell -NoProfile -ExecutionPolicy Bypass -Command '
                        . '"& \'' . $psScriptPath . '\'; cd .\\vendor\\inter-mediator\\inter-mediator; pnpm ci"'
                    );
                    self::executeCommand($io, $baseDir, 'powershell -NoProfile -ExecutionPolicy Bypass -File '
                        . '".\\vendor\\inter-mediator\\inter-mediator\\dist-docs\\generateminifyjshere.ps1"'
                    );
                } else {
                    // macOS / Linux: download, verify the checksum, then run the pnpm installer.
                    $scriptPath = self::downloadVerifiedPnpmInstaller($io, false);
                    self::executeCommand($io, $baseDir, 'sh ' . escapeshellarg($scriptPath));
                    self::executeCommand($io, $baseDir, "cd ./vendor/inter-mediator/inter-mediator && {$pnpmPath} ci");
                    self::executeCommand($io, $baseDir, "./vendor/inter-mediator/inter-mediator/dist-docs/generateminifyjshere.sh");
                }

                // As the final step, run the root project's post-install/update
                // hook script if it provides one (dependency mode only).
                self::runAfterScript($io, $baseDir, $isUpdate);
            } else {
                // INTER-Mediator is the root project (e.g., git clone + composer install).
                $io->write("<info>INTER-Mediator: Running post-{$taskMessage} tasks (root project mode)...</info>");
                if (PHP_OS_FAMILY === 'Windows') {
                    // Windows: download, verify the checksum, then run the pnpm installer.
                    $scriptPath = self::downloadVerifiedPnpmInstaller($io, true);
                    // A PowerShell single-quoted string escapes a quote by doubling it.
                    $psScriptPath = str_replace("'", "''", $scriptPath);
                    // Run the verified installer and "pnpm ci" in the same
                    // PowerShell session so the PATH the installer sets stays available.
                    self::executeCommand($io, $baseDir, 'powershell -NoProfile -ExecutionPolicy Bypass -Command '
                        . '"& \'' . $psScriptPath . '\'; pnpm ci"'
                    );
                } else {
                    // macOS / Linux: download, verify the checksum, then run the pnpm installer.
                    $scriptPath = self::downloadVerifiedPnpmInstaller($io, false);
                    $preScript = self::isRunningInDocker() ? '$HOME/.shrc SHELL="$(which sh)" ' : '';
                    self::executeCommand($io, $baseDir, $preScript . 'sh ' . escapeshellarg($scriptPath));
                    self::executeCommand($io, $baseDir, "{$pnpmPath} ci");
                }
            }
        } catch (\RuntimeException $e) {
            $io->writeError(sprintf('<error>INTER-Mediator: Post-%s tasks failed: %s</error>', $taskMessage, $e->getMessage()));
            throw $e;
        } finally {
            if ($scriptPath !== null) {
                @unlink($scriptPath);
            }
        }
        @unlink($baseDir . '/__Did_you_run_composer_update.txt');

        $io->write('<info>INTER-Mediator: Post-install tasks completed.</info>');
    }

    /**
     * Download the official pnpm install script for the current platform from
     * the pinned commit, verify its SHA-256 checksum against the expected
     * value, and write it to a temporary file.
     *
     * @param IOInterface $io
     * @param bool $isWindows True to fetch "install.ps1", false for "install.sh".
     * @return string Path to the verified temporary installer script.
     * @throws \RuntimeException When the download fails or the checksum does
     *                           not match the pinned value.
     */
    protected static function downloadVerifiedPnpmInstaller(IOInterface $io, bool $isWindows): string
    {
        $fileName = $isWindows ? 'install.ps1' : 'install.sh';
        $url = self::PNPM_INSTALL_BASE_URL . $fileName;
        $expectedHash = $isWindows ? self::PNPM_INSTALL_PS1_SHA256 : self::PNPM_INSTALL_SH_SHA256;

        $io->write(sprintf('  > Downloading and verifying %s', $url));

        $curl = curl_init($url);
        if ($curl === false) {
            throw new \RuntimeException('Failed to initialize the download of the pnpm installer.');
        }
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_FAILONERROR => true,
            CURLOPT_CONNECTTIMEOUT => 30,
            CURLOPT_TIMEOUT => 120,
        ]);
        // Trust the CA bundle that Composer ships with (composer/ca-bundle) so
        // HTTPS peer verification succeeds even when PHP has no curl.cainfo
        // configured, which is the default on Windows.
        $caBundle = CaBundle::getSystemCaRootBundlePath();
        if (is_dir($caBundle)) {
            curl_setopt($curl, CURLOPT_CAPATH, $caBundle);
        } else {
            curl_setopt($curl, CURLOPT_CAINFO, $caBundle);
        }
        $contents = curl_exec($curl);
        $errorMessage = curl_error($curl);

        if (!is_string($contents) || $contents === '') {
            throw new \RuntimeException(sprintf('Failed to download %s. %s', $url, $errorMessage));
        }

        $actualHash = hash('sha256', $contents);
        if (!hash_equals($expectedHash, $actualHash)) {
            throw new \RuntimeException(sprintf(
                'SHA-256 checksum mismatch for %s (expected %s, got %s). Aborting the pnpm installation for security reasons.',
                $fileName,
                $expectedHash,
                $actualHash
            ));
        }

        $tempPath = tempnam(sys_get_temp_dir(), 'im_pnpm_');
        if ($tempPath === false) {
            throw new \RuntimeException('Failed to create a temporary file for the pnpm installer.');
        }

        // PowerShell only executes script files that have a ".ps1" extension.
        $scriptPath = $tempPath;
        if ($isWindows) {
            $scriptPath = $tempPath . '.ps1';
            if (!@rename($tempPath, $scriptPath)) {
                @unlink($tempPath);
                throw new \RuntimeException('Failed to prepare the pnpm installer file.');
            }
        }

        if (file_put_contents($scriptPath, $contents) === false) {
            @unlink($scriptPath);
            throw new \RuntimeException('Failed to write the pnpm installer to a temporary file.');
        }

        $io->write(sprintf('<info>INTER-Mediator: Verified %s (SHA-256 matches the pinned checksum).</info>', $fileName));
        return $scriptPath;
    }

    /**
     * Determine whether INTER-Mediator is installed as a Composer dependency
     * (true) or is being used as the root project (false).
     */
    protected static function isInstalledAsDependency(\Composer\Composer $composer): bool
    {
        return $composer->getPackage()->getName() !== self::PACKAGE_NAME;
    }

    /**
     * Detect whether the current process is running inside a Docker container.
     *
     * This combines several common heuristics: an explicit environment flag,
     * the legacy /.dockerenv marker, container hints in /proc/.../cgroup,
     * and the "container" environment variable used by some CI systems.
     */
    protected static function isRunningInDocker(): bool
    {
        if (getenv('INTERMEDIATOR_DOCKER') === '1' || getenv('IM_IN_DOCKER') === '1') {
            return true;
        }
        if (getenv('container') === 'docker') {
            return true;
        }
        if (is_file('/.dockerenv')) {
            return true;
        }

        $cgroupFiles = ['/proc/self/cgroup', '/proc/1/cgroup'];
        foreach ($cgroupFiles as $cgroupFile) {
            if (!is_file($cgroupFile)) {
                continue;
            }
            $contents = @file_get_contents($cgroupFile);
            if (!is_string($contents) || $contents === '') {
                continue;
            }
            foreach (['docker', 'containerd', 'lxc', 'kubepods'] as $needle) {
                if (str_contains($contents, $needle)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Run the root project's post-install/update hook script as the final
     * step, if it exists.
     *
     * When INTER-Mediator is installed as a dependency, the root project may
     * provide "lib/doafterinstall.sh" (executed after "composer install") or
     * "lib/doafterupdate.sh" (executed after "composer update"). On Windows,
     * the corresponding "lib/doafterinstall.ps1" / "lib/doafterupdate.ps1"
     * files are used instead. If the script file does not exist, it is
     * silently ignored.
     * @param IOInterface $io
     * @param string $baseDir
     * @param bool $isUpdate
     */
    protected static function runAfterScript(IOInterface $io, string $baseDir, bool $isUpdate): void
    {
        $scriptBaseName = $isUpdate ? 'doafterupdate' : 'doafterinstall';
        $isWindows = PHP_OS_FAMILY === 'Windows';
        $relativePath = 'lib/' . $scriptBaseName . ($isWindows ? '.ps1' : '.sh');

        if (!is_file($baseDir . '/' . $relativePath)) {
            // No hook script provided by the root project; silently skip.
            return;
        }

        // Run interactively so a script that prompts for keyboard input
        // (e.g. via "read") can receive it and show its prompt in real time.
        if ($isWindows) {
            self::executeCommand($io, $baseDir,
                'powershell -NoProfile -ExecutionPolicy Bypass -File ' . $relativePath, true);
        } else {
            self::executeCommand($io, $baseDir, 'sh ' . $relativePath, true);
        }
    }

    /**
     * Recursively remove a directory and all of its contents.
     *
     * Implemented with PHP's filesystem functions so it works on every
     * platform, including Windows. Symbolic links (such as the many links a
     * pnpm "node_modules" tree contains) are removed without descending into
     * their targets. Returns true when the directory existed and was removed;
     * a non-existent directory is silently ignored.
     */
    protected static function removeDirectory(string $path): bool
    {
        if (!is_dir($path)) {
            return false;
        }

        $items = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST,
            \RecursiveIteratorIterator::CATCH_GET_CHILD
        );

        foreach ($items as $item) {
            $pathname = $item->getPathname();
            if ($item->isLink()) {
                // Remove the link itself without following it. A directory
                // junction/symlink on Windows needs rmdir(), while unlink()
                // handles every link type on macOS / Linux.
                if (!@unlink($pathname)) {
                    @rmdir($pathname);
                }
            } elseif ($item->isDir()) {
                @rmdir($pathname);
            } else {
                @unlink($pathname);
            }
        }

        return @rmdir($path);
    }

    /**
     * Execute a shell command in the given directory.
     *
     * When $interactive is true the child process inherits this process's
     * STDIN/STDOUT/STDERR, so commands that prompt for keyboard input (such
     * as a "read" in a shell script) work and their output is shown live.
     * Otherwise the output is captured and printed after the command ends.
     *
     * @throws \RuntimeException When the command cannot be started or exits
     *                           with a non-zero status code.
     */
    protected static function executeCommand(IOInterface $io, string $cwd, string $command, bool $interactive = false): void
    {
        $io->write(sprintf('  > %s', $command));

        if ($interactive) {
            // Inherit the parent process's standard streams so the command can
            // read keyboard input (e.g. a script's "read" prompt) and write
            // its output in real time, instead of capturing them through pipes.
            $process = proc_open($command, [0 => STDIN, 1 => STDOUT, 2 => STDERR], $pipes, $cwd);

            if (!is_resource($process)) {
                throw new \RuntimeException(sprintf('Failed to execute: %s', $command));
            }

            $exitCode = proc_close($process);
            if ($exitCode !== 0) {
                throw new \RuntimeException(sprintf('Command failed with exit code %d: %s', $exitCode, $command));
            }

            return;
        }

        // Capture stdout/stderr through real temporary files instead of pipes.
        // Reading two pipes sequentially can deadlock when the child fills one
        // pipe's buffer while we are still draining the other; file-backed
        // descriptors have no such fixed buffer and also work on Windows, where
        // stream_select() cannot watch process pipes.
        $stdoutStream = tmpfile();
        $stderrStream = tmpfile();
        if ($stdoutStream === false || $stderrStream === false) {
            if ($stdoutStream !== false) {
                fclose($stdoutStream);
            }
            if ($stderrStream !== false) {
                fclose($stderrStream);
            }
            throw new \RuntimeException(sprintf('Failed to allocate output buffers for: %s', $command));
        }

        $process = proc_open(
            $command,
            [
                0 => ['pipe', 'r'],
                1 => $stdoutStream,
                2 => $stderrStream,
            ],
            $pipes,
            $cwd
        );

        if (!is_resource($process)) {
            fclose($stdoutStream);
            fclose($stderrStream);
            throw new \RuntimeException(sprintf('Failed to execute: %s', $command));
        }

        // Send EOF on stdin so a command that reads from it does not block.
        if (isset($pipes[0]) && is_resource($pipes[0])) {
            fclose($pipes[0]);
        }

        $exitCode = proc_close($process);

        rewind($stdoutStream);
        rewind($stderrStream);
        $stdout = stream_get_contents($stdoutStream);
        $stderr = stream_get_contents($stderrStream);
        fclose($stdoutStream);
        fclose($stderrStream);

        if ($stdout) {
            $io->write($stdout);
        }
        if ($exitCode !== 0 && $stderr) {
            $io->writeError($stderr);
        }

        if ($exitCode !== 0) {
            throw new \RuntimeException(sprintf('Command failed with exit code %d: %s', $exitCode, $command));
        }
    }
}

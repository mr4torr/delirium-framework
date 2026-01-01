<?php

declare(strict_types=1);

namespace Delirium\Compile\Service;

use Delirium\Compile\Config\CompileConfig;
use RuntimeException;
use Symfony\Component\Console\Output\OutputInterface;

class SpcBuilder
{
    private string $rootDir;

    public function __construct(string $rootDir)
    {
        $this->rootDir = rtrim($rootDir, '/');
    }

    public function downloadSources(CompileConfig $config, OutputInterface $output): void
    {
        $output->writeln("<info>Downloading PHP sources via Docker...</info>");
        $extensions = implode(',', $config->extensions);
        
        $cmd = $this->buildDockerCommand(
            "download --for-extensions=\"{$extensions}\" --with-php=8.4 --prefer-pre-built"
        );

        $output->writeln("Running: $cmd");
        $this->exec($cmd, $output);
    }

    public function buildMicro(string $pharPath, CompileConfig $config, OutputInterface $output): string
    {
        $output->writeln("<info>Building Static Binary (Micro SAPI) via Docker...</info>");
        $extensions = implode(',', $config->extensions);
        
        $buildCmd = $this->buildDockerCommand(
            "build \"{$extensions}\" --build-micro --with-micro-fake-cli"
        );

        $output->writeln("Running SPC Build: $buildCmd");
        $this->exec($buildCmd, $output);

        // SPC may ignore environment variables and create in root
        // Check both locations: build/buildroot and buildroot
        $microSfx = null;
        $possiblePaths = [
            $this->rootDir . '/build/buildroot/bin/micro.sfx',
            $this->rootDir . '/buildroot/bin/micro.sfx',
            $this->rootDir . '/build/dist/micro.sfx',
            $this->rootDir . '/dist/micro.sfx'
        ];
        
        foreach ($possiblePaths as $path) {
            if (file_exists($path)) {
                $microSfx = $path;
                break;
            }
        }
        
        if (!$microSfx) {
            throw new RuntimeException("Could not find generated micro.sfx in any expected location. Check build output.");
        }
        
        $output->writeln("Found micro.sfx at: {$microSfx}");
        
        // Move artifacts to build/ if they're in root
        $this->moveArtifactsToBuild($output);

        // Fusion
        $binaryPath = $this->rootDir . '/build/' . $config->outputName;
        $output->writeln("Fusing {$microSfx} + {$pharPath} -> {$binaryPath}");
        
        file_put_contents($binaryPath, file_get_contents($microSfx) . file_get_contents($pharPath));
        chmod($binaryPath, 0755);

        return $binaryPath;
    }
    
    private function moveArtifactsToBuild(OutputInterface $output): void
    {
        $dirsToMove = ['buildroot', 'downloads', 'source', 'dist'];
        
        foreach ($dirsToMove as $dir) {
            $sourcePath = $this->rootDir . '/' . $dir;
            $destPath = $this->rootDir . '/build/' . $dir;
            
            if (is_dir($sourcePath) && !is_dir($destPath)) {
                $output->writeln("Moving {$dir}/ to build/{$dir}/");
                
                // Use shell command to handle permission issues
                $cmd = sprintf(
                    'mv %s %s 2>&1',
                    escapeshellarg($sourcePath),
                    escapeshellarg($destPath)
                );
                
                exec($cmd, $cmdOutput, $returnCode);
                
                if ($returnCode !== 0) {
                    $output->writeln("<comment>Warning: Could not move {$dir}/: " . implode("\n", $cmdOutput) . "</comment>");
                }
            }
        }
    }

    private function buildDockerCommand(string $spcArgs): string
    {
        // Deps needed for SPC build on Alpine
        $deps = "git curl build-base linux-headers libtool automake autoconf pkgconf re2c bison flex cmake libxml2-dev openssl-dev";
        
        $uid = getmyuid();
        $gid = getmygid();
        
        // Create symlinks to force SPC to write to build/ subdirectories
        // SPC ignores environment variables, so we use symlinks instead
        $setupSymlinks = 'mkdir -p build/buildroot build/downloads build/source build/pkgroot build/log && ' .
                        'ln -sfn build/buildroot buildroot && ' .
                        'ln -sfn build/downloads downloads && ' .
                        'ln -sfn build/source source && ' .
                        'ln -sfn build/pkgroot pkgroot && ' .
                        'ln -sfn build/log log';
        
        // Command chain:
        // 1. apk add build tools
        // 2. create pkg-config symlink
        // 3. setup directory symlinks
        // 4. run spc doctor --auto-fix
        // 5. run spc command
        // 6. fix permissions
        
        $chownCmd = sprintf(
            'chown -R %d:%d build/ || true',
            $uid,
            $gid
        );
        
        $containerCmd = sprintf(
            'apk update && apk add --no-cache %s && ln -sf /usr/bin/pkgconf /usr/bin/pkg-config && %s && php vendor/bin/spc doctor --auto-fix && (php vendor/bin/spc %s); RET=$?; %s; exit $RET',
            $deps,
            $setupSymlinks,
            $spcArgs,
            $chownCmd
        );

        return sprintf(
            'docker run --rm -v "%s":/app -w /app php:8.4-cli-alpine sh -c "%s"',
            $this->rootDir,
            $containerCmd
        );
    }

    private function exec(string $cmd, OutputInterface $output): void
    {
        // stream output
        $descriptor = [
            1 => ['pipe', 'w'], // stdout
            2 => ['pipe', 'w'], // stderr
        ];
        
        $proc = proc_open($cmd, $descriptor, $pipes);
        
        if (is_resource($proc)) {
            while ($s = fgets($pipes[1])) {
                $output->write($s);
            }
             while ($s = fgets($pipes[2])) {
                $output->write("<error>$s</error>");
            }
            fclose($pipes[1]);
            fclose($pipes[2]);
            
            $code = proc_close($proc);
            if ($code !== 0) {
                throw new RuntimeException("Command failed with code $code");
            }
        }
    }
}

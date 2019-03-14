<?php

namespace Shapecode\Devliver\Service;

use Composer\Downloader\FileDownloader;
use Composer\Factory;
use Composer\Package\Archiver\ArchiveManager;
use Composer\Package\CompletePackageInterface;
use Composer\Util\ComposerMirror;
use Shapecode\Devliver\Composer\ComposerManager;
use Shapecode\Devliver\Entity\PackageInterface as EntityPackageInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Class DistSynchronization
 *
 * @package Shapecode\Devliver\Service
 * @author  Nikita Loges
 */
class DistSynchronization implements DistSynchronizationInterface
{

    /** @var ComposerManager */
    protected $composerManager;

    /** @var string */
    protected $format = 'zip';

    /** @var string */
    protected $distDir;

    /**
     * @param ComposerManager $composerManager
     * @param                 $distDir
     */
    public function __construct(ComposerManager $composerManager, $distDir)
    {
        $this->composerManager = $composerManager;
        $this->distDir = $distDir;
    }

    /**
     * @inheritdoc
     */
    public function getDistFilename(EntityPackageInterface $dbPackage, $ref): string
    {
        $cacheFile = $this->getCacheFile($dbPackage, $ref);

        if (file_exists($cacheFile)) {
            return $cacheFile;
        }

        if (!$dbPackage->getVersions()->count()) {
            return '';
        }

        foreach ($dbPackage->getPackages() as $package) {
            if ($package->getSourceReference() == $ref) {
                $this->downloadPackage($dbPackage, $package);

                return $cacheFile;
            }
        }

        return '';
    }

    /**
     * @param EntityPackageInterface   $dbPackage
     * @param CompletePackageInterface $package
     *
     * @return string
     */
    protected function downloadPackage(EntityPackageInterface $dbPackage, CompletePackageInterface $package)
    {
        $cacheFile = $this->getCacheFile($dbPackage, $package->getSourceReference());
        $cacheDir = dirname($cacheFile);

        $fs = new Filesystem();

        if (!$fs->exists($cacheDir)) {
            $fs->mkdir($cacheDir);
        }

        if (!$fs->exists($cacheFile)) {

            if ($url = $package->getDistUrl()) {
                try {
                    $downloader = $this->createFileDownloader();
                    $path = $downloader->download($package, sys_get_temp_dir());

                    $fs->rename($path, $cacheFile);

                    return $cacheFile;
                } catch (\Exception $e) {
                }
            }

            $archiveManager = $this->createArchiveManager();
            $path = $archiveManager->archive($package, $package->getDistType() ?: $this->format, sys_get_temp_dir());

            $fs->rename($path, $cacheFile);
        }

        return $cacheFile;
    }

    /**
     * @param CompletePackageInterface $package
     *
     * @return string
     */
    protected function getCacheFile(EntityPackageInterface $dbPackage, $ref): string
    {
        $targetDir = $this->distDir.DistSynchronizationInterface::DIST_FORMAT;

        $name = $dbPackage->getName();
        $type = $this->format;
        $version = 'placeholder';

        return ComposerMirror::processUrl($targetDir, $name, $version, $ref, $type);
    }

    /**
     * @return FileDownloader
     */
    protected function createFileDownloader(): FileDownloader
    {
        $config = $this->composerManager->getConfig();
        $io = $this->composerManager->createIO();

        $downloader = new FileDownloader($io, $config);
        $downloader->setOutputProgress(false);

        return $downloader;
    }

    /**
     * @return ArchiveManager
     */
    protected function createArchiveManager(): ArchiveManager
    {
        $config = $this->composerManager->getConfig();

        $factory = new Factory();

        /* @var ArchiveManager $archiveManager */
        $archiveManager = $factory->createArchiveManager($config);
        $archiveManager->setOverwriteFiles(false);

        return $archiveManager;
    }
}

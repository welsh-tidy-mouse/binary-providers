<?php

declare(strict_types=1);

namespace WelshTidyMouse\BinaryProvider;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Path;
use WelshTidyMouse\BinaryFetcher\Contract\BinaryProviderInterface;
use WelshTidyMouse\BinaryFetcher\Exception\BinaryProviderException;
use WelshTidyMouse\BinaryFetcher\Tool\ArchiveProcessor;
use WelshTidyMouse\BinaryFetcher\Tool\ArchiveProcessorInterface;
use WelshTidyMouse\BinaryFetcher\Tool\GithubProviderHelperTrait;
use WelshTidyMouse\BinaryFetcher\Type\OsType;
use WelshTidyMouse\BinaryFetcher\Type\SystemArchType;

class DartSassBinaryProvider implements BinaryProviderInterface
{
    use GithubProviderHelperTrait;

    public const string DART_SASS = 'dart-sass';

    public function __construct(
        protected ArchiveProcessorInterface $archiveProcessor = new ArchiveProcessor(),
        protected Filesystem $filesystem = new Filesystem(),
    ) {
    }

    public static function getName(): string
    {
        return self::DART_SASS;
    }

    /**
     * @inheritdoc
     */
    public function getDownloadableAssetUrl(string $version, OsType $os, SystemArchType $arch): ?string
    {
        $assetName = $this->getAssetName($version, $os, $arch);
        if (null === $assetName) {
            return null;
        }

        return $this->generateDownloadUrl($version, 'sass/dart-sass', $assetName);
    }

    /**
     * @inheritdoc
     */
    public function getBinaryFilenameFromDownloadedAsset(string $assetFileName, string $downloadDirPath): string
    {
        /** @phpstan-ignore-next-line Dart Sasss binary assets always have an extension */
        ['filename' => $filename, 'extension' => $ext] = pathinfo($assetFileName);
        $binaryFileName = Path::getFilenameWithoutExtension($filename);
        if ($this->filesystem->exists(Path::join($downloadDirPath, $binaryFileName))) {
            $this->filesystem->remove(Path::join($downloadDirPath, 'src'));
            $this->filesystem->remove(Path::join($downloadDirPath, $binaryFileName));
        }

        match ($ext) {
            'gz' => $this->archiveProcessor->untar($assetFileName, $downloadDirPath),
            'zip' => $this->archiveProcessor->unzip($assetFileName, $downloadDirPath),
            default => throw new BinaryProviderException(self::DART_SASS, 'Asset extension file can not be matched.'),
        };

        $this->filesystem->remove(Path::join($downloadDirPath, $filename));
        $this->filesystem->remove(Path::join($downloadDirPath, $assetFileName));

        $targetBinaryFileName = str_contains($assetFileName, 'windows') ? 'sass.exe' : 'sass';

        $this->filesystem->copy(
            Path::join($downloadDirPath, self::DART_SASS, $targetBinaryFileName),
            Path::join($downloadDirPath, $targetBinaryFileName)
        );

        $this->filesystem->mirror(
            Path::join($downloadDirPath, self::DART_SASS, 'src'),
            Path::join($downloadDirPath, 'src')
        );

        $this->filesystem->remove(Path::join($downloadDirPath, self::DART_SASS));

        return $targetBinaryFileName;
    }

    protected function getAssetName(string $version, OsType $os, SystemArchType $arch): ?string
    {
        return match (true) {
            OsType::MACOS === $os && SystemArchType::X_64 === $arch => \sprintf('dart-sass-%s-macos-x64.tar.gz', $version),
            OsType::MACOS === $os && SystemArchType::ARM_64 === $arch => \sprintf('dart-sass-%s-macos-arm64.tar.gz', $version),
            OsType::LINUX === $os && SystemArchType::X_64 === $arch => \sprintf('dart-sass-%s-linux-x64.tar.gz', $version),
            OsType::LINUX === $os && SystemArchType::ARM_64 === $arch => \sprintf('dart-sass-%s-linux-arm64.tar.gz', $version),
            OsType::ALPINE_LINUX === $os && SystemArchType::X_64 === $arch => \sprintf('dart-sass-%s-linux-x64-musl.tar.gz', $version),
            OsType::ALPINE_LINUX === $os && SystemArchType::ARM_64 === $arch => \sprintf('dart-sass-%s-linux-arm64-musl.tar.gz', $version),
            OsType::WINDOWS === $os && SystemArchType::X_64 === $arch => \sprintf('dart-sass-%s-windows-x64.zip', $version),
            default => null,
        };
    }
}

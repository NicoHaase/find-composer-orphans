<?php

class ComposerOrphanChecker
{
    /**
     * @var string
     */
    private $jsonFileName;

    /**
     * @var string
     */
    private $lockFileName;

    /**
     * ComposerOrphanChecker constructor.
     * @param string $jsonFileName
     * @param string $lockFileName
     */
    public function __construct(string $jsonFileName, string $lockFileName)
    {
        $this->jsonFileName = $jsonFileName;
        $this->lockFileName = $lockFileName;
    }

    public function getOrphans(): array
    {
        $directRequiredPackages = $this->getPackagesFromJson();
        $lockfileData = $this->getLockfileData();

        foreach ($directRequiredPackages as $package) {
            $lockfileData->markAsRequired($package);
        }

        return $lockfileData->getOrphans();
    }

    private function getPackagesFromJson(): array
    {
        $jsonFileContent = file_get_contents($this->jsonFileName);
        $content = json_decode($jsonFileContent);

        if (null === $content) {
            return [];
        }

        $result = [];
        $requireList = $content->require;
        foreach ($requireList as $packageName => $version) {
            $result[] = $packageName;
        }

        return $result;
    }

    private function getLockfileData(): Lockfile
    {
        $lockfile = new Lockfile();

        $jsonFileContent = file_get_contents($this->lockFileName);
        $content = json_decode($jsonFileContent);

        if (null === $content) {
            return $lockfile;
        }

        $packageList = $content->packages;
        foreach ($packageList as $packageDetails) {
            $package = new Package($packageDetails->name);
            foreach ($packageDetails->require as $packageName => $version) {
                $package->addRequirement($packageName);
            }

            $lockfile->addPackage($package);
        }

        return $lockfile;
    }
}

class Lockfile
{
    /**
     * @var Package[]
     */
    private $packageList = [];

    public function addPackage(Package $package): void
    {
        $this->packageList[$package->getName()] = $package;
    }

    public function markAsRequired(string $packageName)
    {
        if (false === isset($this->packageList[$packageName])) {
            return;
        }

        $package = $this->packageList[$packageName];
        $package->markAsRequired();

        foreach ($package->getRequirements() as $requirement) {
            $this->markAsRequired($requirement);
        }
    }

    public function getOrphans(): array
    {
        $orphanPackages = array_filter($this->packageList, function (Package $package) {
            return false === $package->isRequired();
        });

        $orphanPackageNames = array_map(function (Package $package) {
            return $package->__toString();
        }, $orphanPackages);

        return $orphanPackageNames;
    }
}

class Package
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string[]
     */
    private $requirements = [];

    /**
     * @var bool
     */
    private $isRequired = false;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function markAsRequired(): void
    {
        $this->isRequired = true;
    }

    public function addRequirement(string $requirement): void
    {
        $this->requirements[] = $requirement;
    }

    public function isRequired(): bool
    {
        return $this->isRequired;
    }

    public function getRequirements(): array
    {
        return $this->requirements;
    }

    public function __toString(): string
    {
        return $this->name;
    }
}

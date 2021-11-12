<?php
declare(strict_types = 1);
namespace In2code\Lux\Domain\Service\Provider;

use In2code\Lux\Domain\Service\ConfigurationService;
use In2code\Lux\Exception\FileNotFoundException;
use In2code\Lux\Utility\FileUtility;
use In2code\Lux\Utility\ObjectUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException;

/**
 * Class Telecommunication
 */
class Telecommunication
{
    /**
     * @var ConfigurationService|null
     */
    protected $configurationService = null;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->configurationService = ObjectUtility::getConfigurationService();
    }

    /**
     * @param string $company
     * @return bool
     * @throws InvalidConfigurationTypeException
     * @throws FileNotFoundException
     */
    public function isTelecommunicationProvider(string $company): bool
    {
        return $this->isTelecommunicationProviderByDisallowedList($company) ||
            $this->isTelecommunicationProviderByWildcardSearch($company);
    }

    /**
     * Search for exact matches in list
     *
     * @param string $company
     * @return bool
     * @throws InvalidConfigurationTypeException
     * @throws FileNotFoundException
     */
    protected function isTelecommunicationProviderByDisallowedList(string $company): bool
    {
        $tpFileList = $this->configurationService->getTypoScriptSettingsByPath(
            'general.telecommunicationProviderList'
        );
        $filename = GeneralUtility::getFileAbsFileName($tpFileList);
        if (file_exists($filename) === false) {
            throw new FileNotFoundException(
                'File defined in "telecommunicationProviderList" does not exists',
                1636753747
            );
        }
        return FileUtility::isStringInFile($company, $filename);
    }

    /**
     * Search for wildcard matches in list
     *
     * @param string $company
     * @return bool
     * @throws InvalidConfigurationTypeException
     * @throws FileNotFoundException
     */
    protected function isTelecommunicationProviderByWildcardSearch(string $company): bool
    {
        $tpFileList = $this->configurationService->getTypoScriptSettingsByPath(
            'general.telecommunicationProviderTermList'
        );
        $filename = GeneralUtility::getFileAbsFileName($tpFileList);
        if (file_exists($filename) === false) {
            throw new FileNotFoundException(
                'File defined in "telecommunicationProviderList" does not exists',
                1636753747
            );
        }
        $fileContent = file_get_contents($filename);
        $terms = GeneralUtility::trimExplode(PHP_EOL, $fileContent, true);
        foreach ($terms as $term) {
            if (stristr($company, $term) !== false) {
                return true;
            }
        }
        return false;
    }
}

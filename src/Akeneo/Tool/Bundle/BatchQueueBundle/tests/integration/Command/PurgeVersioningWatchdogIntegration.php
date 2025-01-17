<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\BatchQueueBundle\tests\integration\Command;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Component\Batch\Exception\InvalidJobException;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

/**
 * @author    JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PurgeVersioningWatchdogIntegration extends TestCase
{
    /**
     * @test
     */
    public function it_purges_versions_without_custom_config(): void
    {
        $output = $this->runWatchdog();
        $result = $output->fetch();

        Assert::assertMatchesRegularExpression('/Launching job execution "\d+"/', $result);
        \preg_match('/Launching job execution "(\d+)"/', $result, $matches);
        $jobExecutionId = (int)$matches[1];

        Assert::assertStringContainsString(
            sprintf(
                "akeneo:batch:job' 'versioning_purge' '%d' '--quiet' '--env=test'",
                $jobExecutionId
            ),
            $result
        );
        Assert::assertMatchesRegularExpression('/Job execution "\d+" is finished in \d seconds/', $result);

        $jobExecutionRawParameters = $this->getConnection()->executeQuery(
            'SELECT raw_parameters FROM akeneo_batch_job_execution WHERE id=id',
            ['id'=> $jobExecutionId]
        )->fetchOne();

        $jobExecutionRawParameters = \json_decode($jobExecutionRawParameters, true);
        Assert::assertNull($jobExecutionRawParameters['more-than-days']);
        Assert::assertNull($jobExecutionRawParameters['less-than-days']);
    }

    /**
     * @test
     */
    public function it_purges_versions_with_custom_config(): void
    {
        $output = $this->runWatchdog([
            '--config' => ['less-than-days' => 5, 'more-than-days' => 15],
        ]);
        $result = $output->fetch();

        Assert::assertMatchesRegularExpression('/Launching job execution "\d+"/', $result);
        \preg_match('/Launching job execution "(\d+)"/', $result, $matches);
        $jobExecutionId = (int)$matches[1];

        Assert::assertStringContainsString(
            sprintf(
                "akeneo:batch:job' 'versioning_purge' '%d' '--quiet' '--env=test'",
                $jobExecutionId
            ),
            $result
        );
        Assert::assertMatchesRegularExpression('/Job execution "\d+" is finished in \d seconds/', $result);

        $jobExecutionRawParameters = $this->getConnection()->executeQuery(
            'SELECT raw_parameters FROM akeneo_batch_job_execution WHERE id=id',
            ['id'=> $jobExecutionId]
        )->fetchOne();

        $jobExecutionRawParameters = \json_decode($jobExecutionRawParameters, true);
        Assert::assertEquals('15', $jobExecutionRawParameters['more-than-days']);
        Assert::assertEquals('5', $jobExecutionRawParameters['less-than-days']);
    }

    /**
     * @test
     */
    public function it_cannot_purges_versions_with_wrong_config(): void
    {
        $output = $this->runWatchdog([
            '--config' => ['unknow-option' => 'foo'],
        ]);
        $result = $output->fetch();

        Assert::assertMatchesRegularExpression(
            '/Job instance "versioning_purge" running the job "versioning_purge" is invalid/',
            $result
        );
    }

    /**
     * @inheritDoc
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function getConnection(): Connection
    {
        return $this->get('database_connection');
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->getConnection()->executeQuery('DELETE FROM pim_versioning_version');
    }

    /**
     * Run watchdog command in verbose mode to test output
     */
    private function runWatchdog(array $arrayInput = []): BufferedOutput
    {
        $application = new Application(static::$kernel);
        $application->setAutoExit(false);

        $defaultArrayInput = [
            'command' => 'akeneo:batch:watchdog',
            '--job_code' => 'versioning_purge',
            '-vvv',
        ];

        $arrayInput = array_merge($defaultArrayInput, $arrayInput);
        if (isset($arrayInput['--config'])) {
            $arrayInput['--config'] = \json_encode($arrayInput['--config']);
        }

        $input = new ArrayInput($arrayInput);
        $output = new BufferedOutput();
        $application->run($input, $output);

        return $output;
    }
}

<?php
/*
 * @copyright  Copyright (C) 2017, 2018, 2019 Blue Flame Digital Solutions Limited / Phil Taylor. All rights reserved.
 * @author     Phil Taylor <phil@phil-taylor.com>
 * @see        https://github.com/PhilETaylor/mysites.guru
 * @license    MIT
 */

namespace Philetaylor\DoctrineEncryptBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Get status of doctrine encrypt bundle and the database.
 *
 * @author Phil E. Taylor <phil@phil-taylor.com>
 * @author Marcel van Nuil <marcel@ambta.com>
 * @author Michael Feinbier <michael@feinbier.net>
 */
class DoctrineEncryptStatusCommand extends PhiletaylorAbstract
{
    protected static $defaultName = 'doctrine:encrypt:status';

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setDescription('Get status of doctrine encrypt bundle and the database');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $metaDataArray = $this->entityManager->getMetadataFactory()->getAllMetadata();

        $totalCount = 0;
        foreach ($metaDataArray as $metaData) {
            if ($metaData->isMappedSuperclass) {
                continue;
            }

            $count                    = 0;
            $encryptedPropertiesCount = \count($this->getEncryptionableProperties($metaData));
            if ($encryptedPropertiesCount > 0) {
                $totalCount += $encryptedPropertiesCount;
                $count += $encryptedPropertiesCount;
            }

            if ($count > 0) {
                $output->writeln(sprintf('<info>%s</info> has <info>%d</info> properties which are encrypted.', $metaData->name, $count));
            } else {
                $output->writeln(sprintf('<info>%s</info> has no properties which are encrypted.', $metaData->name));
            }
        }

        $output->writeln('');
        $output->writeln(sprintf('<info>%d</info> entities found which are containing <info>%d</info> encrypted properties.', \count($metaDataArray), $totalCount));

        $output->writeln('');
        $keys = $this->subscriber->getSecretKeys();
        if (!$keys) {
            $output->writeln('<error>There are NO configured encryption keys!!</error>');

            return self::FAILURE;
        }
        $output->writeln('Here are the configured encryption keys:');
        foreach ($keys as $k=>$v) {
            $output->writeln("<info>$k</info>\t\t=>\t\t<info>$v</info>");
        }
        
         return self::SUCCESS;
    }
}

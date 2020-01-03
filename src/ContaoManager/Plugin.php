<?php

/*
 * This file is part of Oveleon Object Type Entity.
 *
 * (c) https://www.oveleon.de/
 */

declare(strict_types=1);

namespace ContaoEstateManager\ObjectTypeEntityOnOffice\ContaoManager;

use Contao\CoreBundle\ContaoCoreBundle;
use ContaoEstateManager\ObjectTypeEntity\ObjectTypeEntity;
use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use ContaoEstateManager\ObjectTypeEntityOnOffice\ObjectTypeEntityOnOffice;

class Plugin implements BundlePluginInterface
{
    /**
     * {@inheritdoc}
     */
    public function getBundles(ParserInterface $parser): array
    {
        return [
            BundleConfig::create(ObjectTypeEntityOnOffice::class)
                ->setLoadAfter([ContaoCoreBundle::class, ObjectTypeEntity::class])
                ->setReplace(['object-type-entity-onoffice']),
        ];
    }
}

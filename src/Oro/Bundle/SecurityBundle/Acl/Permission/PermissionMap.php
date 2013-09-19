<?php

namespace Oro\Bundle\SecurityBundle\Acl\Permission;

use Symfony\Component\Security\Acl\Permission\PermissionMapInterface;
use Oro\Bundle\SecurityBundle\Acl\Extension\AclExtensionSelector;

/**
 * This is permission map complements the masks which have been defined
 * on in all implementations of the mask builder and registered using ACL extension functionality.
 */
class PermissionMap implements PermissionMapInterface
{
    /**
     * @var AclExtensionSelector
     */
    protected $extensionSelector;

    /**
     * Constructor
     *
     * @param AclExtensionSelector $extensionSelector
     */
    public function __construct(AclExtensionSelector $extensionSelector)
    {
        $this->extensionSelector = $extensionSelector;
    }

    /**
     * {@inheritDoc}
     */
    public function getMasks($permission, $object)
    {
        return $this->extensionSelector
            ->select($object)
            ->getMasks($permission);
    }

    /**
     * {@inheritDoc}
     */
    public function contains($permission)
    {
        foreach ($this->extensionSelector->all() as $extension) {
            if ($extension->hasMasks($permission)) {
                return true;
            }
        }

        return false;
    }
}
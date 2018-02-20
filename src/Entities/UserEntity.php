<?php

namespace AdvancedLearning\Oauth2Server\Entities;

use AdvancedLearning\Oauth2Server\Extensions\GroupExtension;
use League\OAuth2\Server\Entities\Traits\EntityTrait;
use League\OAuth2\Server\Entities\UserEntityInterface;
use SilverStripe\Security\Group;
use SilverStripe\Security\Member;

class UserEntity implements UserEntityInterface
{
    use EntityTrait;

    public $member;

    /**
     * @param Member $member
     * @return ScopeEntity
     */
    public function __construct(?Member $member){
        if($member instanceof Member) {
           $this->setMember($member);
        }
    }

    /**
     * Get the Member associated with this ClientEntity.
     *
     * @return Member
     */
    public function getMember()
    {
        return $this->member;
    }

    /**
     * @param Member $member
     * @return $this
     */
    public function setMember(Member $member)
    {
        $this->member = $member;
        $this->setIdentifier($member->ID);
        return $this;
    }

    /**
     * Checks whether the member has a scope. Only works if the GroupExtension has been configured.
     *
     * @param string $scope
     * @return bool
     */
    public function hasScope(string $scope): bool
    {
        // always return true if extensions not configured
        return !Group::create()->hasExtension(GroupExtension::class) || $this->getMember()->Groups()->filter([
            'Scopes.Name' => $scope
        ])->count() > 0;
    }
}
